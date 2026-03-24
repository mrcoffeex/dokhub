<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_doctors'      => Doctor::count(),
                'approved_doctors'   => Doctor::where('status', 'approved')->count(),
                'pending_doctors'    => Doctor::where('status', 'pending')->count(),
                'total_appointments' => Appointment::count(),
                'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
                'pending_appointments' => Appointment::where('status', 'pending')->count(),
            ],
            'recent_appointments' => Appointment::with('doctor')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
