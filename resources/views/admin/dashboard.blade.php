@extends('layouts.admin')

@section('title', 'Pusat Kendali Admin - CRM Layanan Mahasiswa UNPAM')

@section('content')
    <div class="space-y-6">
        <!-- Header Halaman & Tombol Aksi Cepat -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Pusat Kendali Layanan
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Kelola percakapan mahasiswa, publikasi informasi satu pintu, dan pengiriman reminder WhatsApp.
                </p>
            </div>
            <!-- Panel Tombol Aksi Cepat -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.informations.create') }}"
                    class="min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-unpam-navy text-white text-xs sm:text-sm font-bold hover:bg-slate-800 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Informasi
                </a>
                <a href="{{ route('admin.reminders.create') }}"
                    class="min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-unpam-gold text-slate-950 text-xs sm:text-sm font-bold hover:bg-amber-400 transition-all shadow-sm border border-amber-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Buat Reminder
                </a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.settings.index') }}"
                        class="min-h-[44px] inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold transition-all shadow-xs">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Pengaturan
                    </a>
                @endif
            </div>
        </div>

        <!-- Bar Status Ringkas & Gateway Fonnte -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Kartu 1: Live Chat & Interaksi -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Live Chat &amp; Layanan</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span
                            class="text-2xl font-extrabold {{ $counts['waiting_admin'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">
                            {{ $counts['total_active'] }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Sesi Aktif</span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1.5 text-[11px]">
                        @if($counts['waiting_admin'] > 0)
                            <span class="font-bold text-amber-800 dark:text-amber-200 bg-amber-100 dark:bg-amber-900/60 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-700">
                                {{ $counts['waiting_admin'] }} Butuh Admin
                            </span>
                        @endif
                        <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $counts['bot_active'] }} Dilayani Bot</span>
                    </div>
                </div>
                <div
                    class="w-10 h-10 rounded-lg {{ $counts['waiting_admin'] > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 animate-pulse' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>

            <!-- Kartu 2: Informasi Kampus Terbit -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Informasi Terbit</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $counts['published_info'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Artikel Aktif</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-unpam-navy dark:bg-slate-700 dark:text-amber-300 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </div>
            </div>

            <!-- Kartu 3: Mahasiswa Dummy Terdaftar -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mahasiswa Terdaftar</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $counts['active_students'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Penerima WA</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>

            <!-- Kartu 4: Gateway WhatsApp Fonnte -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">WhatsApp Gateway</p>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <span
                            class="w-2.5 h-2.5 rounded-full {{ $fonnteConfigured ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span class="text-xs font-bold {{ $fonnteConfigured ? 'text-emerald-800 dark:text-emerald-400' : 'text-amber-800 dark:text-amber-300' }}">
                            {{ $fonnteConfigured ? 'Terkoneksi' : 'Token Belum Diisi' }}
                        </span>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.settings.index') }}"
                            class="text-[11px] font-semibold text-unpam-navy dark:text-amber-400 hover:underline mt-1 block">
                            Konfigurasi API &rarr;
                        </a>
                    @endif
                </div>
                <div
                    class="w-10 h-10 rounded-lg {{ $fonnteConfigured ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Prioritas 1: Antrean & Percakapan Live Chat -->
        <section aria-labelledby="heading-livechat-queue"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h2 id="heading-livechat-queue" class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Percakapan Mahasiswa &amp; Live Chat</span>
                        @if($counts['waiting_admin'] > 0)
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-950 border border-amber-300 dark:bg-amber-900/60 dark:text-amber-200 dark:border-amber-700 animate-pulse">
                                {{ $counts['waiting_admin'] }} Menunggu Admin
                            </span>
                        @endif
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                        Seluruh interaksi mahasiswa dan tamu dari Website &amp; WhatsApp, baik yang sedang dilayani Bot maupun yang memerlukan respons Admin.
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <a href="{{ route('admin.live-chat.index') }}"
                        class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-lg bg-unpam-navy text-white text-xs sm:text-sm font-bold hover:bg-slate-800 transition-colors shadow-sm">
                        Buka Live Chat Terpusat
                    </a>
                </div>
            </div>

            <!-- Tab Filter Percakapan -->
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-2 text-xs font-bold overflow-x-auto">
                <a href="{{ route('admin.dashboard', ['tab' => 'all']) }}"
                    class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap {{ ($currentTab ?? 'all') === 'all' ? 'bg-unpam-navy text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                    Semua Aktif ({{ $counts['total_active'] }})
                </a>
                <a href="{{ route('admin.dashboard', ['tab' => 'waiting']) }}"
                    class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 {{ ($currentTab ?? '') === 'waiting' ? 'bg-amber-400 text-slate-950 font-black' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-800 hover:bg-amber-100' }}">
                    <span>Menunggu Admin</span>
                    @if($counts['waiting_admin'] > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-red-600 text-white">{{ $counts['waiting_admin'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.dashboard', ['tab' => 'bot']) }}"
                    class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap {{ ($currentTab ?? '') === 'bot' ? 'bg-slate-800 dark:bg-slate-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                    Dilayani Bot ({{ $counts['bot_active'] }})
                </a>
                <a href="{{ route('admin.dashboard', ['tab' => 'admin']) }}"
                    class="px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap {{ ($currentTab ?? '') === 'admin' ? 'bg-emerald-700 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                    Admin Aktif ({{ $counts['admin_active'] }})
                </a>
            </div>

            @if($conversations->isEmpty())
                <div class="bg-slate-50 dark:bg-slate-850 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-lg p-6 text-center space-y-2">
                    <div
                        class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">Tidak Ada Percakapan Pada Filter Ini</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 max-w-md mx-auto">
                        Belum ada interaksi chat yang sesuai dengan tab yang dipilih. Chatbot WhatsApp &amp; Website tetap siaga merespons pengguna 24/7.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 dark:bg-slate-900/70 text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider">
                                <th class="py-3 px-4 font-bold">Mahasiswa / Kontak</th>
                                <th class="py-3 px-4 font-bold">Kanal</th>
                                <th class="py-3 px-4 font-bold">Status Layanan</th>
                                <th class="py-3 px-4 font-bold">Pesan Terakhir</th>
                                <th class="py-3 px-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($conversations as $conv)
                                @php
                                    $isRegistered = $conv->channel === 'whatsapp' && isset($registeredPhones[$conv->contact_number]);
                                    $lastMsg = $conv->messages->last();
                                @endphp
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-750 transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="w-9 h-9 rounded-full {{ $conv->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-blue-100 text-blue-900 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-300 dark:border-blue-700' }} flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                                {{ substr($conv->user_name ?: $conv->external_user_id, 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="block font-bold">{{ $conv->user_name ?: $conv->external_user_id }}</span>
                                                    @if($conv->channel === 'whatsapp')
                                                        @if($isRegistered)
                                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">
                                                                Mhs Terdaftar
                                                            </span>
                                                        @else
                                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-700">
                                                                Kontak Baru
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 text-xs font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                                                    @if($conv->contact_number)
                                                        <span>+{{ $conv->contact_number }}</span>
                                                    @endif
                                                    @if(auth()->user()->isSuperAdmin() && $conv->channel === 'whatsapp' && ! $isRegistered)
                                                        <a href="{{ route('admin.students.create', ['whatsapp_number' => $conv->contact_number, 'name' => $conv->user_name]) }}"
                                                           class="text-[11px] font-bold text-unpam-navy dark:text-amber-400 hover:underline">
                                                            + Daftarkan Mhs
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span
                                            class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full {{ $conv->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-blue-100 text-blue-900 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-300 dark:border-blue-700' }}">
                                            @if($conv->channel === 'whatsapp')
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> WhatsApp
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Website
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($conv->status === 'WAITING_ADMIN')
                                            <span class="inline-block text-xs font-black px-2.5 py-1 rounded-full bg-amber-100 text-amber-950 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700 animate-pulse">
                                                Menunggu Admin
                                            </span>
                                        @elseif($conv->status === 'ADMIN_ACTIVE')
                                            <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-900 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                                Admin Aktif ({{ $conv->assignedAdmin?->name ?? 'Admin' }})
                                            </span>
                                        @elseif($conv->status === 'BOT_ACTIVE')
                                            <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                                Bot Aktif
                                            </span>
                                        @else
                                            <span class="inline-block text-xs font-medium px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                                {{ $conv->status_label }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-300 max-w-xs">
                                        @if($lastMsg)
                                            <div class="truncate">
                                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $lastMsg->sender_type === 'user' ? 'Mhs:' : ($lastMsg->sender_type === 'admin' ? 'CS:' : 'Bot:') }}</span>
                                                {{ $lastMsg->message }}
                                            </div>
                                        @endif
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $conv->last_message_at?->diffForHumans() ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('admin.live-chat.index', ['conversation_id' => $conv->id]) }}"
                                            class="min-h-[40px] inline-flex items-center px-3.5 py-1.5 rounded-lg {{ $conv->status === 'WAITING_ADMIN' ? 'bg-amber-500 hover:bg-amber-600 text-slate-950 font-black' : 'bg-unpam-navy hover:bg-slate-800 text-white font-bold' }} text-xs transition-colors shadow-sm">
                                            @if($conv->status === 'WAITING_ADMIN')
                                                Ambil Alih &rarr;
                                            @elseif($conv->status === 'BOT_ACTIVE')
                                                Pantau Chat &rarr;
                                            @else
                                                Buka Chat &rarr;
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- Dua Kolom Pekerjaan: Reminder Pembayaran & Informasi Kampus -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Kolom Kiri: Reminder Pembayaran Umum -->
            <section aria-labelledby="heading-reminders-box"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h2 id="heading-reminders-box" class="text-base font-bold text-slate-900 dark:text-white">
                            Reminder Pembayaran Umum
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Jadwal pengingat tagihan massal via WhatsApp.</p>
                    </div>
                    <a href="{{ route('admin.reminders.index') }}"
                        class="text-xs font-bold text-unpam-navy dark:text-amber-400 hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                @if($upcomingReminders->isEmpty())
                    <div
                        class="bg-slate-50 dark:bg-slate-850 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-lg p-5 text-xs text-slate-600 dark:text-slate-400 text-center space-y-2">
                        <p class="font-semibold text-slate-800 dark:text-slate-200">Belum ada reminder yang dijadwalkan.</p>
                        <a href="{{ route('admin.reminders.create') }}"
                            class="inline-flex items-center min-h-[40px] px-3.5 py-1.5 rounded-lg bg-unpam-navy text-white font-bold text-xs">
                            + Buat Jadwal Baru
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                        @foreach($upcomingReminders as $rem)
                            <li class="py-3 flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <a href="{{ route('admin.reminders.show', $rem) }}"
                                        class="font-bold text-slate-900 dark:text-white hover:text-unpam-navy dark:hover:text-amber-400 hover:underline block leading-snug">
                                        {{ $rem->title }}
                                    </a>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <span>Jadwal: {{ $rem->scheduled_at?->format('d M Y H:i') ?? 'Manual' }}</span>
                                        <span>&bull;</span>
                                        <span
                                            class="font-bold uppercase text-[11px] px-1.5 py-0.5 rounded {{ $rem->status === 'sent' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                                            {{ $rem->status }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ route('admin.reminders.show', $rem) }}"
                                    class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-xs font-bold text-slate-800 dark:text-slate-200 transition-colors shrink-0">
                                    Detail / Kirim
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <!-- Kolom Kanan: Informasi Kampus & Chatbot Knowledge Base -->
            <section aria-labelledby="heading-info-box"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h2 id="heading-info-box" class="text-base font-bold text-slate-900 dark:text-white">
                            Sumber Informasi Kampus & Bot
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Data informasi terbit otomatis sinkron ke Web & Bot WhatsApp.</p>
                    </div>
                    <a href="{{ route('admin.informations.index') }}"
                        class="text-xs font-bold text-unpam-navy dark:text-amber-400 hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                @if($recentInformations->isEmpty())
                    <div
                        class="bg-slate-50 dark:bg-slate-850 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-lg p-5 text-xs text-slate-600 dark:text-slate-400 text-center space-y-2">
                        <p class="font-semibold text-slate-800 dark:text-slate-200">Belum ada informasi kampus yang dibuat.</p>
                        <a href="{{ route('admin.informations.create') }}"
                            class="inline-flex items-center min-h-[40px] px-3.5 py-1.5 rounded-lg bg-unpam-navy text-white font-bold text-xs">
                            + Tambah Informasi Baru
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                        @foreach($recentInformations as $info)
                            <li class="py-3 flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="inline-block text-[10px] font-extrabold uppercase px-2 py-0.5 rounded {{ $info->is_published ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                                            {{ $info->is_published ? 'Terbit' : 'Draft' }}
                                        </span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $info->category_label }}</span>
                                    </div>
                                    <p class="font-bold text-slate-900 dark:text-white leading-snug">{{ $info->title }}</p>
                                </div>
                                <a href="{{ route('admin.informations.edit', $info) }}"
                                    class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-xs font-bold text-slate-800 dark:text-slate-200 transition-colors shrink-0">
                                    Ubah
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection