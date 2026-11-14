@extends('layouts.admin')

@section('title', 'Detail & Log Pengingat Pembayaran - Admin UNPAM')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 dark:border-slate-700 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $reminder->title }}</h1>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    Detail pesan pengingat pembayaran dan riwayat pengiriman per mahasiswa.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('admin.reminders.send-now', $reminder) }}"
                      method="POST"
                      data-confirm="Kirim pengingat ini sekarang ke seluruh target mahasiswa?">
                    @csrf
                    <button type="submit"
                            class="min-h-[44px] px-4 py-2 rounded-lg bg-unpam-gold text-slate-950 text-sm font-bold hover:bg-unpam-goldHover shadow-xs transition-colors">
                        Kirim Manual Sekarang via Fonnte
                    </button>
                </form>
                <a href="{{ route('admin.reminders.index') }}"
                   class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 space-y-3 shadow-xs">
                <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-2">Isi Pesan Reminder</h2>
                <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-lg p-4 text-sm text-slate-900 dark:text-slate-200 whitespace-pre-line leading-relaxed">{{ $reminder->message }}</div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 space-y-3 text-sm shadow-xs">
                <h2 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-2">Status &amp; Ringkasan</h2>
                <dl class="space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-600 dark:text-slate-400 shrink-0">Status Terakhir:</dt>
                        <dd class="font-bold text-slate-900 dark:text-white text-right break-words">{{ strtoupper($reminder->status) }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-600 dark:text-slate-400 shrink-0">Target Penerima:</dt>
                        <dd class="font-bold text-slate-900 dark:text-white text-right break-words">{{ $reminder->target_type_label }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-600 dark:text-slate-400 shrink-0">Jadwal:</dt>
                        <dd class="font-semibold text-slate-900 dark:text-slate-200 text-right break-words">{{ $reminder->scheduled_at?->format('d M Y, H:i') ?? 'Manual' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-600 dark:text-slate-400 shrink-0">Terakhir Dikirim:</dt>
                        <dd class="font-semibold text-slate-900 dark:text-slate-200 text-right break-words">{{ $reminder->sent_at?->format('d M Y, H:i:s') ?? 'Belum pernah' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-600 dark:text-slate-400 shrink-0">Total Target:</dt>
                        <dd class="font-bold text-slate-900 dark:text-white text-right">{{ $reminder->total_targets }} mahasiswa</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-600 dark:text-slate-400 shrink-0">Berhasil Terkirim:</dt>
                        <dd class="font-bold text-emerald-700 dark:text-emerald-400 text-right">{{ $reminder->sent_count }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-600 dark:text-slate-400 shrink-0">Gagal / Belum Diuji:</dt>
                        <dd class="font-bold text-red-700 dark:text-red-400 text-right">{{ $reminder->failed_count }}</dd>
                    </div>
                </dl>

                @if($reminder->last_error)
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 text-xs text-amber-950 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/40 p-3 rounded-lg border border-amber-300 dark:border-amber-700">
                        <span class="font-bold block mb-0.5">Catatan Pengiriman Terakhir:</span>
                        {{ $reminder->last_error }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Tabel Log Pengiriman -->
        <section aria-labelledby="heading-logs" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 space-y-4 shadow-xs">
            <h2 id="heading-logs" class="text-base font-bold text-slate-900 dark:text-white">Riwayat Log Pengiriman per Mahasiswa</h2>

            @if($logs->isEmpty())
                <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-lg p-6 text-center text-sm text-slate-700 dark:text-slate-300">
                    Belum ada riwayat pengiriman untuk reminder ini. Tekan tombol <strong>Kirim Manual Sekarang via Fonnte</strong> di atas untuk menjalankan pengiriman.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900/70 text-slate-900 dark:text-slate-200 text-xs uppercase">
                                <th class="py-3 px-3 font-bold">Waktu</th>
                                <th class="py-3 px-3 font-bold">Nama Mahasiswa</th>
                                <th class="py-3 px-3 font-bold">Nomor WhatsApp</th>
                                <th class="py-3 px-3 font-bold">Status Pengiriman</th>
                                <th class="py-3 px-3 font-bold">Respons Gateway</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($logs as $log)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                    <td class="py-3 px-3 text-xs text-slate-700 dark:text-slate-300">
                                        {{ $log->sent_at?->format('d M Y H:i:s') ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-slate-900 dark:text-white">
                                        {{ $log->recipient_name }}
                                    </td>
                                    <td class="py-3 px-3 font-mono text-xs text-slate-900 dark:text-slate-200">
                                        +{{ $log->whatsapp_number }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-block text-xs font-bold px-2 py-0.5 rounded {{ $log->status === 'sent' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : ($log->status === 'unverified_no_token' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700' : 'bg-red-100 dark:bg-red-950/60 text-red-950 dark:text-red-300 border border-red-300 dark:border-red-700') }}">
                                            {{ $log->status === 'sent' ? 'Terkirim' : ($log->status === 'unverified_no_token' ? 'Belum Diuji (Tanpa Token)' : 'Gagal') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-xs font-mono text-slate-700 dark:text-slate-300 break-all max-w-md">
                                        {{ \Illuminate\Support\Str::limit($log->provider_response, 140) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
