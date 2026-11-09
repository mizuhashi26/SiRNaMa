@extends('layouts.admin')

@section('title', 'Ringkasan Tugas Admin - Layanan Mahasiswa UNPAM')

@section('content')
    <!-- Header Halaman & Tindakan Cepat -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-300 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                Pusat Kendali Admin
            </h1>
            <p class="text-sm text-slate-700 mt-1">
                Kelola antrean percakapan mahasiswa, publikasi informasi satu pintu, dan pengiriman pengingat pembayaran.
            </p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('admin.informations.create') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark">
                + Tambah Informasi Kampus
            </a>
            <a href="{{ route('admin.reminders.create') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded bg-unpam-gold text-slate-950 text-sm font-bold hover:bg-unpam-goldHover">
                + Buat Reminder Pembayaran
            </a>
        </div>
    </div>

    <!-- Status Integrasi Gateway Fonnte (Jujur & Transparan sesuai AGENTS.md) -->
    <section aria-label="Status Konfigurasi Gateway WhatsApp" class="bg-white border border-slate-300 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full {{ $fonnteConfigured ? 'bg-emerald-600' : 'bg-amber-500' }}" aria-hidden="true"></span>
                <h2 class="text-sm font-bold text-slate-900">
                    Status WhatsApp Gateway (Fonnte):
                    @if($fonnteConfigured)
                        <span class="text-emerald-800">Token Terkonfigurasi</span>
                    @else
                        <span class="text-amber-900">Token Belum Diisi</span>
                    @endif
                </h2>
            </div>
            <p class="text-xs text-slate-700">
                @if($fonnteConfigured)
                    Pengiriman pengingat dan balasan WhatsApp dikirim melalui gateway Fonnte.
                @else
                    Masukkan token Fonnte pada file konfigurasi untuk menghubungkan layanan WhatsApp.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0 text-xs font-semibold">
            <span class="px-3 py-1.5 rounded bg-slate-100 border border-slate-300 text-slate-900">
                Informasi Terbit: <strong>{{ $counts['published_info'] }}</strong>
            </span>
            <span class="px-3 py-1.5 rounded bg-slate-100 border border-slate-300 text-slate-900">
                Mahasiswa Aktif: <strong>{{ $counts['active_students'] }}</strong>
            </span>
        </div>
    </section>

    <!-- Prioritas Utama 1: Antrean Live Chat Mahasiswa (Menunggu Admin & Admin Aktif) -->
    <section aria-labelledby="heading-livechat-queue" class="bg-white border border-slate-300 rounded-lg p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-4">
            <div>
                <h2 id="heading-livechat-queue" class="text-lg font-bold text-slate-900">
                    Antrean Percakapan Mahasiswa ({{ $counts['waiting_admin'] }} Menunggu Admin, {{ $counts['admin_active'] }} Admin Aktif)
                </h2>
                <p class="text-xs sm:text-sm text-slate-700">
                    Percakapan dari Website dan WhatsApp yang membutuhkan penanganan Admin.
                </p>
            </div>
            <a href="{{ route('admin.live-chat.index') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded bg-unpam-navy text-white text-xs sm:text-sm font-semibold hover:bg-unpam-navyDark self-start sm:self-auto">
                Buka Ruang Live Chat
            </a>
        </div>

        @if($waitingConversations->isEmpty())
            <div class="bg-slate-50 border border-slate-200 rounded p-6 text-center space-y-2">
                <p class="text-sm font-bold text-slate-900">Tidak Ada Antrean Percakapan yang Menunggu Admin</p>
                <p class="text-xs text-slate-700">
                    Saat ini seluruh interaksi mahasiswa sedang ditangani otomatis oleh Chatbot berbasis menu. Jika mahasiswa memilih menu <strong>3. Hubungi Admin</strong>, percakapan akan langsung muncul di sini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-300 bg-slate-100 text-slate-900 text-xs uppercase">
                            <th class="py-3 px-3 font-bold">Mahasiswa / Pengunjung</th>
                            <th class="py-3 px-3 font-bold">Kanal</th>
                            <th class="py-3 px-3 font-bold">Status</th>
                            <th class="py-3 px-3 font-bold">Waktu Aktivitas</th>
                            <th class="py-3 px-3 font-bold text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($waitingConversations as $conv)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-3 font-semibold text-slate-900">
                                    {{ $conv->user_name ?: $conv->external_user_id }}
                                    @if($conv->contact_number)
                                        <span class="block text-xs font-normal text-slate-600">+{{ $conv->contact_number }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-block text-xs font-bold px-2.5 py-1 rounded {{ $conv->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-950 border border-emerald-300' : 'bg-blue-100 text-blue-950 border border-blue-300' }}">
                                        {{ $conv->channel === 'whatsapp' ? 'WhatsApp' : 'Website' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-block text-xs font-bold px-2.5 py-1 rounded {{ $conv->status === 'WAITING_ADMIN' ? 'bg-amber-200 text-slate-950' : 'bg-emerald-200 text-slate-950' }}">
                                        {{ $conv->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-xs text-slate-700">
                                    {{ $conv->last_message_at?->diffForHumans() ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('admin.live-chat.index', ['conversation_id' => $conv->id]) }}"
                                       class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded bg-unpam-navy text-white text-xs font-bold hover:bg-unpam-navyDark">
                                        Tangani Chat
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <!-- Dua Kolom Pekerjaan Admin: Reminder Pembayaran & Informasi Kampus -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Reminder Pembayaran Umum -->
        <section aria-labelledby="heading-reminders-box" class="bg-white border border-slate-300 rounded-lg p-5 space-y-4">
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <div>
                    <h2 id="heading-reminders-box" class="text-base font-bold text-slate-900">
                        Reminder Pembayaran Umum Terbaru
                    </h2>
                    <p class="text-xs text-slate-700">Jadwal pengingat tagihan umum ke mahasiswa dummy.</p>
                </div>
                <a href="{{ route('admin.reminders.index') }}" class="min-h-[40px] inline-flex items-center text-xs font-bold text-unpam-navy hover:underline">
                    Kelola Semua
                </a>
            </div>

            @if($upcomingReminders->isEmpty())
                <div class="bg-slate-50 border border-slate-200 rounded p-4 text-xs text-slate-700 space-y-2">
                    <p class="font-semibold text-slate-900">Belum ada jadwal reminder pembayaran.</p>
                    <a href="{{ route('admin.reminders.create') }}" class="inline-flex items-center min-h-[40px] px-3 py-1.5 rounded bg-unpam-navy text-white font-bold">
                        Buat Reminder Pertama
                    </a>
                </div>
            @else
                <ul class="divide-y divide-slate-200 text-sm">
                    @foreach($upcomingReminders as $rem)
                        <li class="py-3 flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <a href="{{ route('admin.reminders.show', $rem) }}" class="font-bold text-slate-900 hover:underline">
                                    {{ $rem->title }}
                                </a>
                                <p class="text-xs text-slate-600">
                                    Jadwal: {{ $rem->scheduled_at?->format('d M Y H:i') ?? 'Manual' }} |
                                    Status: <strong>{{ strtoupper($rem->status) }}</strong>
                                </p>
                            </div>
                            <a href="{{ route('admin.reminders.show', $rem) }}"
                               class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded border border-slate-300 bg-slate-100 text-xs font-semibold text-slate-900 hover:bg-slate-200 shrink-0">
                                Detail / Kirim
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <!-- Informasi Kampus Terbaru -->
        <section aria-labelledby="heading-info-box" class="bg-white border border-slate-300 rounded-lg p-5 space-y-4">
            <div class="flex items-center justify-between gap-2 border-b border-slate-200 pb-3">
                <div>
                    <h2 id="heading-info-box" class="text-base font-bold text-slate-900">
                        Sumber Informasi Kampus & Chatbot
                    </h2>
                    <p class="text-xs text-slate-700">Data yang dipublikasikan otomatis tampil di Web & WhatsApp.</p>
                </div>
                <a href="{{ route('admin.informations.index') }}" class="min-h-[40px] inline-flex items-center text-xs font-bold text-unpam-navy hover:underline">
                    Kelola Semua
                </a>
            </div>

            @if($recentInformations->isEmpty())
                <div class="bg-slate-50 border border-slate-200 rounded p-4 text-xs text-slate-700 space-y-2">
                    <p class="font-semibold text-slate-900">Belum ada informasi kampus yang dibuat.</p>
                    <a href="{{ route('admin.informations.create') }}" class="inline-flex items-center min-h-[40px] px-3 py-1.5 rounded bg-unpam-navy text-white font-bold">
                        Tambah Informasi
                    </a>
                </div>
            @else
                <ul class="divide-y divide-slate-200 text-sm">
                    @foreach($recentInformations as $info)
                        <li class="py-3 flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded {{ $info->is_published ? 'bg-emerald-100 text-emerald-950' : 'bg-slate-200 text-slate-800' }}">
                                    {{ $info->is_published ? 'Terbit' : 'Draft' }} - {{ $info->category_label }}
                                </span>
                                <p class="font-bold text-slate-900">{{ $info->title }}</p>
                            </div>
                            <a href="{{ route('admin.informations.edit', $info) }}"
                               class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded border border-slate-300 bg-slate-100 text-xs font-semibold text-slate-900 hover:bg-slate-200 shrink-0">
                                Ubah
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection

