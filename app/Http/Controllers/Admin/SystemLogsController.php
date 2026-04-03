<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemLogsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ActivityLog::with('causer:id,name,email,role')
            ->where('log_name', 'system')
            ->latest();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('description', 'ilike', "%{$term}%")
                  ->orWhere('ip_address', 'ilike', "%{$term}%")
                  ->orWhereHas('causer', fn ($u) =>
                      $u->where('name', 'ilike', "%{$term}%")
                        ->orWhere('email', 'ilike', "%{$term}%")
                  );
            });
        }

        if ($request->filled('method')) {
            $query->whereJsonContains('properties->method', strtoupper($request->method_filter));
        }

        if ($request->filled('causer_type')) {
            if ($request->causer_type === 'guest') {
                $query->whereNull('causer_id');
            } elseif ($request->causer_type === 'user') {
                $query->whereNotNull('causer_id');
            }
        }

        if ($request->filled('device')) {
            $query->whereJsonContains('properties->device', $request->device);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();

        $stats = [
            'total'       => ActivityLog::where('log_name', 'system')->count(),
            'today'       => ActivityLog::where('log_name', 'system')->whereDate('created_at', today())->count(),
            'unique_ips'  => ActivityLog::where('log_name', 'system')
                                ->whereNotNull('ip_address')
                                ->distinct('ip_address')
                                ->count('ip_address'),
            'guests'      => ActivityLog::where('log_name', 'system')->whereNull('causer_id')->count(),
            'users'       => ActivityLog::where('log_name', 'system')->whereNotNull('causer_id')->count(),
        ];

        return Inertia::render('Admin/SystemLogs/Index', [
            'logs'    => $logs,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'method_filter', 'causer_type', 'device', 'date_from', 'date_to']),
        ]);
    }
}
