<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
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
}
