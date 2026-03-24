<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function store(Request $request, Doctor $doctor)
    {
        abort_unless($doctor->isApproved(), 404);

        $validated = $request->validate([
            'patient_name'     => ['required', 'string', 'max:100'],
            'patient_email'    => ['required', 'email', 'max:150'],
            'patient_phone'    => ['required', 'string', 'max:25'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'reason'           => ['nullable', 'string', 'max:500'],
        ]);

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
            ...$validated,
            'doctor_id' => $doctor->id,
            'reference' => Appointment::generateReference(),
            'status'    => 'confirmed',
            'confirmed_at' => now(),
        ]);

        Mail::to($appointment->patient_email)->send(new AppointmentConfirmation($appointment));

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
