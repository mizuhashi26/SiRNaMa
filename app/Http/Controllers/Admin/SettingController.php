<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\FonnteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(FonnteService $fonnteService): View
    {
        return view('admin.settings.index', [
            'fonnteConfigured' => $fonnteService->isConfigured(),
            'fonnteToken' => AppSetting::getValue('fonnte_token', config('services.fonnte.token', '')),
            'fonnteApiUrl' => AppSetting::getValue('fonnte_api_url', config('services.fonnte.api_url', 'https://api.fonnte.com/send')),
            'institutionName' => AppSetting::getValue('app_institution_name', 'Universitas Pamulang'),
            'logoText' => AppSetting::getValue('app_logo_text', 'UNPAM'),
            'tagline' => AppSetting::getValue('app_tagline', 'Layanan Mahasiswa'),
            'footerNote' => AppSetting::getValue('app_footer_note', 'Portal Informasi & Layanan Mahasiswa UNPAM'),
            'adminPhone' => AppSetting::getValue('admin_contact_phone', '6281234567890'),
            'logoImage' => AppSetting::getValue('app_logo_image', null),
            'themePrimaryColor' => AppSetting::getValue('theme_primary_color', '#0F294A'),
            'themeAccentColor' => AppSetting::getValue('theme_accent_color', '#EAB308'),
            'adminName' => auth()->user()?->name ?? 'Admin Layanan',
            'webhookUrl' => url('/api/webhook/fonnte'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_institution_name' => ['required', 'string', 'max:150'],
            'app_logo_text' => ['nullable', 'string', 'max:20'],
            'app_tagline' => ['required', 'string', 'max:150'],
            'app_footer_note' => ['nullable', 'string', 'max:255'],
            'admin_contact_phone' => ['nullable', 'string', 'max:30'],
            'admin_name' => ['nullable', 'string', 'max:100'],
            'theme_primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'theme_accent_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'fonnte_token' => ['nullable', 'string', 'max:255'],
            'fonnte_api_url' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'remove_logo_image' => ['nullable', 'boolean'],
        ], [
            'app_institution_name.required' => 'Nama institusi kampus wajib diisi.',
            'app_logo_text.required' => 'Teks / Singkatan logo wajib diisi.',
            'app_tagline.required' => 'Tagline portal layanan wajib diisi.',
            'theme_primary_color.regex' => 'Format warna utama tidak valid (contoh: #0F294A).',
            'theme_accent_color.regex' => 'Format warna aksen tidak valid (contoh: #EAB308).',
            'logo_file.image' => 'File logo harus berupa gambar (PNG, JPG, SVG, WebP).',
            'logo_file.max' => 'Ukuran file logo maksimal 2MB.',
        ]);

        AppSetting::setValue('app_institution_name', trim($validated['app_institution_name']), 'identity', 'Nama Institusi Kampus');
        if (!empty($validated['app_logo_text'])) {
            AppSetting::setValue('app_logo_text', strtoupper(trim($validated['app_logo_text'])), 'identity', 'Singkatan / Teks Logo');
        }
        AppSetting::setValue('app_tagline', trim($validated['app_tagline']), 'identity', 'Tagline Layanan');
        AppSetting::setValue('app_footer_note', trim($validated['app_footer_note'] ?? ''), 'identity', 'Teks Footer');
        AppSetting::setValue('admin_contact_phone', trim($validated['admin_contact_phone'] ?? ''), 'contact', 'Nomor Kontak Admin');

        // Pengaturan Warna Web
        $themePrimary = !empty($validated['theme_primary_color']) ? strtolower(trim($validated['theme_primary_color'])) : '#0F294A';
        $themeAccent = !empty($validated['theme_accent_color']) ? strtolower(trim($validated['theme_accent_color'])) : '#EAB308';
        AppSetting::setValue('theme_primary_color', $themePrimary, 'theme', 'Warna Utama Web');
        AppSetting::setValue('theme_accent_color', $themeAccent, 'theme', 'Warna Aksen Web');

        // Pengaturan Nama Admin
        if (!empty($validated['admin_name'])) {
            $adminName = trim($validated['admin_name']);
            AppSetting::setValue('admin_name', $adminName, 'identity', 'Nama Admin Pengelola');
            if ($user = $request->user()) {
                $user->name = $adminName;
                $user->save();
            }
        }

        // Pengaturan Fonnte API
        if (array_key_exists('fonnte_token', $validated)) {
            AppSetting::setValue('fonnte_token', trim($validated['fonnte_token'] ?? ''), 'api', 'Token WhatsApp Fonnte');
        }
        if (array_key_exists('fonnte_api_url', $validated)) {
            AppSetting::setValue('fonnte_api_url', trim($validated['fonnte_api_url'] ?? 'https://api.fonnte.com/send'), 'api', 'Endpoint URL API Fonnte');
        }

        // Sinkronisasi otomatis ke .env agar aman dari reset database (hanya di lingkungan yang mengizinkan penulisan file)
        if (! empty($validated['fonnte_token'])) {
            $this->syncToEnvFile([
                'FONNTE_TOKEN' => trim($validated['fonnte_token']),
                'FONNTE_API_URL' => trim($validated['fonnte_api_url'] ?? 'https://api.fonnte.com/send'),
            ]);
        }

        // Penanganan Logo File (disimpan sebagai Data URI di database agar kompatibel dengan serverless read-only)
        if ($request->boolean('remove_logo_image')) {
            $oldLogo = AppSetting::getValue('app_logo_image');
            if ($oldLogo && ! str_starts_with($oldLogo, 'data:') && file_exists(public_path($oldLogo))) {
                @unlink(public_path($oldLogo));
            }
            AppSetting::setValue('app_logo_image', null, 'identity', 'Data/Path Logo Gambar');
        } elseif ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $mime = $ext === 'svg' ? 'image/svg+xml' : ($file->getMimeType() ?: 'image/png');
            $contents = file_get_contents($file->getRealPath());

            if ($contents !== false) {
                $dataUri = 'data:' . $mime . ';base64,' . base64_encode($contents);
                AppSetting::setValue('app_logo_image', $dataUri, 'identity', 'Data/Path Logo Gambar');
            }
        }

        AppSetting::clearSettingCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan sistem, identitas logo, dan API WhatsApp berhasil diperbarui.');
    }

    /**
     * Sinkronisasi nilai kredensial ke file .env lokal agar tidak hilang saat database di-reset.
     */
    protected function syncToEnvFile(array $data): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $envPath = base_path('.env');
        if (! file_exists($envPath) || ! is_writable($envPath)) {
            return;
        }

        try {
            $content = file_get_contents($envPath);
            foreach ($data as $key => $val) {
                $valStr = (string) $val;
                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$valStr}", $content);
                } else {
                    $content = rtrim($content) . "\n{$key}={$valStr}\n";
                }
            }
            @file_put_contents($envPath, $content);
        } catch (\Throwable) {
            // Fail safely if .env is write-restricted
        }
    }
}

