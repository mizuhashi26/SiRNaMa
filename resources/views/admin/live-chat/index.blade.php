@extends('layouts.admin')

@section('title', 'Live Chat Terpusat - Dashboard Admin CRM UNPAM')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Live Chat Admin Terpusat (Website & WhatsApp)</h1>
            <p class="text-sm text-slate-700">
                Kelola percakapan mahasiswa dari kedua kanal dengan alur: <strong>Menunggu Admin</strong> &rarr; <strong>Ambil Alih (Admin Aktif)</strong> &rarr; <strong>Akhiri Percakapan (Selesai)</strong>.
            </p>
        </div>
    </div>

    <!-- Filter Status & Kanal -->
    <form action="{{ route('admin.live-chat.index') }}" method="GET" class="bg-white border border-slate-300 rounded-lg p-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="status" class="block text-xs font-bold text-slate-800 mb-1">Filter Status Percakapan</label>
            <select id="status" name="status" class="min-h-[44px] px-3 py-2 rounded border border-slate-400 text-sm text-slate-900 bg-white">
                <option value="">Semua Status</option>
                @foreach($statusLabels as $code => $label)
                    <option value="{{ $code }}" @selected($statusFilter === $code)>{{ $label }} ({{ $code }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="channel" class="block text-xs font-bold text-slate-800 mb-1">Filter Kanal</label>
            <select id="channel" name="channel" class="min-h-[44px] px-3 py-2 rounded border border-slate-400 text-sm text-slate-900 bg-white">
                <option value="">Semua Kanal (Website & WhatsApp)</option>
                <option value="web" @selected($channelFilter === 'web')>Website</option>
                <option value="whatsapp" @selected($channelFilter === 'whatsapp')>WhatsApp (Fonnte)</option>
            </select>
        </div>

        <button type="submit" class="min-h-[44px] px-4 py-2 rounded bg-unpam-navy text-white text-sm font-semibold hover:bg-unpam-navyDark">
            Terapkan Filter
        </button>

        @if($statusFilter !== '' || $channelFilter !== '')
            <a href="{{ route('admin.live-chat.index') }}" class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded border border-slate-300 text-sm font-medium text-slate-800 hover:bg-slate-100">
                Reset
            </a>
        @endif
    </form>

    <!-- Layout Dua Panel Live Chat -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Daftar Percakapan (Kiri: 4 Kolom) -->
        <aside aria-label="Daftar Sesi Percakapan" class="lg:col-span-4 bg-white border border-slate-300 rounded-lg overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-300 px-4 py-3 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Daftar Percakapan ({{ $conversations->count() }})</h2>
            </div>

            @if($conversations->isEmpty())
                <div class="p-6 text-center space-y-2">
                    <p class="text-sm font-bold text-slate-900">Belum Ada Percakapan</p>
                    <p class="text-xs text-slate-700">
                        Percakapan dari widget Website maupun webhook WhatsApp akan otomatis muncul di daftar ini.
                    </p>
                </div>
            @else
                <ul class="divide-y divide-slate-200 max-h-[540px] overflow-y-auto">
                    @foreach($conversations as $item)
                        @php
                            $isSelected = $activeConversation && $activeConversation->id === $item->id;
                            $statusBadge = match($item->status) {
                                'WAITING_ADMIN' => 'bg-amber-200 text-slate-950',
                                'ADMIN_ACTIVE' => 'bg-emerald-200 text-slate-950',
                                'CLOSED' => 'bg-slate-200 text-slate-800',
                                default => 'bg-blue-100 text-blue-950',
                            };
                        @endphp
                        <li>
                            <a href="{{ route('admin.live-chat.index', array_filter(['conversation_id' => $item->id, 'status' => $statusFilter, 'channel' => $channelFilter])) }}"
                               class="block p-4 transition-colors {{ $isSelected ? 'bg-amber-50 border-l-4 border-unpam-navy' : 'hover:bg-slate-50' }}">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded {{ $item->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-950 border border-emerald-300' : 'bg-slate-200 text-slate-900' }}">
                                        {{ $item->channel === 'whatsapp' ? 'WhatsApp' : 'Website' }}
                                    </span>
                                    <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded {{ $statusBadge }}">
                                        {{ $item->status_label }}
                                    </span>
                                </div>
                                <p class="font-bold text-sm text-slate-900 truncate">
                                    {{ $item->user_name ?: $item->external_user_id }}
                                </p>
                                @if($item->contact_number)
                                    <p class="text-xs font-mono text-slate-600">+{{ $item->contact_number }}</p>
                                @endif
                                <p class="text-[11px] text-slate-600 mt-1">
                                    Aktivitas: {{ $item->last_message_at?->diffForHumans() ?? '-' }}
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>

        <!-- Panel Ruang Chat Aktif (Kanan: 8 Kolom) -->
        <section aria-label="Ruang Percakapan Aktif" class="lg:col-span-8 bg-white border border-slate-300 rounded-lg overflow-hidden flex flex-col">
            @if(! $activeConversation)
                <div class="p-12 text-center space-y-2">
                    <h2 class="text-base font-bold text-slate-900">Pilih Percakapan di Panel Kiri</h2>
                    <p class="text-sm text-slate-700">
                        Pilih salah satu sesi percakapan mahasiswa untuk membaca riwayat pesan, mengambil alih percakapan, atau mengirim balasan.
                    </p>
                </div>
            @else
                <!-- Header Percakapan & Tombol Kontrol Status -->
                <div class="bg-slate-100 border-b border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-extrabold text-slate-900">
                                {{ $activeConversation->user_name ?: $activeConversation->external_user_id }}
                            </h2>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded {{ $activeConversation->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-950 border border-emerald-300' : 'bg-blue-100 text-blue-950 border border-blue-300' }}">
                                Kanal: {{ $activeConversation->channel === 'whatsapp' ? 'WhatsApp' : 'Website' }}
                            </span>
                            <span id="livechat-active-status" class="text-xs font-bold px-2.5 py-0.5 rounded bg-unpam-navy text-white">
                                {{ $activeConversation->status_label }} ({{ $activeConversation->status }})
                            </span>
                        </div>
                        <p class="text-xs text-slate-700">
                            ID Sesi: <code class="font-mono">{{ $activeConversation->external_user_id }}</code>
                            @if($activeConversation->assignedAdmin)
                                | Ditangani oleh: <strong>{{ $activeConversation->assignedAdmin->name }}</strong>
                            @endif
                        </p>
                    </div>

                    <!-- Tombol Aksi: Ambil Alih & Akhiri Percakapan -->
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if($activeConversation->status !== 'ADMIN_ACTIVE')
                            <form action="{{ route('admin.live-chat.take-over', $activeConversation) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="min-h-[44px] px-4 py-2 rounded bg-unpam-gold text-slate-950 font-bold text-xs sm:text-sm hover:bg-unpam-goldHover">
                                    Ambil Alih Percakapan
                                </button>
                            </form>
                        @endif

                        @if($activeConversation->status !== 'CLOSED')
                            <form action="{{ route('admin.live-chat.close', $activeConversation) }}"
                                  method="POST"
                                  onsubmit="return confirm('Akhiri percakapan ini? Status akan menjadi Selesai dan Chatbot akan kembali aktif untuk sesi berikutnya.');">
                                @csrf
                                <button type="submit"
                                        class="min-h-[44px] px-4 py-2 rounded bg-slate-800 text-white font-semibold text-xs sm:text-sm hover:bg-red-700">
                                    Akhiri Percakapan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($activeConversation->channel === 'whatsapp')
                    <div class="bg-amber-50 border-b border-amber-200 px-4 py-2 text-xs text-slate-800">
                        <strong>Catatan Sinkronisasi WhatsApp:</strong> Balasan yang Anda kirim dari form di bawah akan diteruskan ke nomor WhatsApp mahasiswa melalui API Fonnte
                        ({{ $fonnteConfigured ? 'Token Fonnte Aktif' : 'Mode Lokal - Token Fonnte belum diisi di .env' }}).
                        Hindari membalas langsung dari aplikasi HP WhatsApp karena Fonnte tidak menjamin sinkronisasi otomatis pesan keluar dari perangkat ke dashboard.
                    </div>
                @endif

                <!-- Riwayat Pesan -->
                <div id="admin-chat-messages"
                     data-poll-url="{{ route('admin.live-chat.poll', $activeConversation) }}"
                     class="p-4 space-y-3 bg-slate-50 min-h-[320px] max-h-[440px] overflow-y-auto text-sm">
                    @forelse($activeConversation->messages as $msg)
                        @php
                            $isUser = $msg->sender_type === 'user';
                            $isAdmin = $msg->sender_type === 'admin';
                            $isSystem = $msg->sender_type === 'system';
                        @endphp
                        <div class="{{ $isSystem ? 'bg-amber-50 border border-amber-300 text-slate-900 rounded p-3 text-xs' : ($isAdmin ? 'ml-8 bg-unpam-navy text-white rounded-lg p-3.5' : ($isUser ? 'mr-8 bg-white border-2 border-slate-300 text-slate-900 rounded-lg p-3.5' : 'mr-8 bg-slate-200 text-slate-900 rounded-lg p-3.5')) }}">
                            <div class="flex items-center justify-between gap-2 mb-1 text-xs {{ $isAdmin ? 'text-slate-200' : 'text-slate-600' }}">
                                <span class="font-bold">
                                    {{ $msg->sender_name ?: strtoupper($msg->sender_type) }}
                                    ({{ strtoupper($msg->sender_type) }})
                                </span>
                                <span>
                                    {{ $msg->created_at?->format('d M H:i') }}
                                    @if($msg->delivery_status === 'sent_gateway')
                                        | Terkirim Fonnte
                                    @elseif($msg->delivery_status === 'local_only')
                                        | Tersimpan Lokal (Tanpa Token WA)
                                    @elseif($msg->delivery_status === 'failed_gateway')
                                        | Gagal Gateway WA
                                    @endif
                                </span>
                            </div>
                            <div class="whitespace-pre-line leading-relaxed break-words">{{ $msg->message }}</div>
                        </div>
                    @empty
                        <div class="text-center text-slate-600 py-8 text-sm">
                            Belum ada pesan pada percakapan ini.
                        </div>
                    @endforelse
                </div>

                <!-- Form Balasan Admin -->
                <form action="{{ route('admin.live-chat.reply', $activeConversation) }}"
                      method="POST"
                      class="bg-white border-t border-slate-300 p-4 space-y-3">
                    @csrf
                    <div>
                        <label for="reply-message" class="block text-xs font-bold text-slate-900 mb-1">
                            Balas Pesan Mahasiswa
                            @if($activeConversation->status !== 'ADMIN_ACTIVE')
                                <span class="font-normal text-slate-600">(Mengirim balasan akan otomatis mengubah status menjadi Admin Aktif)</span>
                            @endif
                        </label>
                        <textarea id="reply-message"
                                  name="message"
                                  rows="3"
                                  required
                                  placeholder="Ketik balasan untuk mahasiswa di sini..."
                                  class="w-full px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900"></textarea>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-slate-600">
                            Kanal tujuan: <strong>{{ $activeConversation->channel === 'whatsapp' ? 'WhatsApp (+' . $activeConversation->contact_number . ')' : 'Widget Website Publik' }}</strong>
                        </span>
                        <button type="submit"
                                class="min-h-[44px] px-5 py-2 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark">
                            Kirim Balasan Admin
                        </button>
                    </div>
                </form>
            @endif
        </section>
    </div>

    @if($activeConversation)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('admin-chat-messages');
                const statusBadge = document.getElementById('livechat-active-status');
                if (!container) return;

                container.scrollTop = container.scrollHeight;
                const pollUrl = container.getAttribute('data-poll-url');
                let currentCount = {{ $activeConversation->messages->count() }};

                setInterval(async function () {
                    try {
                        const res = await fetch(pollUrl, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (statusBadge && data.status_label) {
                            statusBadge.textContent = data.status_label + ' (' + data.status + ')';
                        }
                        if (Array.isArray(data.messages) && data.messages.length !== currentCount) {
                            window.location.reload();
                        }
                    } catch (e) {
                        // Abaikan gangguan jaringan sementara saat polling
                    }
                }, 5000);
            });
        </script>
    @endif
@endsection

