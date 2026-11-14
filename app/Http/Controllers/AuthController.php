<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $redirectRoute = Auth::user()->isAdminChat() ? route('admin.live-chat.index') : route('admin.dashboard');

            return redirect($redirectRoute);
        }

        return view('auth.login');
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
            $lifetimeMinutes = (int) config('session.lifetime', 120);
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

        $defaultRoute = Auth::user()->isAdminChat() ? route('admin.live-chat.index') : route('admin.dashboard');

        return redirect()->intended($defaultRoute)
            ->with('success', 'Berhasil masuk ke Dashboard Admin CRM.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Bersihkan seluruh sisa sesi milik akun ini di tabel sessions agar perangkat lain langsung bisa masuk
        if ($userId && config('session.driver') === 'database') {
            DB::table((string) config('session.table', 'sessions'))
                ->where('user_id', $userId)
                ->delete();
        }

        return redirect()->route('login')
            ->with('success', 'Anda telah keluar dari sesi Admin.')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => 'Sun, 02 Jan 1990 00:00:00 GMT',
            ]);
    }
}

