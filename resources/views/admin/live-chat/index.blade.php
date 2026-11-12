@extends('layouts.admin')

@section('title', 'Meja Layanan (Live Chat Customer Service) - Admin UNPAM')

@section('content')
<div class="space-y-4">
    <!-- Header Meja Layanan CS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-300 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                <span>Pusat Layanan Mahasiswa</span>
                <span>&bull;</span>
                <span class="text-unpam-navy dark:text-amber-400 font-black">Konsol Customer Service</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Meja Layanan Live Chat Terpadu</span>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                    Kanal WhatsApp &amp; Website
                </span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            @php $waitingCount = $conversations->where('status', 'WAITING_ADMIN')->count(); @endphp
            @if($waitingCount > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-950 dark:text-amber-200 border border-amber-300 dark:border-amber-700 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                    {{ $waitingCount }} Mahasiswa Menunggu Respons
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Semua Antrean Tertangani
                </span>
            @endif
        </div>
    </div>

    <!-- Layout Dua Panel Workstation CS -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-300 dark:border-slate-700 shadow-xs overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[600px]">
        
        <!-- PANEL KIRI (4 Kolom / 33%): Kotak Masuk Tiket & Daftar Kontak -->
        <aside aria-label="Kotak Masuk Percakapan Mahasiswa" class="lg:col-span-4 border-r border-slate-200 dark:border-slate-700 flex flex-col bg-slate-50/70 dark:bg-slate-900/60">
            <!-- Header Inbox & Filter CS -->
            <div class="p-3.5 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Kotak Masuk Obrolan</span>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-600">
                        {{ $conversations->count() }} Percakapan
                    </span>
                </div>

                <!-- Search Input Mahasiswa -->
                <div class="relative">
                    <input type="text"
                           id="csContactSearch"
                           placeholder="Cari nama atau nomor WA..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-unpam-navy">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Tabs Filter Status CS -->
                <div class="flex items-center gap-1 text-xs font-bold overflow-x-auto pb-0.5">
                    <a href="{{ route('admin.live-chat.index') }}"
                       class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap {{ empty($statusFilter) && empty($channelFilter) ? 'bg-unpam-navy text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        Semua
                    </a>
                    <a href="{{ route('admin.live-chat.index', ['status' => 'WAITING_ADMIN']) }}"
                       class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap flex items-center gap-1 {{ $statusFilter === 'WAITING_ADMIN' ? 'bg-amber-400 text-slate-950 font-black' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 border border-amber-200 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900' }}">
                        <span>Menunggu</span>
                        @if($waitingCount > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-red-600 text-white">{{ $waitingCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.live-chat.index', ['status' => 'BOT_ACTIVE']) }}"
                       class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap {{ $statusFilter === 'BOT_ACTIVE' ? 'bg-slate-800 dark:bg-slate-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        Bot Aktif
                    </a>
                    <a href="{{ route('admin.live-chat.index', ['channel' => 'whatsapp']) }}"
                       class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap {{ $channelFilter === 'whatsapp' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        WhatsApp
                    </a>
                    <a href="{{ route('admin.live-chat.index', ['channel' => 'web']) }}"
                       class="px-2.5 py-1 rounded-md transition-colors whitespace-nowrap {{ $channelFilter === 'web' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                        Website
                    </a>
                </div>
            </div>

            <!-- List Kontak Chat CS -->
            <div id="csContactList" class="flex-1 overflow-y-auto max-h-[560px] divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($conversations as $item)
                    @php
                        $isSelected = $activeConversation && $activeConversation->id === $item->id;
                        $lastMsg = $item->messages->last();
                        $statusBadge = match($item->status) {
                            'WAITING_ADMIN' => 'bg-amber-100 dark:bg-amber-950/80 text-amber-950 dark:text-amber-200 border-amber-300 dark:border-amber-700 font-black',
                            'ADMIN_ACTIVE' => 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-950 dark:text-emerald-200 border-emerald-300 dark:border-emerald-700 font-bold',
                            'CLOSED' => 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-600',
                            default => 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
                        };
                    @endphp
                    <a href="{{ route('admin.live-chat.index', array_filter(['conversation_id' => $item->id, 'status' => $statusFilter, 'channel' => $channelFilter])) }}"
                       data-search="{{ strtolower(($item->user_name ?? '') . ' ' . ($item->contact_number ?? '') . ' ' . $item->external_user_id) }}"
                       class="cs-contact-item block p-3.5 transition-colors relative {{ $isSelected ? 'bg-white dark:bg-slate-800 border-l-4 border-unpam-navy dark:border-amber-400 shadow-xs' : 'hover:bg-white/70 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-start gap-3">
                            <!-- Avatar CS dengan penanda kanal -->
                            <div class="relative shrink-0">
                                <div class="w-10 h-10 rounded-full {{ $item->channel === 'whatsapp' ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-900 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700' : 'bg-blue-100 dark:bg-blue-950/70 text-blue-900 dark:text-blue-200 border border-blue-300 dark:border-blue-700' }} flex items-center justify-center font-black text-xs">
                                    {{ strtoupper(substr($item->user_name ?: $item->external_user_id, 0, 2)) }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-bold text-white border-2 border-white dark:border-slate-800 {{ $item->channel === 'whatsapp' ? 'bg-emerald-600' : 'bg-blue-600' }}">
                                    @if($item->channel === 'whatsapp') WA @else WB @endif
                                </span>
                            </div>

                            <!-- Detail Mahasiswa -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <h2 class="text-sm font-bold text-slate-900 dark:text-white truncate flex items-center gap-1.5">
                                        <span class="truncate">{{ $item->user_name ?: $item->external_user_id }}</span>
                                        @if($item->channel === 'whatsapp' && !isset($registeredPhones[$item->contact_number]))
                                            <span class="text-[9px] font-bold px-1 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700 shrink-0">Baru</span>
                                        @endif
                                    </h2>
                                    <span class="text-[10px] text-slate-400 font-mono shrink-0">
                                        {{ $item->last_message_at?->format('H:i') ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-1 mb-1.5">
                                    <span class="inline-block text-[10px] uppercase px-2 py-0.5 rounded-full border {{ $statusBadge }}">
                                        {{ $item->status_label }}
                                    </span>
                                    @if($item->contact_number)
                                        <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 truncate">+{{ $item->contact_number }}</span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    @if($lastMsg)
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $lastMsg->sender_type === 'user' ? 'Mhs:' : 'CS:' }}</span>
                                        {{ $lastMsg->message }}
                                    @else
                                        <span class="italic text-slate-400">Belum ada percakapan</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 space-y-1">
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Tidak Ada Percakapan</p>
                        <p class="text-[11px] text-slate-400">Belum ada obrolan masuk pada filter ini.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- PANEL KANAN (8 Kolom / 67%): Konsol Meja Layanan Customer Service -->
        <section aria-label="Konsol Percakapan CS Aktif" class="lg:col-span-8 flex flex-col bg-white dark:bg-slate-800">
            @if(! $activeConversation)
                <div class="flex-1 flex flex-col items-center justify-center p-16 text-center text-slate-400 space-y-3">
                    <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-200">Pilih Mahasiswa dari Panel Kiri</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mt-1">
                            Klik salah satu kontak mahasiswa untuk memulai pendampingan layanan, membaca riwayat, atau mengirim balasan.
                        </p>
                    </div>
                </div>
            @else
                <!-- Header Percakapan CS -->
                <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-unpam-navy dark:bg-slate-700 text-white font-black text-sm flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr($activeConversation->user_name ?: $activeConversation->external_user_id, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-black text-slate-900 dark:text-white leading-tight">
                                    {{ $activeConversation->user_name ?: $activeConversation->external_user_id }}
                                </h2>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $activeConversation->channel === 'whatsapp' ? 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-900 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700' : 'bg-blue-100 dark:bg-blue-950/70 text-blue-900 dark:text-blue-200 border border-blue-300 dark:border-blue-700' }}">
                                    {{ $activeConversation->channel === 'whatsapp' ? 'WhatsApp' : 'Website' }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-2">
                                @if($activeConversation->contact_number)
                                    <span class="font-mono">No: +{{ $activeConversation->contact_number }}</span>
                                    <span>&bull;</span>
                                @endif
                                <span>Status: <strong id="livechat-active-status" class="text-slate-800 dark:text-slate-200">{{ $activeConversation->status_label }}</strong></span>
                                @if($activeConversation->channel === 'whatsapp')
                                    <span>&bull;</span>
                                    @if(isset($registeredPhones[$activeConversation->contact_number]))
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">
                                            Mahasiswa Terdaftar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 border border-amber-200 dark:border-amber-700">
                                            Nomor Belum Terdaftar
                                        </span>
                                        @if(auth()->user()->isSuperAdmin())
                                            <a href="{{ route('admin.students.create', ['whatsapp_number' => $activeConversation->contact_number, 'name' => $activeConversation->user_name]) }}"
                                               target="_blank"
                                               class="text-[11px] font-bold text-unpam-navy dark:text-amber-400 hover:underline">
                                                + Daftarkan Mahasiswa
                                            </a>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Kendali Sesi CS -->
                    <div class="flex items-center gap-2 shrink-0">
                        @if($activeConversation->status !== 'ADMIN_ACTIVE')
                            <form action="{{ route('admin.live-chat.take-over', $activeConversation) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="min-h-[40px] px-4 py-2 rounded-lg bg-unpam-gold hover:bg-amber-400 text-slate-950 font-black text-xs shadow-xs transition-colors border border-amber-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>Ambil Alih Obrolan</span>
                                </button>
                            </form>
                        @endif

                        @if($activeConversation->status !== 'CLOSED')
                            <form action="{{ route('admin.live-chat.close', $activeConversation) }}"
                                  method="POST"
                                  onsubmit="return confirm('Selesaikan pendampingan dan kembalikan obrolan ke Chatbot?');">
                                @csrf
                                <button type="submit"
                                        class="min-h-[40px] px-3.5 py-2 rounded-lg bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white font-bold text-xs transition-colors shadow-xs flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Selesaikan (Kembali ke Bot)</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Area Riwayat Pesan CS (Bisa Discroll Alami) -->
                <div id="admin-chat-messages"
                     data-poll-url="{{ route('admin.live-chat.poll', $activeConversation) }}"
                     class="flex-1 p-5 space-y-4 bg-slate-50/60 dark:bg-slate-950/40 overflow-y-auto max-h-[460px]">
                    @forelse($activeConversation->messages as $msg)
                        @php
                            $isUser = $msg->sender_type === 'user';
                            $isAdmin = $msg->sender_type === 'admin';
                            $isSystem = $msg->sender_type === 'system';
                        @endphp

                        @if($isSystem)
                            <div class="flex justify-center my-1.5">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 border border-amber-200/80 dark:border-amber-700/80 text-center max-w-md shadow-xs">
                                    {{ $msg->message }}
                                </span>
                            </div>
                        @elseif($isAdmin)
                            <div class="flex justify-end pl-12">
                                <div class="max-w-md sm:max-w-lg bg-unpam-navy dark:bg-slate-750 text-white rounded-2xl rounded-tr-xs px-4 py-3 shadow-xs space-y-1">
                                    <div class="flex items-center justify-between gap-3 text-[10px] text-slate-300">
                                        <span class="font-bold text-unpam-gold">{{ $msg->sender_name ?: 'CS Layanan' }} (ADMIN)</span>
                                        <span class="font-mono">{{ $msg->created_at?->format('H:i') }}</span>
                                    </div>
                                    <div class="text-xs sm:text-sm whitespace-pre-line leading-relaxed break-words">
                                        {{ $msg->message }}
                                    </div>
                                    @if($msg->delivery_status === 'sent_gateway')
                                        <div class="text-[10px] text-emerald-300 text-right font-medium pt-0.5">
                                            &check;&check; Terkirim ke WhatsApp
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start pr-12">
                                <div class="max-w-md sm:max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white rounded-2xl rounded-tl-xs px-4 py-3 shadow-xs space-y-1">
                                    <div class="flex items-center justify-between gap-3 text-[10px] text-slate-400">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $msg->sender_name ?: 'Mahasiswa' }} (MAHASISWA)</span>
                                        <span class="font-mono">{{ $msg->created_at?->format('H:i') }}</span>
                                    </div>
                                    <div class="text-xs sm:text-sm whitespace-pre-line leading-relaxed break-words text-slate-800 dark:text-slate-200">
                                        {{ $msg->message }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center text-slate-400 py-16 text-sm">
                            Belum ada riwayat pesan pada sesi ini.
                        </div>
                    @endforelse
                </div>

                <!-- CS Composer Bar (Bawah) -->
                <div class="p-3.5 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 space-y-2.5">
                    <!-- Template Balasan Cepat CS (Canned Responses) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto text-[11px] pb-1">
                        <span class="font-bold text-slate-500 dark:text-slate-400 uppercase shrink-0 text-[10px]">Balasan Cepat:</span>
                        <button type="button"
                                onclick="insertQuickReply('Halo, selamat datang di Layanan Mahasiswa UNPAM. Ada yang bisa kami bantu?')"
                                class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold whitespace-nowrap transition-colors">
                            Sapaan Pembuka
                        </button>
                        <button type="button"
                                onclick="insertQuickReply('Baik, mohon tunggu sebentar ya. Kami sedang melakukan verifikasi data pada sistem.')"
                                class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold whitespace-nowrap transition-colors">
                            Mohon Tunggu
                        </button>
                        <button type="button"
                                onclick="insertQuickReply('Sama-sama, senang bisa membantu. Jika ada hal lain yang perlu ditanyakan, silakan hubungi kami kembali.')"
                                class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold whitespace-nowrap transition-colors">
                            Penutup Layanan
                        </button>
                    </div>

                    <form action="{{ route('admin.live-chat.reply', $activeConversation) }}" method="POST" class="flex items-end gap-2.5">
                        @csrf
                        <div class="flex-1 relative">
                            <textarea id="csReplyInput"
                                      name="message"
                                      rows="2"
                                      required
                                      placeholder="Ketik balasan CS ke mahasiswa... (Tekan Kirim untuk membalas)"
                                      class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-unpam-navy resize-none"></textarea>
                        </div>
                        <button type="submit"
                                class="min-h-[46px] px-5 py-2.5 rounded-xl bg-unpam-navy dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors inline-flex items-center gap-2 shrink-0">
                            <span>Kirim Balasan</span>
                            <svg class="w-4 h-4 text-unpam-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </form>
                </div>
            @endif
        </section>
    </div>
</div>

<script>
    function insertQuickReply(text) {
        const input = document.getElementById('csReplyInput');
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    // Filter pencarian kontak instan di panel kiri
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('csContactSearch');
        const contactItems = document.querySelectorAll('.cs-contact-item');

        if (searchInput && contactItems.length > 0) {
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                contactItems.forEach(item => {
                    const searchData = item.getAttribute('data-search') || '';
                    if (searchData.includes(query)) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            });
        }

        @if($activeConversation)
            const container = document.getElementById('admin-chat-messages');
            const statusBadge = document.getElementById('livechat-active-status');
            if (container) {
                container.scrollTop = container.scrollHeight;
                const pollUrl = container.getAttribute('data-poll-url');
                let currentCount = {{ $activeConversation->messages->count() }};

                setInterval(async function () {
                    try {
                        const res = await fetch(pollUrl, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (statusBadge && data.status_label) {
                            statusBadge.textContent = data.status_label;
                        }
                        if (Array.isArray(data.messages) && data.messages.length !== currentCount) {
                            window.location.reload();
                        }
                    } catch (e) {
                        // Silent polling
                    }
                }, 3500);
            }
        @endif
    });
</script>
@endsection
