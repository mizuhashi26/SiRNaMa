@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Data Mahasiswa' : 'Tambah Mahasiswa') . ' - Admin UNPAM')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    {{ $isEdit ? 'Ubah Data Mahasiswa' : 'Tambah Data Mahasiswa' }}
                </h1>
                <p class="text-sm text-slate-700">
                    Kelola data mahasiswa dan nomor kontak WhatsApp aktif.
                </p>
            </div>
            <a href="{{ route('admin.students.index') }}"
               class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded border border-slate-300 bg-white text-sm font-semibold text-slate-800 hover:bg-slate-100">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.students.update', $student) : route('admin.students.store') }}"
              method="POST"
              class="bg-white border border-slate-300 rounded-lg p-6 space-y-4">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nim" class="block text-sm font-bold text-slate-900 mb-1">
                        NIM <span class="text-red-700">*</span>
                    </label>
                    <input type="text"
                           id="nim"
                           name="nim"
                           value="{{ old('nim', $student->nim) }}"
                           required
                           placeholder="Contoh: 2110114001"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 font-mono">
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-sm font-bold text-slate-900 mb-1">
                        Nomor WhatsApp <span class="text-red-700">*</span>
                    </label>
                    <input type="text"
                           id="whatsapp_number"
                           name="whatsapp_number"
                           value="{{ old('whatsapp_number', $student->whatsapp_number) }}"
                           required
                           placeholder="Contoh: 081234567890 atau 6281234567890"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 font-mono">
                    <p class="text-xs text-slate-600 mt-1">Otomatis dinormalisasi ke format 628 saat disimpan.</p>
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-bold text-slate-900 mb-1">
                    Nama Lengkap Mahasiswa <span class="text-red-700">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $student->name) }}"
                       required
                       placeholder="Contoh: Rizky Pratama"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="study_program" class="block text-sm font-bold text-slate-900 mb-1">
                        Program Studi <span class="text-red-700">*</span>
                    </label>
                    <input type="text"
                           id="study_program"
                           name="study_program"
                           value="{{ old('study_program', $student->study_program ?: 'Teknik Informatika S1') }}"
                           required
                           placeholder="Teknik Informatika S1"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
                </div>

                <div>
                    <label for="semester" class="block text-sm font-bold text-slate-900 mb-1">
                        Semester <span class="text-red-700">*</span>
                    </label>
                    <input type="number"
                           id="semester"
                           name="semester"
                           min="1"
                           max="14"
                           value="{{ old('semester', $student->semester ?? 5) }}"
                           required
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-bold text-slate-900 mb-1">
                    Catatan (Opsional)
                </label>
                <input type="text"
                       id="notes"
                       name="notes"
                       value="{{ old('notes', $student->notes) }}"
                       placeholder="Catatan tambahan mahasiswa"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
            </div>

            <div class="pt-2 border-t border-slate-200 flex items-start gap-2.5">
                <input type="checkbox"
                       id="is_active"
                       name="is_active"
                       value="1"
                       @checked(old('is_active', $student->is_active))
                       class="w-4 h-4 mt-1 rounded border-slate-400 text-unpam-navy">
                <label for="is_active" class="text-sm text-slate-900">
                    <span class="font-bold block">Aktif Menerima Reminder Pembayaran</span>
                    <span class="text-xs text-slate-600">Jika dicentang, nomor ini diikutsertakan saat pengiriman reminder pembayaran umum.</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.students.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded border border-slate-300 text-sm font-semibold text-slate-800 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Mahasiswa' }}
                </button>
            </div>
        </form>
    </div>
@endsection

