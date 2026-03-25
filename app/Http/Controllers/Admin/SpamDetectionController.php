<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SpamDetectionController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->get('risk', 'all');

        // Aggregate per patient email — only flag those with more than 5 bookings in the last 24 hours
        $rows = DB::table('appointments')
            ->selectRaw("
                patient_email,
                MIN(patient_name) AS patient_name,
                COUNT(*) AS total_bookings,
                COUNT(CASE WHEN created_at >= NOW() - INTERVAL '24 hours' THEN 1 END) AS bookings_24h,
                COUNT(CASE WHEN created_at >= NOW() - INTERVAL '7 days'  THEN 1 END) AS bookings_7d,
                COUNT(DISTINCT doctor_id) AS unique_doctors,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS cancelled_count,
                COUNT(CASE WHEN status = 'pending'   THEN 1 END) AS pending_count,
                MAX(created_at) AS last_booking_at
            ")
            ->groupBy('patient_email')
            ->havingRaw("COUNT(CASE WHEN created_at >= NOW() - INTERVAL '24 hours' THEN 1 END) > 5")
            ->orderByRaw("COUNT(CASE WHEN created_at >= NOW() - INTERVAL '24 hours' THEN 1 END) DESC")
            ->get();

        // Classify risk level based solely on daily booking volume
        $classified = $rows->map(function ($row) {
            $cancelRate = $row->total_bookings > 0
                ? (int) round(($row->cancelled_count / $row->total_bookings) * 100)
                : 0;

            $risk    = $row->bookings_24h >= 10 ? 'high' : ($row->bookings_24h >= 7 ? 'medium' : 'low');
            $reasons = ["{$row->bookings_24h} bookings in the last 24 hours"];

            $row->risk        = $risk;
            $row->reasons     = $reasons;
            $row->cancel_rate = $cancelRate;

            return $row;
        });

        $stats = [
            'total'  => $classified->count(),
            'high'   => $classified->where('risk', 'high')->count(),
            'medium' => $classified->where('risk', 'medium')->count(),
            'low'    => $classified->where('risk', 'low')->count(),
        ];

        $filtered = $filter !== 'all'
            ? $classified->filter(fn($r) => $r->risk === $filter)->values()
            : $classified->values();

        return Inertia::render('Admin/SpamDetection', [
            'flagged' => $filtered,
            'stats'   => $stats,
            'filter'  => $filter,
        ]);
    }
}
