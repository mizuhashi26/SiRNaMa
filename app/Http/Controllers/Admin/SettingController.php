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
            'webhookUrl' => url('/api/webhook/fonnte'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_institution_name' => ['required', 'string', 'max:150'],
            'app_logo_text' => ['required', 'string', 'max:20'],
            'app_tagline' => ['required', 'string', 'max:150'],
            'app_footer_note' => ['nullable', 'string', 'max:255'],
            'admin_contact_phone' => ['nullable', 'string', 'max:30'],
            'fonnte_token' => ['nullable', 'string', 'max:255'],
            'fonnte_api_url' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'remove_logo_image' => ['nullable', 'boolean'],
        ], [
            'app_institution_name.required' => 'Nama institusi kampus wajib diisi.',
            'app_logo_text.required' => 'Teks / Singkatan logo wajib diisi.',
            'app_tagline.required' => 'Tagline portal layanan wajib diisi.',
            'logo_file.image' => 'File logo harus berupa gambar (PNG, JPG, SVG, WebP).',
            'logo_file.max' => 'Ukuran file logo maksimal 2MB.',
        ]);

        AppSetting::setValue('app_institution_name', trim($validated['app_institution_name']), 'identity', 'Nama Institusi Kampus');
        AppSetting::setValue('app_logo_text', strtoupper(trim($validated['app_logo_text'])), 'identity', 'Singkatan / Teks Logo');
        AppSetting::setValue('app_tagline', trim($validated['app_tagline']), 'identity', 'Tagline Layanan');
        AppSetting::setValue('app_footer_note', trim($validated['app_footer_note'] ?? ''), 'identity', 'Teks Footer');
        AppSetting::setValue('admin_contact_phone', trim($validated['admin_contact_phone'] ?? ''), 'contact', 'Nomor Kontak Admin');

        // Pengaturan Fonnte API
        AppSetting::setValue('fonnte_token', trim($validated['fonnte_token'] ?? ''), 'api', 'Token WhatsApp Fonnte');
        AppSetting::setValue('fonnte_api_url', trim($validated['fonnte_api_url'] ?: 'https://api.fonnte.com/send'), 'api', 'Endpoint URL API Fonnte');

        // Penanganan Logo File
        if ($request->boolean('remove_logo_image')) {
            $oldLogo = AppSetting::getValue('app_logo_image');
            if ($oldLogo && file_exists(public_path($oldLogo))) {
                @unlink(public_path($oldLogo));
            }
            AppSetting::setValue('app_logo_image', null, 'identity', 'Path Logo Gambar');
        } elseif ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('uploads/branding');

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $file->move($targetDir, $filename);
            AppSetting::setValue('app_logo_image', 'uploads/branding/' . $filename, 'identity', 'Path Logo Gambar');
        }

        AppSetting::clearSettingCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan sistem, identitas logo, dan API WhatsApp berhasil diperbarui.');
    }
}

