<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnforceAdminIdleTimeout
{
    public const SESSION_ACTIVITY_KEY = 'admin_last_activity_at';

    public const SESSION_CLOSING_KEY = 'admin_closing_at';

    public const SESSION_LOGIN_AT_KEY = 'admin_logged_in_at';

    /**
     * Enforce admin idle timeout (default 15 minutes) and browser/tab close grace timeout (default 60 seconds).
     * Background polling requests validate expiry without extending activity or clearing close state.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $idleMinutes = max(1, (int) config('session.admin_idle_timeout', 15));
        $idleTimeoutSeconds = $idleMinutes * 60;
        $closeGraceSeconds = max(10, (int) config('session.admin_close_grace_seconds', 60));
        $now = now()->timestamp;

        $lastActivity = $request->session()->get(self::SESSION_ACTIVITY_KEY);
        $closingAt = $request->session()->get(self::SESSION_CLOSING_KEY);

        $isClosedTooLong = $closingAt !== null && ($now - (int) $closingAt) >= $closeGraceSeconds;
        $isIdleExpired = ($lastActivity === null && Auth::viaRemember())
            || ($lastActivity !== null && ($now - (int) $lastActivity) >= $idleTimeoutSeconds);

        if ($isClosedTooLong || $isIdleExpired) {
            AuthController::terminateUserSession($request);

            if ($isClosedTooLong) {
                $message = "Sesi Admin telah berakhir otomatis karena halaman admin ditutup lebih dari {$closeGraceSeconds} detik. Silakan login kembali.";
                $loginUrl = route('login', ['close_timeout' => 1]);
            } else {
                $message = "Sesi Admin telah berakhir otomatis karena tidak ada aktivitas selama {$idleMinutes} menit. Silakan login kembali.";
                $loginUrl = route('login', ['idle_timeout' => 1]);
            }

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

        if (! $request->session()->has(self::SESSION_LOGIN_AT_KEY)) {
            $request->session()->put(self::SESSION_LOGIN_AT_KEY, $now);
        }

        $isBackgroundPoll = $request->routeIs('admin.live-chat.poll')
            || $request->headers->get('X-Background-Poll') === '1';
        $isClosingBeacon = $request->routeIs('admin.session.closing');
        $isActivePing = $request->routeIs('admin.session.active');

        if (! $isBackgroundPoll && ! $isClosingBeacon) {
            $request->session()->forget(self::SESSION_CLOSING_KEY);
            Cache::forget(AuthController::closingCacheKey((int) Auth::id()));
        }

        if ((! $isBackgroundPoll && ! $isClosingBeacon && ! $isActivePing) || $lastActivity === null) {
            $request->session()->put(self::SESSION_ACTIVITY_KEY, $now);
        }

        return $next($request);
    }
}
