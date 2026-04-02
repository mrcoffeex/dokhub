<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentLogsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = PaymentLog::with('doctor:id,name,email,slug')
            ->latest();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('doctor', fn ($q) =>
                $q->where('name', 'ilike', "%{$term}%")
                  ->orWhere('email', 'ilike', "%{$term}%")
            );
        }

        if ($request->filled('billing_period')) {
            $query->where('billing_period', $request->billing_period);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total'          => PaymentLog::count(),
            'total_revenue'  => PaymentLog::where('status', 'completed')->sum('amount'),
            'this_month'     => PaymentLog::where('status', 'completed')
                                    ->whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->count(),
            'monthly_subs'   => PaymentLog::where('billing_period', 'monthly')->count(),
            'yearly_subs'    => PaymentLog::where('billing_period', 'yearly')->count(),
        ];

        return Inertia::render('Admin/PaymentLogs/Index', [
            'logs'    => $logs,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'billing_period', 'source']),
        ]);
    }
}
