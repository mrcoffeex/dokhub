<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalAppts     = Appointment::count();
        $pendingAppts   = Appointment::where('status', 'pending')->count();
        $confirmedAppts = Appointment::where('status', 'confirmed')->count();
        $completedAppts = Appointment::where('status', 'completed')->count();
        $cancelledAppts = Appointment::where('status', 'cancelled')->count();

        $revenue = DB::table('appointments')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->where('appointments.status', 'completed')
            ->sum('doctors.consultation_fee');

        $weekTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            return [
                'date'  => $date,
                'label' => now()->subDays($daysAgo)->format('D'),
                'count' => Appointment::whereDate('appointment_date', $date)->count(),
            ];
        });

        $topDoctors = Doctor::withCount('appointments')
            ->orderByDesc('appointments_count')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_doctors'          => Doctor::count(),
                'approved_doctors'       => Doctor::where('status', 'approved')->count(),
                'pending_doctors'        => Doctor::where('status', 'pending')->count(),
                'suspended_doctors'      => Doctor::where('status', 'suspended')->count(),
                'total_appointments'     => $totalAppts,
                'today_appointments'     => Appointment::whereDate('appointment_date', today())->count(),
                'today_confirmed'        => Appointment::whereDate('appointment_date', today())->where('status', 'confirmed')->count(),
                'pending_appointments'   => $pendingAppts,
                'confirmed_appointments' => $confirmedAppts,
                'completed_appointments' => $completedAppts,
                'cancelled_appointments' => $cancelledAppts,
                'total_revenue'          => (float) $revenue,
            ],
            'week_trend'          => $weekTrend->values(),
            'top_doctors'         => $topDoctors,
            'recent_appointments' => Appointment::with('doctor')->latest()->limit(6)->get(),
        ]);
    }
}

