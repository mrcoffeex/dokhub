<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentReceived;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\SmsService;
use App\Sms\AppointmentSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function store(Request $request, Doctor $doctor)
    {
        abort_unless($doctor->isApproved(), 404);

        $captchaConfigured = app()->isProduction()
            && config('services.hcaptcha.secret')
            && config('services.hcaptcha.sitekey');

        $validated = $request->validate([
            'appointment_type' => ['required', 'in:in_person,online'],
            'patient_name'     => ['required', 'string', 'max:100'],
            'patient_email'    => ['required', 'email', 'max:150'],
            'patient_phone'    => ['required', 'string', 'regex:/^09\d{9}$/'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'reason'           => ['nullable', 'string', 'max:500'],
            'hcaptcha_token'   => $captchaConfigured ? ['required', 'string'] : ['nullable', 'string'],
        ], [
            'patient_phone.regex' => 'Phone number must be an 11-digit mobile number (e.g. 09123456789).',
        ]);

        if ($captchaConfigured) {
            $captchaResponse = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'secret'   => config('services.hcaptcha.secret'),
                'response' => $validated['hcaptcha_token'],
            ]);
            $captchaBody = $captchaResponse->json();
            if (! ($captchaBody['success'] ?? false)) {
                Log::warning('hCaptcha verification failed (booking)', [
                    'error-codes' => $captchaBody['error-codes'] ?? [],
                    'http_status' => $captchaResponse->status(),
                ]);
                return back()->withErrors(['hcaptcha_token' => 'Human verification failed. Please try again.']);
            }
        }

        // Strip non-model fields before create
        $bookingData = collect($validated)->except('hcaptcha_token')->all();

        // Check visitor hasn't exceeded 5 bookings today (across all doctors)
        $dailyBookings = Appointment::where('patient_email', $validated['patient_email'])
            ->whereDate('created_at', today())
            ->count();

        if ($dailyBookings >= 5) {
            return back()->withErrors(['patient_email' => 'You have reached the maximum of 5 bookings per day. Please try again tomorrow.']);
        }

        // Check patient doesn't already have a booking with this doctor on the same date
        $alreadyBooked = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('patient_email', $validated['patient_email'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($alreadyBooked) {
            return back()->withErrors(['appointment_date' => 'You already have an appointment with this doctor on this date. Only one booking per day is allowed.']);
        }

        // Check slot isn't already taken
        $conflict = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['appointment_time' => 'This time slot is no longer available. Please choose another.']);
        }

        $appointment = Appointment::create([
            ...$bookingData,
            'doctor_id' => $doctor->id,
            'reference' => Appointment::generateReference(),
            'status'    => 'pending',
        ]);

        $appointment->load('doctor');

        if (config('mail.default') !== 'log') {
            try {
                Mail::to($appointment->patient_email)
                    ->send(new AppointmentReceived($appointment));
            } catch (\Exception $e) {
                // Mail failure should not block the booking confirmation
                Log::error('Appointment confirmation mail failed', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // SMS notification
        try {
            app(SmsService::class)->send(
                $appointment->patient_phone,
                AppointmentSms::received($appointment)
            );
        } catch (\Exception $e) {
            Log::error('Appointment received SMS failed', [
                'appointment_id' => $appointment->id,
                'error'          => $e->getMessage(),
            ]);
        }

        return Inertia::render('Booking/Success', [
            'appointment' => $appointment->load('doctor'),
        ]);
    }

    public function lookup(Request $request): Response
    {
        $request->validate([
            'reference' => ['required', 'string'],
            'email'     => ['required', 'email'],
        ]);

        $appointment = Appointment::where('reference', $request->reference)
            ->where('patient_email', $request->email)
            ->with('doctor')
            ->first();

        return Inertia::render('Booking/Lookup', [
            'appointment' => $appointment,
            'searched'    => true,
        ]);
    }
}
