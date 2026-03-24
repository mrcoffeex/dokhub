<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        return Inertia::render('DoctorDashboard', [
            'doctor' => $doctor,
            'appointments' => Appointment::where('doctor_id', $doctor->id)
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->limit(20)
                ->get(),
            'stats' => [
                'total_appointments' => Appointment::where('doctor_id', $doctor->id)->count(),
                'pending_appointments' => Appointment::where('doctor_id', $doctor->id)->where('status', 'pending')->count(),
                'confirmed_appointments' => Appointment::where('doctor_id', $doctor->id)->where('status', 'confirmed')->count(),
                'completed_appointments' => Appointment::where('doctor_id', $doctor->id)->where('status', 'completed')->count(),
            ],
        ]);
    }
}
