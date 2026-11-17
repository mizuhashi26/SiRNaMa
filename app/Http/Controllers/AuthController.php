<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnforceAdminIdleTimeout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public const NO_CACHE_HEADERS = [
        'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => 'Sun, 02 Jan 1990 00:00:00 GMT',
    ];

    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $redirectRoute = Auth::user()->isAdminChat() ? route('admin.live-chat.index') : route('admin.dashboard');

            return redirect($redirectRoute);
        }

        $idleWarning = null;
        if ($request->boolean('idle_timeout') && ! session()->has('warning')) {
            $idleMinutes = max(1, (int) config('session.admin_idle_timeout', 30));
            $idleWarning = "Sesi Admin telah berakhir otomatis karena tidak ada aktivitas selama {$idleMinutes} menit. Silakan login kembali.";
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
                max(1, (int) config('session.lifetime', 30)),
                max(1, (int) config('session.admin_idle_timeout', 30))
            );
            $expiryThreshold = now()->subMinutes($lifetimeMinutes)->getTimestamp();
            $currentSessionId = $request->session()->getId();

            // Bersihkan sesi yang sudah kedaluwarsa untuk akun ini agar tidak mengunci permanen
            DB::table($sessionTable)
                ->where('user_id', $user->getAuthIdentifier())
                ->where('last_activity', '<', $expiryThreshold)
                ->delete();

            $hasActiveOtherSession = DB::table($sessionTable)
                ->where('user_id', $user->getAuthIdentifier())
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
        $request->session()->put(EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY, now()->timestamp);

        $defaultRoute = Auth::user()->isAdminChat() ? route('admin.live-chat.index') : route('admin.dashboard');

        return redirect()->intended($defaultRoute)
            ->with('success', 'Berhasil masuk ke Dashboard Admin CRM.');
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        self::terminateUserSession($request);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda telah keluar dari sesi Admin.',
                'redirect' => route('login', $request->boolean('auto_logout') ? ['idle_timeout' => 1] : []),
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

        // Bersihkan seluruh sisa sesi milik akun ini di tabel sessions agar perangkat lain langsung bisa masuk
        if ($resolvedUserId && config('session.driver') === 'database') {
            DB::table((string) config('session.table', 'sessions'))
                ->where('user_id', $resolvedUserId)
                ->delete();
        }
    }
}

