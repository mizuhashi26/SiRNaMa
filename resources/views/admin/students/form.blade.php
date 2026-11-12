@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Data Mahasiswa' : 'Tambah Mahasiswa') . ' - Admin UNPAM')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 dark:border-slate-700 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                    {{ $isEdit ? 'Ubah Data Mahasiswa' : 'Tambah Data Mahasiswa' }}
                </h1>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    Kelola data mahasiswa dan nomor kontak WhatsApp aktif.
                </p>
            </div>
            <a href="{{ route('admin.students.index') }}"
               class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-750 transition-colors">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.students.update', $student) : route('admin.students.store') }}"
              method="POST"
              class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-6 space-y-4 shadow-xs">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nim" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                        NIM (12 Digit) <span class="text-red-700 dark:text-red-400">*</span>
                    </label>
                    <input type="text"
                           id="nim"
                           name="nim"
                           value="{{ old('nim', $student->nim) }}"
                           required
                           maxlength="12"
                           minlength="12"
                           pattern="[0-9]{12}"
                           inputmode="numeric"
                           placeholder="Contoh: 211011400001"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white font-mono tracking-wider focus:ring-2 focus:ring-unpam-navy">
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Harus tepat 12 digit angka sesuai standar UNPAM.</p>
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                        Nomor WhatsApp <span class="text-red-700 dark:text-red-400">*</span>
                    </label>
                    <input type="text"
                           id="whatsapp_number"
                           name="whatsapp_number"
                           value="{{ old('whatsapp_number', $student->whatsapp_number) }}"
                           required
                           placeholder="Contoh: 081234567890 atau 6281234567890"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-unpam-navy">
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Otomatis dinormalisasi ke format 628 saat disimpan.</p>
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Nama Lengkap Mahasiswa <span class="text-red-700 dark:text-red-400">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $student->name) }}"
                       required
                       placeholder="Contoh: Rizky Pratama"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="study_program" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                        Program Studi / Jurusan <span class="text-red-700 dark:text-red-400">*</span>
                    </label>
                    <select id="study_program"
                            name="study_program"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 text-sm text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:ring-2 focus:ring-unpam-navy">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($studyPrograms as $program)
                            <option value="{{ $program }}" @selected(old('study_program', $student->study_program) === $program)>
                                {{ $program }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="regular_class" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                            Kelas Reguler <span class="text-red-700 dark:text-red-400">*</span>
                        </label>
                        <select id="regular_class"
                                name="regular_class"
                                required
                                class="w-full min-h-[44px] px-3 py-2 rounded-lg border border-slate-400 dark:border-slate-600 text-sm text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:ring-2 focus:ring-unpam-navy">
                            @foreach($regularClasses as $key => $label)
                                <option value="{{ $key }}" @selected(old('regular_class', $student->regular_class ?? 'reg_a') === $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="semester" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                            Semester <span class="text-red-700 dark:text-red-400">*</span>
                        </label>
                        <input type="number"
                               id="semester"
                               name="semester"
                               min="1"
                               max="14"
                               value="{{ old('semester', $student->semester ?? 1) }}"
                               required
                               class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                    </div>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Catatan (Opsional)
                </label>
                <input type="text"
                       id="notes"
                       name="notes"
                       value="{{ old('notes', $student->notes) }}"
                       placeholder="Catatan tambahan mahasiswa"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-start gap-2.5">
                <input type="checkbox"
                       id="is_active"
                       name="is_active"
                       value="1"
                       @checked(old('is_active', $student->is_active))
                       class="w-4 h-4 mt-1 rounded border-slate-400 dark:border-slate-600 text-unpam-navy">
                <label for="is_active" class="text-sm text-slate-900 dark:text-white">
                    <span class="font-bold block">Aktif Menerima Reminder Pembayaran</span>
                    <span class="text-xs text-slate-600 dark:text-slate-400">Jika dicentang, nomor ini diikutsertakan saat pengiriman reminder pembayaran umum.</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <a href="{{ route('admin.students.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-750 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2.5 rounded-xl bg-unpam-navy dark:bg-slate-700 text-white text-sm font-bold hover:bg-slate-800 dark:hover:bg-slate-600 shadow-xs transition-colors">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Mahasiswa' }}
                </button>
            </div>
        </form>
    </div>
@endsection
