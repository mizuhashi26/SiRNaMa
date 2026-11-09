@extends('layouts.admin')

@section('title', 'Kelola Informasi Kampus - Dashboard Admin CRM UNPAM')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-300 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Pengelolaan Informasi Kampus</h1>
            <p class="text-sm text-slate-700">
                Satu sumber data untuk Halaman Website Publik, Chatbot Website, dan Chatbot WhatsApp.
            </p>
        </div>
        <a href="{{ route('admin.informations.create') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark self-start sm:self-auto">
            + Tambah Informasi Baru
        </a>
    </div>

    <!-- Filter & Pencarian -->
    <form action="{{ route('admin.informations.index') }}" method="GET" class="bg-white border border-slate-300 rounded-lg p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label for="q" class="block text-xs font-bold text-slate-800 mb-1">Cari Judul atau Isi Informasi</label>
            <input type="search"
                   id="q"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Ketik kata kunci pengumuman, ujian, atau pembayaran..."
                   class="w-full min-h-[44px] px-3 py-2 rounded border border-slate-400 text-sm text-slate-900">
        </div>
        <div class="sm:w-64">
            <label for="category" class="block text-xs font-bold text-slate-800 mb-1">Filter Kategori</label>
            <select id="category"
                    name="category"
                    class="w-full min-h-[44px] px-3 py-2 rounded border border-slate-400 text-sm text-slate-900 bg-white">
                <option value="">Semua Kategori</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="min-h-[44px] px-4 py-2 rounded bg-unpam-navy text-white text-sm font-semibold hover:bg-unpam-navyDark">
                Terapkan
            </button>
            @if($search !== '' || $selectedCategory !== '')
                <a href="{{ route('admin.informations.index') }}" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded border border-slate-300 text-sm font-medium text-slate-800 hover:bg-slate-100">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($informations->isEmpty())
        <div class="bg-white border border-slate-300 rounded-lg p-8 text-center space-y-3">
            <h2 class="text-base font-bold text-slate-900">Belum Ada Data Informasi Kampus</h2>
            <p class="text-sm text-slate-700">
                Tambahkan informasi akademik atau jadwal pembayaran umum agar dapat diakses mahasiswa di website dan chatbot.
            </p>
            <a href="{{ route('admin.informations.create') }}"
               class="min-h-[44px] inline-flex items-center px-4 py-2 rounded bg-unpam-navy text-white text-sm font-bold">
                + Buat Informasi Kampus Pertama
            </a>
        </div>
    @else
        <div class="bg-white border border-slate-300 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-300 bg-slate-100 text-slate-900 text-xs uppercase">
                            <th class="py-3.5 px-4 font-bold">Kode / Judul Informasi</th>
                            <th class="py-3.5 px-4 font-bold">Kategori</th>
                            <th class="py-3.5 px-4 font-bold">Status Publikasi</th>
                            <th class="py-3.5 px-4 font-bold">Diperbarui</th>
                            <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($informations as $info)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-mono bg-slate-100 border border-slate-300 px-1.5 py-0.5 rounded text-slate-800">
                                            INFO {{ $info->id }}
                                        </span>
                                        @if($info->is_pinned)
                                            <span class="text-xs font-bold bg-amber-200 text-slate-950 px-2 py-0.5 rounded">
                                                Disematkan
                                            </span>
                                        @endif
                                    </div>
                                    <p class="font-bold text-slate-900 mt-1">{{ $info->title }}</p>
                                    @if($info->summary)
                                        <p class="text-xs text-slate-600 mt-0.5">{{ \Illuminate\Support\Str::limit($info->summary, 95) }}</p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded {{ $info->category === 'pembayaran' ? 'bg-amber-100 text-slate-950 border border-amber-300' : 'bg-slate-100 text-slate-900 border border-slate-300' }}">
                                        {{ $info->category_label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($info->is_published)
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded bg-emerald-100 text-emerald-950 border border-emerald-300">
                                            Dipublikasikan
                                        </span>
                                    @else
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded bg-slate-200 text-slate-800">
                                            Draft (Tersembunyi)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-700">
                                    {{ $info->updated_at->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if($info->is_published)
                                            <a href="{{ route('public.show', $info->slug) }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded border border-slate-300 text-xs font-semibold text-slate-800 hover:bg-slate-100">
                                                Lihat
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.informations.edit', $info) }}"
                                           class="min-h-[40px] inline-flex items-center px-3 py-1.5 rounded bg-unpam-navy text-white text-xs font-semibold hover:bg-unpam-navyDark">
                                            Ubah
                                        </a>
                                        <form action="{{ route('admin.informations.destroy', $info) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus informasi ini dari website dan chatbot?');">
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
            {{ $informations->links() }}
        </div>
    @endif
@endsection

