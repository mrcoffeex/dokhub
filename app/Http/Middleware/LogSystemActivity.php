<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LogSystemActivity
{
    private const SKIP_EXTENSIONS = ['js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map'];
    private const SKIP_PREFIXES   = ['_debugbar', 'telescope', 'horizon', 'livewire/update'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldSkip($request)) {
            return $response;
        }

        try {
            $user   = $request->user();
            $method = $request->method();
            $path   = $request->path();
            $status = $response->getStatusCode();

            $agent = new \Jenssegers\Agent\Agent();
            $agent->setUserAgent($request->userAgent() ?? '');

            $device   = $agent->isPhone() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : ($agent->isDesktop() ? 'Desktop' : 'Robot'));
            $browser  = $agent->browser() ?: 'Unknown';
            $os       = $agent->platform() ?: 'Unknown';
            $isBot    = $agent->isRobot();

            $description = sprintf('[%s] %s %s → %d', $user ? $user->name : 'Guest', $method, '/' . ltrim($path, '/'), $status);

            $logBuilder = activity('system')
                ->withProperties([
                    'method'     => $method,
                    'url'        => $request->fullUrl(),
                    'path'       => '/' . ltrim($path, '/'),
                    'status'     => $status,
                    'device'     => $device,
                    'browser'    => $browser,
                    'os'         => $os,
                    'is_bot'     => $isBot,
                    'robot'      => $isBot ? ($agent->robot() ?: 'Bot') : null,
                    'referrer'   => $request->header('referer'),
                ]);

            if ($user) {
                $logBuilder->causedBy($user);
            }

            $log = $logBuilder->log($description);

            if ($log !== null) {
                DB::table(config('activitylog.table_name', 'activity_log'))
                    ->where('id', $log->id)
                    ->update([
                        'ip_address' => $this->resolveIp($request),
                        'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                    ]);
            }
        } catch (\Throwable) {
            // Logging must never break the application
        }

        return $response;
    }

    private function resolveIp(Request $request): string
    {
        // Support common proxy headers
        foreach (['X-Forwarded-For', 'X-Real-IP', 'CF-Connecting-IP'] as $header) {
            $value = $request->header($header);
            if ($value) {
                $ip = trim(explode(',', $value)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $request->ip() ?? '0.0.0.0';
    }

    private function shouldSkip(Request $request): bool
    {
        $path = $request->path();

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if ($ext && in_array(strtolower($ext), self::SKIP_EXTENSIONS, true)) {
            return true;
        }

        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        // Skip Vite HMR / hot reload pings
        if ($request->header('X-Inertia') === null && str_contains($request->path(), '__vite')) {
            return true;
        }

        return false;
    }
}
