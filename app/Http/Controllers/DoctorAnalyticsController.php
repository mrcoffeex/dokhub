<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use App\Models\Appointment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoctorAnalyticsController extends Controller
{
    use ResolvesCurrentDoctor;

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
        $this->assertPermission($request, 'appointments.view');
        $doctor   = $this->getDoctor($request);
        [$dateFrom, $dateTo] = $this->parsePeriod($request);

        $diffDays = (int) $dateFrom->diffInDays($dateTo);
        $isDaily  = $diffDays <= 60;

        $groupExpr = $isDaily
            ? DB::raw("TO_CHAR(appointment_date, 'YYYY-MM-DD') as date_key")
            : DB::raw("TO_CHAR(appointment_date, 'YYYY-MM') as date_key");

        // ── Appointment trend ──────────────────────────────────
        $rawTrend = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select($groupExpr, DB::raw('count(*) as total'))
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->pluck('total', 'date_key');

        $keys = $isDaily
            ? collect(CarbonPeriod::create($dateFrom, '1 day', $dateTo))->map(fn (\DateTimeInterface $d) => $d->format('Y-m-d'))
            : collect(CarbonPeriod::create($dateFrom->copy()->startOfMonth(), '1 month', $dateTo))->map(fn (\DateTimeInterface $d) => $d->format('Y-m'));

        $appointmentTrend = $keys->map(fn ($k) => ['date' => $k, 'count' => (int) ($rawTrend[$k] ?? 0)]);

        // ── Status breakdown ───────────────────────────────────
        $statusBreakdown = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Appointment type breakdown ─────────────────────────
        $typeBreakdown = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('appointment_type', DB::raw('count(*) as total'))
            ->groupBy('appointment_type')
            ->pluck('total', 'appointment_type');

        // ── Monthly revenue — last 12 months (fixed window) ────
        $allMonths = collect(CarbonPeriod::create(now()->subYear()->startOfMonth(), '1 month', now()))
            ->map(fn (\DateTimeInterface $d) => $d->format('Y-m'));

        $revenueRaw = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->where('appointment_date', '>=', now()->subYear()->startOfMonth()->toDateString())
            ->select(
                DB::raw("TO_CHAR(appointment_date, 'YYYY-MM') as month"),
                DB::raw('count(*) as completed_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('completed_count', 'month');

        $fee           = (float) ($doctor->consultation_fee ?? 0);
        $monthlyRevenue = $allMonths->map(fn ($m) => [
            'month'     => $m,
            'completed' => (int) ($revenueRaw[$m] ?? 0),
            'revenue'   => (int) ($revenueRaw[$m] ?? 0) * $fee,
        ]);

        // ── Top diagnoses ──────────────────────────────────────
        $topDiagnoses = DB::table('diagnoses')
            ->where('doctor_id', $doctor->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('title', DB::raw('count(*) as total'))
            ->groupBy('title')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── Peak appointment hours ─────────────────────────────
        $peakRaw = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select(
                DB::raw("EXTRACT(HOUR FROM appointment_time::time)::integer as hour"),
                DB::raw('count(*) as total')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $peakHours = collect(range(6, 20))->map(fn ($h) => [
            'hour'  => sprintf('%02d:00', $h),
            'count' => (int) ($peakRaw[$h] ?? 0),
        ]);

        // ── Inventory by category ──────────────────────────────
        $inventoryByCategory = DB::table('inventory_items')
            ->where('doctor_id', $doctor->id)
            ->select(
                'category',
                DB::raw('count(*) as items'),
                DB::raw('sum(current_stock) as total_stock')
            )
            ->groupBy('category')
            ->orderByDesc('total_stock')
            ->get();

        // ── Period stats ───────────────────────────────────────
        $periodBase     = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        $total          = (int) $periodBase->count();
        $completed      = (int) (clone $periodBase)->where('status', 'completed')->count();
        $cancelled      = (int) (clone $periodBase)->where('status', 'cancelled')->count();
        $uniquePatients = (int) (clone $periodBase)->distinct('patient_email')->count('patient_email');

        return Inertia::render('Doctor/Analytics', [
            'doctor'  => $doctor,
            'filters' => [
                'period'    => $request->get('period', '30d'),
                'date_from' => $request->get('date_from'),
                'date_to'   => $request->get('date_to'),
            ],
            'stats' => [
                'total'           => $total,
                'completed'       => $completed,
                'cancelled'       => $cancelled,
                'revenue'         => $completed * $fee,
                'unique_patients' => $uniquePatients,
            ],
            'appointment_trend'      => $appointmentTrend->values(),
            'status_breakdown'       => $statusBreakdown,
            'type_breakdown'         => $typeBreakdown,
            'monthly_revenue'        => $monthlyRevenue->values(),
            'top_diagnoses'          => $topDiagnoses,
            'peak_hours'             => $peakHours->values(),
            'inventory_by_category'  => $inventoryByCategory,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->assertPermission($request, 'appointments.view');
        $doctor = $this->getDoctor($request);
        [$dateFrom, $dateTo] = $this->parsePeriod($request);

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get(['reference', 'patient_name', 'patient_email', 'patient_phone',
                   'appointment_date', 'appointment_time', 'status', 'appointment_type', 'reason']);

        $filename = 'doctor-analytics-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($appointments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Patient', 'Email', 'Phone', 'Date', 'Time', 'Status', 'Type', 'Reason']);
            foreach ($appointments as $a) {
                fputcsv($out, [
                    $a->reference,
                    $a->patient_name,
                    $a->patient_email,
                    $a->patient_phone,
                    $a->appointment_date->format('Y-m-d'),
                    $a->appointment_time,
                    $a->status,
                    $a->appointment_type,
                    $a->reason ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
