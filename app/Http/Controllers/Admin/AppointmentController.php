<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Appointment::with('doctor');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q
                ->where('patient_name', 'ilike', "%{$term}%")
                ->orWhere('patient_email', 'ilike', "%{$term}%")
                ->orWhere('reference', 'ilike', "%{$term}%")
            );
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        return Inertia::render('Admin/Appointments/Index', [
            'appointments' => $query->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $request->only(['search', 'status', 'date']),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment->update([
            'status' => $request->status,
            'cancellation_reason' => $request->status === 'cancelled' ? $request->cancellation_reason : null,
            'confirmed_at' => $request->status === 'confirmed' ? now() : $appointment->confirmed_at,
        ]);

        return back()->with('success', 'Appointment status updated.');
    }
}
