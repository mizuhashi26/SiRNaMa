@extends('layouts.admin')

@section('title', 'Pengingat Mahasiswa - Dashboard Admin UNPAM')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Pengingat (Reminder) Mahasiswa</h1>
            <p class="text-sm text-slate-700">
                Kirim pengingat massal (Pembayaran, Akademik, Ujian, Pengumuman, Administrasi) ke {{ $activeStudentCount }} mahasiswa aktif secara manual maupun terjadwal.
            </p>
        </div>
        <a href="{{ route('admin.reminders.create') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-slate-800 self-start sm:self-auto shadow-sm">
            + Buat Pengingat Baru
        </a>
    </div>

    @if($reminders->isEmpty())
        <div class="bg-white border border-slate-300 rounded-lg p-8 text-center space-y-3">
            <h2 class="text-base font-bold text-slate-900">Belum Ada Jadwal Pengingat Mahasiswa</h2>
            <p class="text-sm text-slate-700">
                Buat pengingat baru untuk dikirimkan secara serentak ke seluruh nomor WhatsApp mahasiswa.
            </p>
            <a href="{{ route('admin.reminders.create') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded bg-unpam-navy text-white text-sm font-bold">
                + Buat Pengingat Baru
            </a>
        </div>
    @else
        <div class="bg-white border border-slate-300 rounded-lg overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-300 bg-slate-100 text-slate-900 text-xs uppercase">
                            <th class="py-3.5 px-4 font-bold">Judul &amp; Kategori</th>
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
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $rem->category_label }}
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.reminders.show', $rem) }}" class="font-bold text-slate-900 hover:text-unpam-navy hover:underline">
                                        {{ $rem->title }}
                                    </a>
                                    <p class="text-xs text-slate-600 mt-0.5">
                                        {{ \Illuminate\Support\Str::limit($rem->message, 80) }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-800">
                                    @if($rem->scheduled_at)
                                        <span class="font-semibold block">{{ $rem->scheduled_at->format('d M Y, H:i') }} WIB</span>
                                    @else
                                        <span class="text-slate-600 font-medium">Pengiriman Manual</span>
                                    @endif
                                    @if($rem->sent_at)
                                        <span class="text-slate-500 block mt-0.5 text-[11px]">Dieksekusi: {{ $rem->sent_at->format('d M Y, H:i') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeClass = match($rem->status) {
                                            'sent' => 'bg-emerald-100 text-emerald-950 border border-emerald-300',
                                            'partial' => 'bg-amber-100 text-amber-950 border border-amber-300',
                                            'scheduled' => 'bg-blue-100 text-blue-950 border border-blue-300',
                                            'failed' => 'bg-red-100 text-red-950 border border-red-300',
                                            default => 'bg-slate-200 text-slate-800',
                                        };
                                    @endphp
                                    <span class="inline-block text-xs font-bold px-2.5 py-1 rounded {{ $badgeClass }}">
                                        {{ strtoupper($rem->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-800 font-mono">
                                    @if($rem->status === 'sent' || $rem->status === 'partial')
                                        <span class="text-emerald-700 font-bold">{{ $rem->sent_count }} terkirim</span>
                                        @if($rem->failed_count > 0)
                                            / <span class="text-red-700">{{ $rem->failed_count }} gagal</span>
                                        @endif
                                    @else
                                        <span class="text-slate-500">{{ $rem->total_targets }} target</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('admin.reminders.show', $rem) }}"
                                           class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded border border-slate-300 bg-white text-xs font-semibold text-slate-800 hover:bg-slate-100">
                                            Detail &amp; Log
                                        </a>
                                        @if($rem->status !== 'sent')
                                            <form action="{{ route('admin.reminders.send-now', $rem) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Kirim pengingat ini sekarang ke seluruh mahasiswa aktif?');">
                                                @csrf
                                                <button type="submit"
                                                        class="min-h-[38px] px-3 py-1.5 rounded bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">
                                                    Kirim
                                                </button>
                                            </form>
                                        @endif
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
