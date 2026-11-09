@extends('layouts.admin')

@section('title', 'Detail & Log Pengingat Pembayaran - Admin UNPAM')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ $reminder->title }}</h1>
                <p class="text-sm text-slate-700">
                    Detail pesan pengingat pembayaran dan riwayat pengiriman per mahasiswa.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('admin.reminders.send-now', $reminder) }}"
                      method="POST"
                      onsubmit="return confirm('Kirim pengingat ini sekarang ke seluruh mahasiswa aktif?');">
                    @csrf
                    <button type="submit"
                            class="min-h-[44px] px-4 py-2 rounded bg-unpam-gold text-slate-950 text-sm font-bold hover:bg-unpam-goldHover">
                        Kirim Manual Sekarang via Fonnte
                    </button>
                </form>
                <a href="{{ route('admin.reminders.index') }}"
                   class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded border border-slate-300 bg-white text-sm font-semibold text-slate-800 hover:bg-slate-100">
                    Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white border border-slate-300 rounded-lg p-5 space-y-3">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-2">Isi Pesan Reminder</h2>
                <div class="bg-slate-50 border border-slate-200 rounded p-4 text-sm text-slate-900 whitespace-pre-line leading-relaxed">{{ $reminder->message }}</div>
            </div>

            <div class="bg-white border border-slate-300 rounded-lg p-5 space-y-3 text-sm">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-2">Status & Ringkasan</h2>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Status Terakhir:</dt>
                        <dd class="font-bold text-slate-900">{{ strtoupper($reminder->status) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Jadwal:</dt>
                        <dd class="font-semibold text-slate-900">{{ $reminder->scheduled_at?->format('d M Y, H:i') ?? 'Manual' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Terakhir Dikirim:</dt>
                        <dd class="font-semibold text-slate-900">{{ $reminder->sent_at?->format('d M Y, H:i:s') ?? 'Belum pernah' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Total Target:</dt>
                        <dd class="font-bold text-slate-900">{{ $reminder->total_targets }} mahasiswa</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Berhasil Terkirim:</dt>
                        <dd class="font-bold text-emerald-800">{{ $reminder->sent_count }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Gagal / Belum Diuji:</dt>
                        <dd class="font-bold text-red-800">{{ $reminder->failed_count }}</dd>
                    </div>
                </dl>

                @if($reminder->last_error)
                    <div class="pt-2 border-t border-slate-200 text-xs text-amber-950 bg-amber-50 p-3 rounded border border-amber-300">
                        <span class="font-bold block mb-0.5">Catatan Pengiriman Terakhir:</span>
                        {{ $reminder->last_error }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Tabel Log Pengiriman -->
        <section aria-labelledby="heading-logs" class="bg-white border border-slate-300 rounded-lg p-5 space-y-4">
            <h2 id="heading-logs" class="text-base font-bold text-slate-900">Riwayat Log Pengiriman per Mahasiswa</h2>

            @if($logs->isEmpty())
                <div class="bg-slate-50 border border-slate-200 rounded p-6 text-center text-sm text-slate-700">
                    Belum ada riwayat pengiriman untuk reminder ini. Tekan tombol <strong>Kirim Manual Sekarang via Fonnte</strong> di atas untuk menjalankan pengiriman.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-300 bg-slate-100 text-slate-900 text-xs uppercase">
                                <th class="py-3 px-3 font-bold">Waktu</th>
                                <th class="py-3 px-3 font-bold">Nama Mahasiswa</th>
                                <th class="py-3 px-3 font-bold">Nomor WhatsApp</th>
                                <th class="py-3 px-3 font-bold">Status Pengiriman</th>
                                <th class="py-3 px-3 font-bold">Respons Gateway</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($logs as $log)
                                <tr>
                                    <td class="py-3 px-3 text-xs text-slate-700">
                                        {{ $log->sent_at?->format('d M Y H:i:s') ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-slate-900">
                                        {{ $log->recipient_name }}
                                    </td>
                                    <td class="py-3 px-3 font-mono text-xs text-slate-900">
                                        +{{ $log->whatsapp_number }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-block text-xs font-bold px-2 py-0.5 rounded {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-950' : ($log->status === 'unverified_no_token' ? 'bg-amber-100 text-slate-950' : 'bg-red-100 text-red-950') }}">
                                            {{ $log->status === 'sent' ? 'Terkirim' : ($log->status === 'unverified_no_token' ? 'Belum Diuji (Tanpa Token)' : 'Gagal') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-xs font-mono text-slate-700 break-all max-w-md">
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

