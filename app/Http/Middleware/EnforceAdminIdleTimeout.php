<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceAdminIdleTimeout
{
    public const SESSION_ACTIVITY_KEY = 'admin_last_activity_at';

    /**
     * Enforce idle timeout on authenticated admin routes.
     * Background polling requests validate expiry without extending the activity timestamp.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $idleMinutes = max(1, (int) config('session.admin_idle_timeout', 30));
        $timeoutSeconds = $idleMinutes * 60;
        $now = now()->timestamp;

        $lastActivity = $request->session()->get(self::SESSION_ACTIVITY_KEY);
        $isExpired = ($lastActivity === null && Auth::viaRemember())
            || ($lastActivity !== null && ($now - (int) $lastActivity) >= $timeoutSeconds);

        if ($isExpired) {
            AuthController::terminateUserSession($request);

            $message = "Sesi Admin telah berakhir otomatis karena tidak ada aktivitas selama {$idleMinutes} menit. Silakan login kembali.";
            $loginUrl = route('login', ['idle_timeout' => 1]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect' => $loginUrl,
                ], 401)->withHeaders(AuthController::NO_CACHE_HEADERS);
            }

            return redirect($loginUrl)
                ->with('warning', $message)
                ->withHeaders(AuthController::NO_CACHE_HEADERS);
        }

        $isBackgroundPoll = $request->routeIs('admin.live-chat.poll')
            || $request->headers->get('X-Background-Poll') === '1';

        if (! $isBackgroundPoll || $lastActivity === null) {
            $request->session()->put(self::SESSION_ACTIVITY_KEY, $now);
        }

        return $next($request);
    }
}
