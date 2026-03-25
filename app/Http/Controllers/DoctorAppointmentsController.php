<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
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

        // Auto-register patient when appointment is confirmed or completed
        if (
            in_array($validated['status'], ['confirmed', 'completed']) &&
            ! in_array($previousStatus, ['confirmed', 'completed'])
        ) {
            Patient::firstOrCreate(
                ['doctor_id' => $doctor->id, 'email' => $appointment->patient_email],
                [
                    'name'         => $appointment->patient_name,
                    'phone'        => $appointment->patient_phone,
                    'first_seen_at' => $appointment->appointment_date,
                ]
            );
        }

        return back()->with('success', 'Appointment status updated.');
    }
}
