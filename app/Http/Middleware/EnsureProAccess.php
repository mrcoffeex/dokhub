<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = $request->user()?->doctor;

        // For sub-users, resolve the doctor through their profile
        if (! $doctor && $request->user()?->isSubUser()) {
            $doctor = $request->user()->subUserProfile?->doctor;
        }

        if (!$doctor || !$doctor->hasProAccess()) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'This feature requires a Pro plan.',
                    'upgrade_url' => route('doctor.billing'),
                ], 403);
            }

            return redirect()->route('doctor.billing')
                ->with('error', 'This feature requires a Pro plan. Upgrade to continue.');
        }

        return $next($request);
    }
}
