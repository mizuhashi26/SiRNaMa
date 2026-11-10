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
