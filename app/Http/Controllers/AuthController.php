<?php

/**
 * Sistem Informasi Reminder dan Layanan Mahasiswa (SiRNaMa)
 * @author Muhammad Hilmi Aaqilah - NIM 231011701051
 * @copyright 2026 MHA-Project. All Rights Reserved.
 * @license Academic & Personal Use License (Non-Commercial)
 */

namespace App\Http\Controllers;

use App\Http\Middleware\EnforceAdminIdleTimeout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public const NO_CACHE_HEADERS = [
        'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => 'Sun, 02 Jan 1990 00:00:00 GMT',
    ];

    public static function closingCacheKey(int $userId): string
    {
        return "admin_closing:{$userId}";
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $redirectRoute = Auth::user()->isAdminChat() ? route('admin.live-chat.index') : route('admin.dashboard');

            return redirect($redirectRoute);
        }

        $idleWarning = null;
        if (! session()->has('warning')) {
            if ($request->boolean('close_timeout')) {
                $closeGraceSeconds = max(10, (int) config('session.admin_close_grace_seconds', 60));
                $idleWarning = "Sesi Admin telah berakhir otomatis karena halaman admin ditutup lebih dari {$closeGraceSeconds} detik. Silakan login kembali.";
            } elseif ($request->boolean('idle_timeout')) {
                $idleMinutes = max(1, (int) config('session.admin_idle_timeout', 15));
                $idleWarning = "Sesi Admin telah berakhir otomatis karena tidak ada aktivitas selama {$idleMinutes} menit. Silakan login kembali.";
            }
        }

        return view('auth.login', compact('idleWarning'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        $provider = Auth::getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])
                ->onlyInput('email');
        }

        // Opsi 2: Tolak login baru jika akun masih memiliki sesi aktif di perangkat/browser lain
        if (config('session.driver') === 'database') {
            $sessionTable = (string) config('session.table', 'sessions');
            $lifetimeMinutes = min(
                max(1, (int) config('session.lifetime', 15)),
                max(1, (int) config('session.admin_idle_timeout', 15))
            );
            $expiryThreshold = now()->subMinutes($lifetimeMinutes)->getTimestamp();
            $currentSessionId = $request->session()->getId();

            // Jika perangkat sebelumnya sudah menutup tab admin > 60 detik, bebaskan sesi lamanya
            $userId = (int) $user->getAuthIdentifier();
            $closingInfo = Cache::get(self::closingCacheKey($userId));
            $closeGraceSeconds = max(10, (int) config('session.admin_close_grace_seconds', 60));
            if (
                is_array($closingInfo)
                && ! empty($closingInfo['session_id'])
                && isset($closingInfo['closed_at'])
                && (now()->timestamp - (int) $closingInfo['closed_at']) >= $closeGraceSeconds
            ) {
                DB::table($sessionTable)
                    ->where('user_id', $userId)
                    ->where('id', (string) $closingInfo['session_id'])
                    ->delete();
                Cache::forget(self::closingCacheKey($userId));
            }

            // Bersihkan sesi yang sudah kedaluwarsa untuk akun ini agar tidak mengunci permanen
            DB::table($sessionTable)
                ->where('user_id', $userId)
                ->where('last_activity', '<', $expiryThreshold)
                ->delete();

            $hasActiveOtherSession = DB::table($sessionTable)
                ->where('user_id', $userId)
                ->where('id', '!=', $currentSessionId)
                ->where('last_activity', '>=', $expiryThreshold)
                ->exists();

            if ($hasActiveOtherSession) {
                return back()
                    ->withErrors(['email' => 'Akun ini sedang aktif digunakan di perangkat atau browser lain. Silakan keluar terlebih dahulu dari sesi sebelumnya.'])
                    ->onlyInput('email');
            }
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $loginNow = now()->timestamp;
        $request->session()->put(EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY, $loginNow);
        $request->session()->put(EnforceAdminIdleTimeout::SESSION_LOGIN_AT_KEY, $loginNow);
        $request->session()->forget(EnforceAdminIdleTimeout::SESSION_CLOSING_KEY);
        Cache::forget(self::closingCacheKey((int) $user->getAuthIdentifier()));

        $defaultRoute = Auth::user()->isAdminChat() ? route('admin.live-chat.index') : route('admin.dashboard');

        return redirect()->intended($defaultRoute)
            ->with('success', 'Berhasil masuk ke Dashboard Admin CRM.');
    }

    public function sessionClosing(Request $request): HttpResponse
    {
        $now = now()->timestamp;
        $request->session()->put(EnforceAdminIdleTimeout::SESSION_CLOSING_KEY, $now);

        if (Auth::id()) {
            $ttlSeconds = max(120, (int) config('session.admin_idle_timeout', 15) * 60);
            Cache::put(
                self::closingCacheKey((int) Auth::id()),
                [
                    'session_id' => $request->session()->getId(),
                    'closed_at' => $now,
                ],
                $ttlSeconds
            );
        }

        return response()->noContent()->withHeaders(self::NO_CACHE_HEADERS);
    }

    public function sessionActive(Request $request): JsonResponse
    {
        $request->session()->forget(EnforceAdminIdleTimeout::SESSION_CLOSING_KEY);
        if (Auth::id()) {
            Cache::forget(self::closingCacheKey((int) Auth::id()));
        }

        return response()->json(['ok' => true])->withHeaders(self::NO_CACHE_HEADERS);
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        self::terminateUserSession($request);

        if ($request->expectsJson()) {
            $query = [];
            if ($request->boolean('close_timeout')) {
                $query = ['close_timeout' => 1];
            } elseif ($request->boolean('auto_logout')) {
                $query = ['idle_timeout' => 1];
            }

            return response()->json([
                'message' => 'Anda telah keluar dari sesi Admin.',
                'redirect' => route('login', $query),
            ])->withHeaders(self::NO_CACHE_HEADERS);
        }

        return redirect()->route('login')
            ->with('success', 'Anda telah keluar dari sesi Admin.')
            ->withHeaders(self::NO_CACHE_HEADERS);
    }

    /**
     * Log out the current user, invalidate session + CSRF token, and purge database session rows.
     */
    public static function terminateUserSession(Request $request, ?int $userId = null): void
    {
        $resolvedUserId = $userId ?? Auth::id();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($resolvedUserId) {
            Cache::forget(self::closingCacheKey((int) $resolvedUserId));

            // Bersihkan seluruh sisa sesi milik akun ini di tabel sessions agar perangkat lain langsung bisa masuk
            if (config('session.driver') === 'database') {
                DB::table((string) config('session.table', 'sessions'))
                    ->where('user_id', $resolvedUserId)
                    ->delete();
            }
        }
    }
}

