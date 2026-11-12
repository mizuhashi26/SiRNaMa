@extends('layouts.admin')

@section('title', 'Pengingat Mahasiswa - Dashboard Admin UNPAM')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 dark:border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Pengingat (Reminder) Mahasiswa</h1>
            <p class="text-sm text-slate-700 dark:text-slate-300">
                Kirim pengingat massal (Pembayaran, Akademik, Ujian, Pengumuman, Administrasi) ke {{ $activeStudentCount }} mahasiswa aktif secara manual maupun terjadwal.
            </p>
        </div>
        <a href="{{ route('admin.reminders.create') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded-xl bg-unpam-navy text-white text-sm font-bold hover:bg-slate-800 self-start sm:self-auto shadow-sm">
            + Buat Pengingat Baru
        </a>
    </div>

    @if($reminders->isEmpty())
        <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-8 text-center space-y-3 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Jadwal Pengingat Mahasiswa</h2>
            <p class="text-sm text-slate-700 dark:text-slate-300">
                Buat pengingat baru untuk dikirimkan secara serentak ke seluruh nomor WhatsApp mahasiswa.
            </p>
            <a href="{{ route('admin.reminders.create') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl bg-unpam-navy text-white text-sm font-bold">
                + Buat Pengingat Baru
            </a>
        </div>
    @else
        <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900/70 text-slate-900 dark:text-slate-200 text-xs uppercase">
                            <th class="py-3.5 px-4 font-bold">Judul &amp; Kategori</th>
                            <th class="py-3.5 px-4 font-bold">Jadwal Pengiriman</th>
                            <th class="py-3.5 px-4 font-bold">Status</th>
                            <th class="py-3.5 px-4 font-bold">Rekap Pengiriman</th>
                            <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($reminders as $rem)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                        <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                            {{ $rem->category_label }}
                                        </span>
                                        @if(($rem->target_type ?? 'all') === 'personal')
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-900 dark:text-purple-300 border border-purple-300 dark:border-purple-700">
                                                Personal (1 Penerima)
                                            </span>
                                        @elseif(($rem->target_type ?? 'all') === 'selected')
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-900 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-700">
                                                Beberapa Nomor
                                            </span>
                                        @else
                                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                                Semua Mhs
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.reminders.show', $rem) }}" class="font-bold text-slate-900 dark:text-white hover:text-unpam-navy dark:hover:text-amber-400 hover:underline">
                                        {{ $rem->title }}
                                    </a>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                        {{ \Illuminate\Support\Str::limit($rem->message, 80) }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-800 dark:text-slate-200">
                                    @if($rem->scheduled_at)
                                        <span class="font-semibold block">{{ $rem->scheduled_at->format('d M Y, H:i') }} WIB</span>
                                    @else
                                        <span class="text-slate-600 dark:text-slate-400 font-medium">Pengiriman Manual</span>
                                    @endif
                                    @if($rem->sent_at)
                                        <span class="text-slate-500 dark:text-slate-400 block mt-0.5 text-[11px]">Dieksekusi: {{ $rem->sent_at->format('d M Y, H:i') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeClass = match($rem->status) {
                                            'sent' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700',
                                            'partial' => 'bg-amber-100 dark:bg-amber-950/60 text-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700',
                                            'scheduled' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-950 dark:text-blue-300 border border-blue-300 dark:border-blue-700',
                                            'failed' => 'bg-red-100 dark:bg-red-950/60 text-red-950 dark:text-red-300 border border-red-300 dark:border-red-700',
                                            default => 'bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-block text-xs font-bold px-2.5 py-1 rounded {{ $badgeClass }}">
                                        {{ strtoupper($rem->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-800 dark:text-slate-200 font-mono">
                                    @if($rem->status === 'sent' || $rem->status === 'partial')
                                        <span class="text-emerald-700 dark:text-emerald-400 font-bold">{{ $rem->sent_count }} terkirim</span>
                                        @if($rem->failed_count > 0)
                                            / <span class="text-red-700 dark:text-red-400">{{ $rem->failed_count }} gagal</span>
                                        @endif
                                    @else
                                        <span class="text-slate-500 dark:text-slate-400">{{ $rem->total_targets }} target</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('admin.reminders.show', $rem) }}"
                                           class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-600">
                                            Detail &amp; Log
                                        </a>
                                        @if($rem->status !== 'sent')
                                            <form action="{{ route('admin.reminders.send-now', $rem) }}"
                                                  method="POST"
                                                  data-confirm="Kirim pengingat ini sekarang ke target WhatsApp?">
                                                @csrf
                                                <button type="submit"
                                                        class="min-h-[38px] inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-sm">
                                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                                    </svg>
                                                    <span>Kirim Sekarang</span>
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
