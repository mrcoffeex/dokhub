<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    private function parsePeriod(Request $request): array
    {
        $period = $request->get('period', '30d');
        $dateTo = now()->endOfDay();
        $dateFrom = match ($period) {
            '7d'     => now()->subDays(6)->startOfDay(),
            '30d'    => now()->subDays(29)->startOfDay(),
            '90d'    => now()->subDays(89)->startOfDay(),
            '1y'     => now()->subYear()->startOfDay(),
            'custom' => Carbon::parse($request->get('date_from', now()->subDays(29)->toDateString()))->startOfDay(),
            default  => now()->subDays(29)->startOfDay(),
        };
        if ($period === 'custom' && $request->filled('date_to')) {
            $dateTo = Carbon::parse($request->get('date_to'))->endOfDay();
        }

        return [$dateFrom, $dateTo];
    }

    public function index(Request $request): Response
    {
        [$dateFrom, $dateTo] = $this->parsePeriod($request);
        $diffDays = (int) $dateFrom->diffInDays($dateTo);
        $isDaily  = $diffDays <= 60;

        $groupExpr = $isDaily
            ? DB::raw("TO_CHAR(appointment_date, 'YYYY-MM-DD') as date_key")
            : DB::raw("TO_CHAR(appointment_date, 'YYYY-MM') as date_key");

        // ── Appointment trend ──────────────────────────────────
        $rawTrend = Appointment::whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select($groupExpr, DB::raw('count(*) as total'))
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->pluck('total', 'date_key');

        $keys = $isDaily
            ? collect(CarbonPeriod::create($dateFrom, '1 day', $dateTo))->map(fn (\DateTimeInterface $d) => $d->format('Y-m-d'))
            : collect(CarbonPeriod::create($dateFrom->copy()->startOfMonth(), '1 month', $dateTo))->map(fn (\DateTimeInterface $d) => $d->format('Y-m'));

        $appointmentTrend = $keys->map(fn ($k) => ['date' => $k, 'count' => (int) ($rawTrend[$k] ?? 0)]);

        // ── Status breakdown ───────────────────────────────────
        $statusBreakdown = Appointment::whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Type breakdown ─────────────────────────────────────
        $typeBreakdown = Appointment::whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('appointment_type', DB::raw('count(*) as total'))
            ->groupBy('appointment_type')
            ->pluck('total', 'appointment_type');

        // ── Fixed 12-month window ──────────────────────────────
        $allMonths = collect(CarbonPeriod::create(now()->subYear()->startOfMonth(), '1 month', now()))
            ->map(fn (\DateTimeInterface $d) => $d->format('Y-m'));

        // ── Doctor growth ──────────────────────────────────────
        $doctorGrowthRaw = Doctor::where('created_at', '>=', now()->subYear()->startOfMonth())
            ->select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $doctorGrowth = $allMonths->map(fn ($m) => ['month' => $m, 'count' => (int) ($doctorGrowthRaw[$m] ?? 0)]);

        // ── Subscription revenue ───────────────────────────────
        $subRevenueRaw = DB::table('payment_logs')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subYear()->startOfMonth())
            ->select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"), DB::raw('sum(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $subRevenue = $allMonths->map(fn ($m) => [
            'month'   => $m,
            'revenue' => round((float) ($subRevenueRaw[$m] ?? 0) / 100, 2),
        ]);

        // ── Monthly status stacked (last 12 months) ────────────
        $monthlyStatusRaw = Appointment::where('appointment_date', '>=', now()->subYear()->startOfMonth()->toDateString())
            ->select(
                DB::raw("TO_CHAR(appointment_date, 'YYYY-MM') as month"),
                'status',
                DB::raw('count(*) as total')
            )
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();

        $monthlyStatus = $allMonths->map(fn ($m) => [
            'month'     => $m,
            'pending'   => (int) $monthlyStatusRaw->where('month', $m)->where('status', 'pending')->sum('total'),
            'confirmed' => (int) $monthlyStatusRaw->where('month', $m)->where('status', 'confirmed')->sum('total'),
            'completed' => (int) $monthlyStatusRaw->where('month', $m)->where('status', 'completed')->sum('total'),
            'cancelled' => (int) $monthlyStatusRaw->where('month', $m)->where('status', 'cancelled')->sum('total'),
        ]);

        // ── Top doctors ────────────────────────────────────────
        $topDoctors = Doctor::withCount(['appointments' => fn ($q) =>
            $q->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
        ])
            ->orderByDesc('appointments_count')
            ->limit(10)
            ->get(['id', 'name', 'specialization', 'appointments_count']);

        // ── Specialization distribution ────────────────────────
        $specializationData = DB::table('doctors')
            ->whereNotNull('specialization')
            ->whereRaw("specialization::text != '[]'")
            ->select(
                DB::raw("jsonb_array_elements_text(specialization::jsonb) as spec"),
                DB::raw('count(*) as total')
            )
            ->groupBy('spec')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ── Doctor status breakdown ────────────────────────────
        $doctorStatusBreakdown = Doctor::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Stats ──────────────────────────────────────────────
        $totalAppts     = (int) Appointment::whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])->count();
        $completedAppts = (int) ($statusBreakdown['completed'] ?? 0);
        $totalRevenue   = round((float) DB::table('payment_logs')->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount') / 100, 2);
        $newDoctors     = (int) Doctor::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $completionRate = $totalAppts > 0 ? round($completedAppts / $totalAppts * 100, 1) : 0.0;

        return Inertia::render('Admin/Analytics', [
            'filters' => [
                'period'    => $request->get('period', '30d'),
                'date_from' => $request->get('date_from'),
                'date_to'   => $request->get('date_to'),
            ],
            'stats' => [
                'total_appointments' => $totalAppts,
                'total_revenue'      => $totalRevenue,
                'new_doctors'        => $newDoctors,
                'completion_rate'    => $completionRate,
            ],
            'appointment_trend'       => $appointmentTrend->values(),
            'status_breakdown'        => $statusBreakdown,
            'type_breakdown'          => $typeBreakdown,
            'doctor_growth'           => $doctorGrowth->values(),
            'sub_revenue'             => $subRevenue->values(),
            'monthly_status'          => $monthlyStatus->values(),
            'top_doctors'             => $topDoctors,
            'specialization_data'     => $specializationData,
            'doctor_status_breakdown' => $doctorStatusBreakdown,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$dateFrom, $dateTo] = $this->parsePeriod($request);

        $appointments = Appointment::with('doctor:id,name')
            ->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('appointment_date')
            ->get(['reference', 'doctor_id', 'patient_name', 'patient_email',
                   'appointment_date', 'appointment_time', 'status', 'appointment_type']);

        $filename = 'admin-analytics-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($appointments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Doctor', 'Patient', 'Email', 'Date', 'Time', 'Status', 'Type']);
            foreach ($appointments as $a) {
                fputcsv($out, [
                    $a->reference,
                    $a->doctor?->name ?? '',
                    $a->patient_name,
                    $a->patient_email,
                    $a->appointment_date->format('Y-m-d'),
                    $a->appointment_time,
                    $a->status,
                    $a->appointment_type,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
