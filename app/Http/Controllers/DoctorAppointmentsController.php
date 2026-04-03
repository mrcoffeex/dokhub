<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentCompleted;
use App\Mail\AppointmentStatusConfirmed;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class DoctorAppointmentsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        $query = Appointment::where('doctor_id', $doctor->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'ilike', "%{$search}%")
                  ->orWhere('patient_email', 'ilike', "%{$search}%")
                  ->orWhere('reference', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        $appointments = $query
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Doctor/Appointments', [
            'doctor'       => $doctor,
            'appointments' => $appointments,
            'filters'      => $request->only(['search', 'status', 'date']),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $doctor = Doctor::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'status'              => ['required', 'in:confirmed,cancelled,completed'],
            'cancellation_reason' => ['nullable', 'string', 'max:500', 'required_if:status,cancelled'],
        ]);

        $previousStatus = $appointment->status;

        $appointment->update([
            'status'              => $validated['status'],
            'confirmed_at'        => $validated['status'] === 'confirmed' ? now() : $appointment->confirmed_at,
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        if (config('mail.default') !== 'log') {
            try {
                if ($validated['status'] === 'confirmed' && $previousStatus !== 'confirmed') {
                    $appointment->load('doctor');
                    Mail::to($appointment->patient_email)
                        ->send(new AppointmentStatusConfirmed($appointment));
                } elseif ($validated['status'] === 'completed' && $previousStatus !== 'completed') {
                    $appointment->load(['doctor', 'diagnoses.prescriptions']);
                    Mail::to($appointment->patient_email)
                        ->send(new AppointmentCompleted($appointment));
                }
            } catch (\Exception $e) {
                Log::error('Appointment status mail failed', [
                    'appointment_id' => $appointment->id,
                    'status'         => $validated['status'],
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Appointment status updated.');
    }

    public function addPatient(Request $request, Appointment $appointment)
    {
        $doctor = Doctor::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($appointment->doctor_id === $doctor->id, 403);

        Patient::firstOrCreate(
            ['doctor_id' => $doctor->id, 'email' => $appointment->patient_email],
            [
                'name'          => $appointment->patient_name,
                'phone'         => $appointment->patient_phone,
                'first_seen_at' => $appointment->appointment_date,
            ]
        );

        return back()->with('success', $appointment->patient_name . ' has been added to your patients.');
    }
}
