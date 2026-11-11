@extends('layouts.admin')

@section('title', 'Pengaturan Sistem & API - Admin UNPAM')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sistem &amp; Konfigurasi API</h1>
            <p class="text-sm text-slate-600 mt-1">Kelola identitas kampus, logo website, API WhatsApp Fonnte, dan informasi kontak layanan.</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Tersimpan di Database
            </span>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Bagian 1: Identitas & Logo Website -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-unpam-navy text-unpam-gold flex items-center justify-center font-bold text-sm">
                        ID
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Identitas &amp; Logo Website</h2>
                        <p class="text-xs text-slate-500">Nama institusi, badge logo, file gambar logo, tagline dan footer publik.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="app_institution_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Institusi Kampus <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="app_institution_name"
                           name="app_institution_name"
                           value="{{ old('app_institution_name', $institutionName) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-unpam-navy focus:border-unpam-navy">
                    <p class="text-[11px] text-slate-500 mt-1">Ditampilkan pada judul halaman web dan navigasi.</p>
                </div>

                <div>
                    <label for="app_tagline" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tagline / Subjudul Portal <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="app_tagline"
                           name="app_tagline"
                           value="{{ old('app_tagline', $tagline) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-unpam-navy focus:border-unpam-navy">
                    <p class="text-[11px] text-slate-500 mt-1">Slogan yang tampil pada banner website dan chatbot.</p>
                </div>

                <div class="md:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <label for="logo_file" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                        Upload Logo Resmi (Hanya Logo Gambar Transparan)
                    </label>
                    <p class="text-xs text-slate-600 mb-3">
                        Logo akan otomatis berlatar belakang transparan mengikuti gambar PNG / SVG yang diunggah dan diperbesar secara proporsional di header publik, dashboard, dan login.
                    </p>
                    <input type="file"
                           id="logo_file"
                           name="logo_file"
                           accept="image/png,image/jpeg,image/svg+xml,image/webp"
                           class="w-full px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-4 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-unpam-navy file:text-white hover:file:bg-slate-800">
                    <p class="text-[11px] text-slate-500 mt-1.5">Format disarankan: PNG transparan atau SVG. Ukuran maksimal 2MB.</p>

                    <div class="mt-4 p-4 bg-unpam-navy rounded-lg border border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-2 bg-slate-900/50 rounded-lg flex items-center justify-center">
                                @if(!empty($logoImage) && file_exists(public_path($logoImage)))
                                    <img src="{{ asset($logoImage) }}" alt="Logo Aktif" class="h-14 sm:h-16 w-auto max-w-[140px] object-contain bg-transparent">
                                @else
                                    <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Default" class="h-14 sm:h-16 w-14 sm:w-16 object-contain bg-transparent drop-shadow">
                                @endif
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-white">Preview Logo di Header (Latar Gelap Navy)</span>
                                <span class="block text-[11px] text-slate-300">
                                    {{ !empty($logoImage) ? 'Menggunakan logo khusus yang diupload.' : 'Menggunakan logo default SVG transparan.' }}
                                </span>
                            </div>
                        </div>
                        @if(!empty($logoImage))
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-red-900/80 hover:bg-red-800 text-xs text-red-200 border border-red-700 cursor-pointer">
                                <input type="checkbox" name="remove_logo_image" value="1" class="rounded border-red-500 text-red-600 focus:ring-red-500">
                                <span class="font-bold">Reset ke Logo Bawaan</span>
                            </label>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label for="app_footer_note" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Teks Hak Cipta &amp; Footer
                    </label>
                    <input type="text"
                           id="app_footer_note"
                           name="app_footer_note"
                           value="{{ old('app_footer_note', $footerNote) }}"
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-unpam-navy focus:border-unpam-navy">
                </div>
            </div>
        </div>

        <!-- Bagian 2: Kustomisasi Warna & Tema Web -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-unpam-navy to-unpam-gold text-white flex items-center justify-center font-bold text-sm shadow-xs">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.594M7 21h10a4 4 0 004-4 5 5 0 00-3-4.594M7 21v-2a4 4 0 014-4h2a4 4 0 014 4v2M12 7a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Kustomisasi Warna &amp; Tema Web</h2>
                        <p class="text-xs text-slate-500">Ubah palet warna utama (primary) dan warna aksen tombol website sesuai kebutuhan.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Preset Cepat Warna -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilihan Preset Warna Harmonik (Anti-Slop)
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                        <button type="button" onclick="applyColorPreset('#0F294A', '#EAB308')"
                                class="p-2.5 rounded-lg border border-slate-300 bg-white hover:border-slate-400 text-left transition-all hover:shadow-xs group">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #0F294A;"></span>
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #EAB308;"></span>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 group-hover:text-blue-900">UNPAM Standar</span>
                            <span class="block text-[10px] text-slate-500 font-mono">Navy &amp; Emas</span>
                        </button>

                        <button type="button" onclick="applyColorPreset('#1E3A8A', '#F59E0B')"
                                class="p-2.5 rounded-lg border border-slate-300 bg-white hover:border-slate-400 text-left transition-all hover:shadow-xs group">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #1E3A8A;"></span>
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #F59E0B;"></span>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 group-hover:text-blue-900">Royal Blue</span>
                            <span class="block text-[10px] text-slate-500 font-mono">Blue &amp; Amber</span>
                        </button>

                        <button type="button" onclick="applyColorPreset('#1E1B4B', '#D97706')"
                                class="p-2.5 rounded-lg border border-slate-300 bg-white hover:border-slate-400 text-left transition-all hover:shadow-xs group">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #1E1B4B;"></span>
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #D97706;"></span>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 group-hover:text-blue-900">Midnight Indigo</span>
                            <span class="block text-[10px] text-slate-500 font-mono">Indigo &amp; Warm</span>
                        </button>

                        <button type="button" onclick="applyColorPreset('#064E3B', '#EAB308')"
                                class="p-2.5 rounded-lg border border-slate-300 bg-white hover:border-slate-400 text-left transition-all hover:shadow-xs group">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #064E3B;"></span>
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #EAB308;"></span>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 group-hover:text-blue-900">Emerald Hijau</span>
                            <span class="block text-[10px] text-slate-500 font-mono">Emerald &amp; Gold</span>
                        </button>

                        <button type="button" onclick="applyColorPreset('#0C4A6E', '#D97706')"
                                class="p-2.5 rounded-lg border border-slate-300 bg-white hover:border-slate-400 text-left transition-all hover:shadow-xs group">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #0C4A6E;"></span>
                                <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: #D97706;"></span>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 group-hover:text-blue-900">Ocean Cyan</span>
                            <span class="block text-[10px] text-slate-500 font-mono">Cyan &amp; Bronze</span>
                        </button>
                    </div>
                </div>

                <!-- Input Custom Warna -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                    <div>
                        <label for="theme_primary_color" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Warna Utama Web (Header, Button Utama, Badge) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color"
                                   id="primary_picker"
                                   value="{{ old('theme_primary_color', $themePrimaryColor) }}"
                                   oninput="updatePrimaryColor(this.value)"
                                   class="w-12 h-11 rounded-lg border border-slate-300 cursor-pointer p-0.5 bg-white">
                            <input type="text"
                                   id="theme_primary_color"
                                   name="theme_primary_color"
                                   value="{{ old('theme_primary_color', $themePrimaryColor) }}"
                                   oninput="updatePrimaryColor(this.value, false)"
                                   placeholder="#0F294A"
                                   pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                   class="flex-1 px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm font-mono uppercase focus:ring-2 focus:ring-unpam-navy">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Default UNPAM Navy: <code class="font-mono font-bold">#0F294A</code></p>
                    </div>

                    <div>
                        <label for="theme_accent_color" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Warna Aksen / Sekunder (Tombol Aksi, Highlight, Garis Emas) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color"
                                   id="accent_picker"
                                   value="{{ old('theme_accent_color', $themeAccentColor) }}"
                                   oninput="updateAccentColor(this.value)"
                                   class="w-12 h-11 rounded-lg border border-slate-300 cursor-pointer p-0.5 bg-white">
                            <input type="text"
                                   id="theme_accent_color"
                                   name="theme_accent_color"
                                   value="{{ old('theme_accent_color', $themeAccentColor) }}"
                                   oninput="updateAccentColor(this.value, false)"
                                   placeholder="#EAB308"
                                   pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                   class="flex-1 px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm font-mono uppercase focus:ring-2 focus:ring-unpam-navy">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Default UNPAM Emas: <code class="font-mono font-bold">#EAB308</code></p>
                    </div>
                </div>

                <!-- Preview Komponen Warna Langsung -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-3">
                    <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Preview Hasil Warna:</p>
                    <div id="themePreviewHeader" class="p-3 rounded-lg text-white flex items-center justify-between" style="background-color: {{ old('theme_primary_color', $themePrimaryColor) }}; border-bottom: 3px solid {{ old('theme_accent_color', $themeAccentColor) }};">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ old('theme_accent_color', $themeAccentColor) }};"></span>
                            <span class="text-xs font-bold">Header &amp; Navigasi Portal Layanan</span>
                        </div>
                        <span id="themePreviewBadge" class="px-2.5 py-1 rounded text-[11px] font-black text-slate-950" style="background-color: {{ old('theme_accent_color', $themeAccentColor) }};">
                            Aksen Emas
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Profil & Nama Akun Admin -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-800 text-white flex items-center justify-center font-bold text-sm">
                        <svg class="w-5 h-5 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Profil &amp; Nama Akun Admin</h2>
                        <p class="text-xs text-slate-500">Nama identitas admin yang tampil di navbar dan sebagai akhiran balasan live chat (contoh: -Admin 1).</p>
                    </div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="admin_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap / Nama Admin <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="admin_name"
                           name="admin_name"
                           value="{{ old('admin_name', $adminName) }}"
                           required
                           placeholder="Contoh: Admin 1 atau Nama Lengkap Anda"
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-unpam-navy focus:border-unpam-navy">
                    <p class="text-[11px] text-slate-500 mt-1">Nama ini otomatis muncul pada tombol keluar navbar dan di akhir balasan pesan Live Chat.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email Login
                    </label>
                    <input type="text"
                           value="{{ auth()->user()->email }}"
                           disabled
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-600 text-sm font-mono cursor-not-allowed">
                    <p class="text-[11px] text-slate-500 mt-1">
                        Untuk ubah email/password admin, kelola melalui menu <a href="{{ route('admin.users.index') }}" class="font-bold text-unpam-navy hover:underline">Kelola Admin</a>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Integrasi WhatsApp API (Fonnte Gateway) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                        WA
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Integrasi WhatsApp Gateway (Fonnte)</h2>
                        <p class="text-xs text-slate-500">Koneksi pengiriman reminder otomatis dan chatbot WhatsApp.</p>
                    </div>
                </div>

                @if($fonnteConfigured)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Token Dikonfigurasi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-amber-50 text-amber-800 text-xs font-bold border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Token Belum Diisi / Mode Uji
                    </span>
                @endif
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fonnte_token" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Fonnte Account Token
                        </label>
                        <div class="relative">
                            <input type="password"
                                   id="fonnte_token"
                                   name="fonnte_token"
                                   value="{{ old('fonnte_token', $fonnteToken) }}"
                                   placeholder="Masukkan token dari fonnte.com"
                                   class="w-full px-3.5 py-2.5 pr-10 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <button type="button"
                                    id="toggleTokenVisibility"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                                    title="Lihat / Sembunyikan Token">
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Dapatkan dari dashboard Fonnte (Device &rarr; Connect WhatsApp).</p>
                    </div>

                    <div>
                        <label for="fonnte_api_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Fonnte API Endpoint
                        </label>
                        <input type="url"
                               id="fonnte_api_url"
                               name="fonnte_api_url"
                               value="{{ old('fonnte_api_url', $fonnteApiUrl) }}"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="text-[11px] text-slate-500 mt-1">Default kirim pesan Fonnte: <code class="text-xs text-slate-700 bg-slate-100 px-1 py-0.5 rounded">https://api.fonnte.com/send</code></p>
                    </div>
                </div>

                <!-- Petunjuk Webhook -->
                <div class="rounded-lg bg-slate-50 border border-slate-200 p-4 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        URL Webhook Masuk (Inbound Messages)
                    </h3>
                    <p class="text-xs text-slate-600">
                        Salin URL ini dan masukkan ke pengaturan Webhook di dashboard Fonnte agar pesan dari WhatsApp mahasiswa dapat dibalas oleh bot dan admin secara otomatis:
                    </p>
                    <div class="flex items-center gap-2">
                        <input type="text"
                               readonly
                               value="{{ $webhookUrl }}"
                               id="webhookUrlField"
                               class="flex-1 bg-white px-3 py-2 rounded border border-slate-300 text-xs font-mono text-slate-700 selection:bg-unpam-gold">
                        <button type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('webhookUrlField').value); alert('URL Webhook berhasil disalin!');"
                                class="min-h-[38px] px-3.5 py-2 rounded bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold">
                            Salin URL
                        </button>
                    </div>
                    <p class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded border border-amber-200">
                        <strong>Catatan Pengujian Lokal:</strong> Jika server berjalan di <code class="bg-amber-100 px-1 rounded">localhost</code>, gunakan tunneling (Cloudflare Tunnel atau ngrok) agar Fonnte di cloud dapat memanggil URL webhook ini.
                    </p>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Kontak Layanan & Operasional -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-unpam-gold text-slate-950 flex items-center justify-center font-bold text-sm">
                        CS
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Kontak Layanan Mahasiswa</h2>
                        <p class="text-xs text-slate-500">Nomor WhatsApp admin CS dan nomor kontak darurat.</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="max-w-md">
                    <label for="admin_contact_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor WhatsApp CS Admin
                    </label>
                    <input type="text"
                           id="admin_contact_phone"
                           name="admin_contact_phone"
                           value="{{ old('admin_contact_phone', $adminPhone) }}"
                           placeholder="62812xxxxxxxx"
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-unpam-navy focus:border-unpam-navy">
                    <p class="text-[11px] text-slate-500 mt-1">Gunakan format angka internasional (contoh: 6281234567890).</p>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.dashboard') }}"
               class="min-h-[44px] px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-sm inline-flex items-center">
                Batal
            </a>
            <button type="submit"
                    class="min-h-[44px] px-6 py-2.5 rounded-lg bg-unpam-navy hover:bg-slate-800 text-white font-bold text-sm shadow inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-unpam-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan Pengaturan
            </button>
        </div>
    </form>
</div>

<script>
    function updatePrimaryColor(val, updatePicker = true) {
        if (!val) return;
        if (updatePicker) {
            const picker = document.getElementById('primary_picker');
            if (picker) picker.value = val;
        }
        const text = document.getElementById('theme_primary_color');
        if (text && text.value !== val) text.value = val;

        const header = document.getElementById('themePreviewHeader');
        if (header) header.style.backgroundColor = val;
    }

    function updateAccentColor(val, updatePicker = true) {
        if (!val) return;
        if (updatePicker) {
            const picker = document.getElementById('accent_picker');
            if (picker) picker.value = val;
        }
        const text = document.getElementById('theme_accent_color');
        if (text && text.value !== val) text.value = val;

        const header = document.getElementById('themePreviewHeader');
        if (header) header.style.borderBottomColor = val;

        const badge = document.getElementById('themePreviewBadge');
        if (badge) badge.style.backgroundColor = val;
    }

    function applyColorPreset(primary, accent) {
        updatePrimaryColor(primary);
        updateAccentColor(accent);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('toggleTokenVisibility');
        const tokenInput = document.getElementById('fonnte_token');
        if (toggleBtn && tokenInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = tokenInput.getAttribute('type') === 'password';
                tokenInput.setAttribute('type', isPassword ? 'text' : 'password');
            });
        }
    });
</script>
@endsection
