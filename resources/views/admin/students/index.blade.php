@extends('layouts.admin')

@section('title', 'Data Mahasiswa Simulasi - Dashboard Admin CRM UNPAM')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Data Mahasiswa Simulasi (Dummy)</h1>
            <p class="text-sm text-slate-700">
                Daftar nomor WhatsApp mahasiswa simulasi yang menerima pengiriman Reminder Pembayaran Umum.
            </p>
        </div>
        <a href="{{ route('admin.students.create') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark self-start sm:self-auto">
            + Tambah Mahasiswa Dummy
        </a>
    </div>

    <form action="{{ route('admin.students.index') }}" method="GET" class="bg-white border border-slate-300 rounded-lg p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label for="q" class="block text-xs font-bold text-slate-800 mb-1">Cari NIM, Nama, Prodi, atau Nomor WhatsApp</label>
            <input type="search"
                   id="q"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Contoh: 2110114001 atau 62812..."
                   class="w-full min-h-[44px] px-3 py-2 rounded border border-slate-400 text-sm text-slate-900">
        </div>
        <div class="sm:w-52">
            <label for="status" class="block text-xs font-bold text-slate-800 mb-1">Status Penerima Reminder</label>
            <select id="status"
                    name="status"
                    class="w-full min-h-[44px] px-3 py-2 rounded border border-slate-400 text-sm text-slate-900 bg-white">
                <option value="">Semua Status</option>
                <option value="active" @selected($status === 'active')>Aktif Menerima</option>
                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="min-h-[44px] px-4 py-2 rounded bg-unpam-navy text-white text-sm font-semibold hover:bg-unpam-navyDark">
                Cari
            </button>
            @if($search !== '' || $status !== '')
                <a href="{{ route('admin.students.index') }}" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded border border-slate-300 text-sm font-medium text-slate-800 hover:bg-slate-100">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($students->isEmpty())
        <div class="bg-white border border-slate-300 rounded-lg p-8 text-center space-y-3">
            <h2 class="text-base font-bold text-slate-900">Belum Ada Data Mahasiswa Simulasi</h2>
            <p class="text-sm text-slate-700">
                Tambahkan data mahasiswa dummy beserta nomor WhatsApp pengujian untuk mencoba pengiriman reminder pembayaran.
            </p>
            <a href="{{ route('admin.students.create') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded bg-unpam-navy text-white text-sm font-bold">
                + Tambah Mahasiswa Dummy
            </a>
        </div>
    @else
        <div class="bg-white border border-slate-300 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-300 bg-slate-100 text-slate-900 text-xs uppercase">
                            <th class="py-3.5 px-4 font-bold">NIM</th>
                            <th class="py-3.5 px-4 font-bold">Nama Mahasiswa</th>
                            <th class="py-3.5 px-4 font-bold">Program Studi & Smt</th>
                            <th class="py-3.5 px-4 font-bold">Nomor WhatsApp</th>
                            <th class="py-3.5 px-4 font-bold">Status</th>
                            <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($students as $student)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-4 font-mono text-xs font-bold text-slate-900">
                                    {{ $student->nim }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $student->name }}
                                    @if($student->notes)
                                        <span class="block text-xs font-normal text-slate-600">{{ $student->notes }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-800">
                                    {{ $student->study_program }} (Smt {{ $student->semester }})
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-900">
                                    +{{ $student->whatsapp_number }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($student->is_active)
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded bg-emerald-100 text-emerald-950 border border-emerald-300">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded bg-slate-200 text-slate-800">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.students.edit', $student) }}"
                                           class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded bg-unpam-navy text-white text-xs font-semibold hover:bg-unpam-navyDark">
                                            Ubah
                                        </a>
                                        <form action="{{ route('admin.students.destroy', $student) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus data mahasiswa simulasi ini?');">
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
            {{ $students->links() }}
        </div>
    @endif
@endsection

