@extends('layouts.admin')

@section('title', 'Reminder Pembayaran - Dashboard Admin UNPAM')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Pengingat Pembayaran Kuliah</h1>
            <p class="text-sm text-slate-700">
                Kirim pengingat jadwal pembayaran ke seluruh mahasiswa aktif ({{ $activeStudentCount }} nomor terdaftar) secara manual maupun terjadwal.
            </p>
        </div>
        <a href="{{ route('admin.reminders.create') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark self-start sm:self-auto">
            + Buat Pengingat Baru
        </a>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs sm:text-sm text-slate-900 space-y-1">
        <p class="font-bold">Status Layanan Gateway WhatsApp (Fonnte):</p>
        <p>
            Status koneksi Fonnte:
            <strong>{{ $fonnteConfigured ? 'TERHUBUNG (Pesan dikirim via API Fonnte)' : 'BELUM DIKONFIGURASI (Masukkan token Fonnte pada file konfigurasi)' }}</strong>.
        </p>
    </div>

    @if($reminders->isEmpty())
        <div class="bg-white border border-slate-300 rounded-lg p-8 text-center space-y-3">
            <h2 class="text-base font-bold text-slate-900">Belum Ada Jadwal Pengingat Pembayaran</h2>
            <p class="text-sm text-slate-700">
                Buat jadwal pengingat pembayaran untuk dikirimkan ke daftar nomor WhatsApp mahasiswa.
            </p>
            <a href="{{ route('admin.reminders.create') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded bg-unpam-navy text-white text-sm font-bold">
                + Buat Pengingat Pembayaran
            </a>
        </div>
    @else
        <div class="bg-white border border-slate-300 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-300 bg-slate-100 text-slate-900 text-xs uppercase">
                            <th class="py-3.5 px-4 font-bold">Judul & Isi Singkat</th>
                            <th class="py-3.5 px-4 font-bold">Jadwal Pengiriman</th>
                            <th class="py-3.5 px-4 font-bold">Status</th>
                            <th class="py-3.5 px-4 font-bold">Rekap Pengiriman</th>
                            <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($reminders as $rem)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('admin.reminders.show', $rem) }}" class="font-bold text-slate-900 hover:underline">
                                        {{ $rem->title }}
                                    </a>
                                    <p class="text-xs text-slate-600 mt-0.5">
                                        {{ \Illuminate\Support\Str::limit($rem->message, 90) }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-800">
                                    @if($rem->scheduled_at)
                                        <span class="font-semibold block">{{ $rem->scheduled_at->format('d M Y, H:i') }} WIB</span>
                                    @else
                                        <span class="text-slate-600">Pengiriman Manual</span>
                                    @endif
                                    @if($rem->sent_at)
                                        <span class="text-slate-600 block mt-0.5">Dieksekusi: {{ $rem->sent_at->format('d M Y, H:i') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeClass = match($rem->status) {
                                            'sent' => 'bg-emerald-100 text-emerald-950 border-emerald-300',
                                            'scheduled' => 'bg-blue-100 text-blue-950 border-blue-300',
                                            'unverified_no_token', 'partial' => 'bg-amber-100 text-slate-950 border-amber-300',
                                            'failed' => 'bg-red-100 text-red-950 border-red-300',
                                            default => 'bg-slate-200 text-slate-800 border-slate-300',
                                        };
                                        $statusText = match($rem->status) {
                                            'sent' => 'Terkirim (Fonnte)',
                                            'scheduled' => 'Terjadwal',
                                            'unverified_no_token' => 'Belum Diuji (Tanpa Token)',
                                            'partial' => 'Terkirim Sebagian',
                                            'failed' => 'Gagal Kirim',
                                            default => 'Draft',
                                        };
                                    @endphp
                                    <span class="inline-block text-xs font-bold px-2.5 py-1 rounded border {{ $badgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-800">
                                    Target: <strong>{{ $rem->total_targets }}</strong> |
                                    Terkirim: <strong class="text-emerald-800">{{ $rem->sent_count }}</strong> |
                                    Gagal/Belum: <strong class="text-red-800">{{ $rem->failed_count }}</strong>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2 flex-wrap justify-end">
                                        <form action="{{ route('admin.reminders.send-now', $rem) }}"
                                              method="POST"
                                              onsubmit="return confirm('Kirim reminder ini sekarang ke seluruh mahasiswa aktif?');">
                                            @csrf
                                            <button type="submit"
                                                    class="min-h-[40px] px-3 py-1.5 rounded bg-unpam-gold text-slate-950 text-xs font-bold hover:bg-unpam-goldHover shadow-sm">
                                                Kirim Manual
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.reminders.show', $rem) }}"
                                           class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded border border-slate-300 text-xs font-semibold text-slate-800 hover:bg-slate-100">
                                            Log
                                        </a>
                                        <a href="{{ route('admin.reminders.edit', $rem) }}"
                                           class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded bg-unpam-navy text-white text-xs font-semibold hover:bg-unpam-navyDark">
                                            Ubah
                                        </a>
                                        <form action="{{ route('admin.reminders.destroy', $rem) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus reminder pembayaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="min-h-[40px] px-3 py-1.5 rounded bg-red-700 text-white text-xs font-semibold hover:bg-red-800">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $reminders->links() }}
        </div>
    @endif
@endsection

