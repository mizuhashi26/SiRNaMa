@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Informasi Kampus' : 'Tambah Informasi Kampus') . ' - Admin CRM UNPAM')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    {{ $isEdit ? 'Ubah Informasi Kampus' : 'Tambah Informasi Kampus Baru' }}
                </h1>
                <p class="text-sm text-slate-700">
                    Informasi yang dipublikasikan otomatis tersedia di Website, Chatbot Website, dan Chatbot WhatsApp.
                </p>
            </div>
            <a href="{{ route('admin.informations.index') }}"
               class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded border border-slate-300 bg-white text-sm font-semibold text-slate-800 hover:bg-slate-100">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.informations.update', $information) : route('admin.informations.store') }}"
              method="POST"
              class="bg-white border border-slate-300 rounded-lg p-6 space-y-5">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="title" class="block text-sm font-bold text-slate-900 mb-1">
                    Judul Informasi <span class="text-red-700">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title', $information->title) }}"
                       required
                       placeholder="Contoh: Jadwal Ujian Akhir Semester (UAS) Ganjil Simulasi"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
            </div>

            <div>
                <label for="category" class="block text-sm font-bold text-slate-900 mb-1">
                    Kategori Informasi <span class="text-red-700">*</span>
                </label>
                <select id="category"
                        name="category"
                        required
                        class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 bg-white">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $information->category) === $key)>
                            {{ $label }} {{ $key === 'pembayaran' ? '(Tampil pada Menu 2. Informasi Pembayaran Chatbot)' : '(Tampil pada Menu 1. Informasi Kampus Chatbot)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="summary" class="block text-sm font-bold text-slate-900 mb-1">
                    Ringkasan Singkat (Opsional)
                </label>
                <textarea id="summary"
                          name="summary"
                          rows="2"
                          placeholder="Ringkasan 1-2 kalimat untuk kartu halaman depan dan daftar cepat chatbot..."
                          class="w-full px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">{{ old('summary', $information->summary) }}</textarea>
            </div>

            <div>
                <label for="content" class="block text-sm font-bold text-slate-900 mb-1">
                    Isi Lengkap Informasi <span class="text-red-700">*</span>
                </label>
                <textarea id="content"
                          name="content"
                          rows="8"
                          required
                          placeholder="Tuliskan isi pengumuman, jadwal, atau rincian prosedur secara jelas..."
                          class="w-full px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">{{ old('content', $information->content) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200">
                <div class="flex items-start gap-2.5">
                    <input type="checkbox"
                           id="is_published"
                           name="is_published"
                           value="1"
                           @checked(old('is_published', $information->is_published))
                           class="w-4 h-4 mt-1 rounded border-slate-400 text-unpam-navy">
                    <label for="is_published" class="text-sm text-slate-900">
                        <span class="font-bold block">Publikasikan Sekarang</span>
                        <span class="text-xs text-slate-600">Jika dicentang, langsung tampil di website dan chatbot.</span>
                    </label>
                </div>

                <div class="flex items-start gap-2.5">
                    <input type="checkbox"
                           id="is_pinned"
                           name="is_pinned"
                           value="1"
                           @checked(old('is_pinned', $information->is_pinned))
                           class="w-4 h-4 mt-1 rounded border-slate-400 text-unpam-navy">
                    <label for="is_pinned" class="text-sm text-slate-900">
                        <span class="font-bold block">Sematkan sebagai Informasi Penting</span>
                        <span class="text-xs text-slate-600">Ditampilkan di urutan teratas pada kategorinya.</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.informations.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded border border-slate-300 text-sm font-semibold text-slate-800 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan & Publikasikan' }}
                </button>
            </div>
        </form>
    </div>
@endsection

