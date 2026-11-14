@extends('layouts.admin')

@section('title', 'Tambah Mahasiswa Massal - Dashboard Admin UNPAM')

@section('content')
@php
    $activeMode = old('mode', request()->query('mode', 'rows'));
    if (! in_array($activeMode, ['rows', 'text'], true)) {
        $activeMode = 'rows';
    }

    $defaultRow = [
        'nim' => '',
        'name' => '',
        'whatsapp_number' => '',
        'study_program' => $studyPrograms[0] ?? '',
        'regular_class' => 'reg_a',
        'semester' => 1,
        'notes' => '',
        'is_active' => '1',
    ];

    $initialRows = old('rows');
    if (! is_array($initialRows) || empty($initialRows)) {
        $initialRows = [$defaultRow, $defaultRow, $defaultRow];
    } else {
        $initialRows = array_values($initialRows);
    }
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-300 dark:border-slate-700 pb-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white leading-tight break-words">
                Tambah Mahasiswa Secara Massal
            </h1>
            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 mt-1">
                Masukkan banyak data mahasiswa sekaligus langsung dari browser tanpa perlu membuat atau mengunggah file CSV/Excel.
            </p>
        </div>
        <a href="{{ route('admin.students.index') }}"
           class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors self-start sm:self-auto shrink-0">
            &larr; Kembali ke Data Mahasiswa
        </a>
    </div>

    <!-- Switcher Mode Input Massal -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-2 flex flex-wrap gap-2 shadow-xs"
         role="tablist"
         aria-label="Metode Tambah Mahasiswa Massal">
        <button type="button"
                id="tab-btn-rows"
                role="tab"
                aria-selected="{{ $activeMode === 'rows' ? 'true' : 'false' }}"
                aria-controls="panel-mode-rows"
                onclick="switchBulkMode('rows')"
                class="flex-1 min-w-[200px] min-h-[44px] px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold text-left sm:text-center transition-colors cursor-pointer {{ $activeMode === 'rows' ? 'bg-unpam-navy text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            1. Form Multi-Baris Interaktif
        </button>
        <button type="button"
                id="tab-btn-text"
                role="tab"
                aria-selected="{{ $activeMode === 'text' ? 'true' : 'false' }}"
                aria-controls="panel-mode-text"
                onclick="switchBulkMode('text')"
                class="flex-1 min-w-[200px] min-h-[44px] px-4 py-2.5 rounded-lg text-xs sm:text-sm font-bold text-left sm:text-center transition-colors cursor-pointer {{ $activeMode === 'text' ? 'bg-unpam-navy text-white shadow-xs' : 'bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            2. Tempel Daftar Teks (Copy-Paste)
        </button>
    </div>

    <!-- MODE 1: FORM MULTI-BARIS INTERAKTIF -->
    <section id="panel-mode-rows"
             role="tabpanel"
             aria-labelledby="tab-btn-rows"
             class="{{ $activeMode === 'rows' ? '' : 'hidden' }} space-y-5">
        <form action="{{ route('admin.students.bulk-store') }}" method="POST" class="space-y-5" id="bulkRowsForm">
            @csrf
            <input type="hidden" name="mode" value="rows">

            <!-- Petunjuk & Kontrol Atas -->
            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 space-y-1 min-w-0">
                    <p class="font-bold text-slate-900 dark:text-white">Isi baris mahasiswa di bawah ini</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Baris yang dibiarkan kosong seluruhnya akan diabaikan otomatis. Gunakan tombol <strong>Duplikat</strong> bila kelas/prodi mahasiswa berikutnya sama.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <span class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-800 dark:text-slate-200">
                        Jumlah Baris: <span id="rowsCountLabel">{{ count($initialRows) }}</span>
                    </span>
                </div>
            </div>

            <!-- Container Kartu Baris Mahasiswa -->
            <div id="studentRowsContainer" class="space-y-4">
                @foreach($initialRows as $idx => $row)
                    @php
                        $rowErrors = [];
                        foreach (['nim', 'name', 'whatsapp_number', 'study_program', 'regular_class', 'semester', 'notes'] as $fieldKey) {
                            foreach ($errors->get("rows.{$idx}.{$fieldKey}") as $msg) {
                                $rowErrors[] = $msg;
                            }
                        }
                        $isRowActive = array_key_exists('is_active', $row) ? ! empty($row['is_active']) : true;
                    @endphp
                    <div class="student-row-card bg-white dark:bg-slate-800 border {{ empty($rowErrors) ? 'border-slate-300 dark:border-slate-700' : 'border-red-400 dark:border-red-600' }} rounded-xl p-4 sm:p-5 space-y-4 shadow-xs"
                         data-row-index="{{ $idx }}">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <span class="row-number-badge inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-unpam-navy dark:bg-slate-700 text-white text-xs font-extrabold">
                                    Mahasiswa #<span class="row-number">{{ $loop->iteration }}</span>
                                </span>
                                @if(! empty($rowErrors))
                                    <span class="text-xs font-bold text-red-700 dark:text-red-400">Perlu perbaikan</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button"
                                        onclick="duplicateStudentRow(this)"
                                        class="min-h-[38px] px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-xs font-semibold text-slate-800 dark:text-slate-200 transition-colors cursor-pointer">
                                    Duplikat Prodi &amp; Kelas
                                </button>
                                <button type="button"
                                        onclick="removeStudentRow(this)"
                                        class="min-h-[38px] px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-xs font-bold text-red-700 dark:text-red-300 transition-colors cursor-pointer">
                                    Hapus Baris
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                            <div class="lg:col-span-3">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_nim_{{ $idx }}">
                                    NIM (12 Digit) <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <input type="text"
                                       id="row_nim_{{ $idx }}"
                                       name="rows[{{ $idx }}][nim]"
                                       value="{{ $row['nim'] ?? '' }}"
                                       maxlength="12"
                                       inputmode="numeric"
                                       placeholder="211011400001"
                                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-unpam-navy">
                            </div>

                            <div class="lg:col-span-5">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_name_{{ $idx }}">
                                    Nama Lengkap <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <input type="text"
                                       id="row_name_{{ $idx }}"
                                       name="rows[{{ $idx }}][name]"
                                       value="{{ $row['name'] ?? '' }}"
                                       maxlength="150"
                                       placeholder="Nama lengkap mahasiswa"
                                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                            </div>

                            <div class="lg:col-span-4">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_wa_{{ $idx }}">
                                    Nomor WhatsApp <span class="text-red-600 dark:text-red-400">*</span>
                                </label>
                                <input type="text"
                                       id="row_wa_{{ $idx }}"
                                       name="rows[{{ $idx }}][whatsapp_number]"
                                       value="{{ $row['whatsapp_number'] ?? '' }}"
                                       maxlength="30"
                                       placeholder="0812xxx atau 62812xxx"
                                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-unpam-navy">
                            </div>

                            <div class="lg:col-span-4">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_prodi_{{ $idx }}">
                                    Program Studi
                                </label>
                                <select id="row_prodi_{{ $idx }}"
                                        name="rows[{{ $idx }}][study_program]"
                                        class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                                    @foreach($studyPrograms as $program)
                                        <option value="{{ $program }}" @selected(($row['study_program'] ?? ($studyPrograms[0] ?? '')) === $program)>
                                            {{ $program }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="lg:col-span-3">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_reg_{{ $idx }}">
                                    Kelas Reguler
                                </label>
                                <select id="row_reg_{{ $idx }}"
                                        name="rows[{{ $idx }}][regular_class]"
                                        class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                                    @foreach($regularClasses as $key => $label)
                                        <option value="{{ $key }}" @selected(($row['regular_class'] ?? 'reg_a') === $key)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_sem_{{ $idx }}">
                                    Semester
                                </label>
                                <input type="number"
                                       id="row_sem_{{ $idx }}"
                                       name="rows[{{ $idx }}][semester]"
                                       min="1"
                                       max="14"
                                       value="{{ $row['semester'] ?? 1 }}"
                                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_notes_{{ $idx }}">
                                    Catatan (Opsional)
                                </label>
                                <input type="text"
                                       id="row_notes_{{ $idx }}"
                                       name="rows[{{ $idx }}][notes]"
                                       value="{{ $row['notes'] ?? '' }}"
                                       maxlength="500"
                                       placeholder="Catatan tambahan"
                                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                            <label class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 cursor-pointer">
                                <input type="checkbox"
                                       name="rows[{{ $idx }}][is_active]"
                                       value="1"
                                       @checked($isRowActive)
                                       class="w-4 h-4 rounded border-slate-400 dark:border-slate-600 text-unpam-navy">
                                <span>Aktif menerima reminder pembayaran WhatsApp</span>
                            </label>
                        </div>

                        @if(! empty($rowErrors))
                            <div class="bg-red-50 dark:bg-red-950/50 border border-red-300 dark:border-red-700 rounded-lg px-3 py-2 text-xs text-red-900 dark:text-red-200">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($rowErrors as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Bar Tambah Baris -->
            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button"
                        onclick="addStudentRows(1)"
                        class="min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-unpam-navy dark:border-amber-400 bg-white dark:bg-slate-800 text-unpam-navy dark:text-amber-300 text-xs sm:text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition-colors cursor-pointer">
                    + Tambah 1 Baris
                </button>
                <button type="button"
                        onclick="addStudentRows(5)"
                        class="min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs sm:text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 shadow-xs transition-colors cursor-pointer">
                    + Tambah 5 Baris
                </button>
            </div>

            <!-- Opsi NIM Duplikat & Tombol Simpan -->
            <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-4 sm:p-5 space-y-4 shadow-xs">
                <div class="space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Jika ada NIM yang sudah terdaftar:</p>
                    <div class="flex flex-wrap gap-4 text-xs sm:text-sm text-slate-800 dark:text-slate-200">
                        <label class="inline-flex items-center gap-2 cursor-pointer min-h-[36px]">
                            <input type="radio" name="duplicate_mode" value="update" @checked(old('duplicate_mode', 'update') !== 'skip') class="text-unpam-navy">
                            <span>Perbarui data mahasiswa yang sudah ada</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer min-h-[36px]">
                            <input type="radio" name="duplicate_mode" value="skip" @checked(old('duplicate_mode') === 'skip') class="text-unpam-navy">
                            <span>Lewati NIM yang sudah ada</span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('admin.students.index') }}"
                       class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="min-h-[44px] px-5 py-2.5 rounded-xl bg-unpam-navy dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white text-sm font-bold shadow-xs transition-colors cursor-pointer">
                        Simpan Semua Mahasiswa
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- MODE 2: TEMPEL DAFTAR TEKS MASSAL -->
    <section id="panel-mode-text"
             role="tabpanel"
             aria-labelledby="tab-btn-text"
             class="{{ $activeMode === 'text' ? '' : 'hidden' }} space-y-5">
        <form action="{{ route('admin.students.bulk-store') }}" method="POST"
              class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-4 sm:p-6 space-y-5 shadow-xs">
            @csrf
            <input type="hidden" name="mode" value="text">

            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-2.5 text-xs text-slate-700 dark:text-slate-300">
                <p class="font-bold text-slate-900 dark:text-white text-sm">Panduan Format Teks (1 Mahasiswa per Baris)</p>
                <p class="leading-relaxed">
                    Anda bisa mengetik langsung atau copy-paste daftar dari WhatsApp, Notepad, Google Sheets, atau Excel (pemisah kolom otomatis mendeteksi tanda pipa <code class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700">|</code>, tab, titik koma <code class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700">;</code>, atau koma <code class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700">,</code>).
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3 space-y-1">
                        <p class="font-bold text-slate-800 dark:text-slate-200">Format Lengkap:</p>
                        <code class="block text-[11px] font-mono text-unpam-navy dark:text-amber-300 break-all">
                            NIM | Nama | Prodi | Reguler | No WA | Semester | Catatan
                        </code>
                    </div>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-3 space-y-1">
                        <p class="font-bold text-slate-800 dark:text-slate-200">Format Ringkas Minimal:</p>
                        <code class="block text-[11px] font-mono text-unpam-navy dark:text-amber-300 break-all">
                            NIM | Nama | No WA
                        </code>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <label for="bulk_text" class="block text-sm font-bold text-slate-900 dark:text-white">
                        Daftar Mahasiswa (Tempel atau Ketik di Sini) <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                            <span id="textLineCount">0</span> baris terdeteksi
                        </span>
                        <button type="button"
                                onclick="fillBulkTextExample()"
                                class="min-h-[36px] px-3 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer">
                            Isi Contoh
                        </button>
                        <button type="button"
                                onclick="clearBulkText()"
                                class="min-h-[36px] px-3 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                            Bersihkan
                        </button>
                    </div>
                </div>
                <textarea id="bulk_text"
                          name="bulk_text"
                          rows="9"
                          required
                          placeholder="211011400001 | Budi Santoso | Teknik Informatika S1 | Reguler A | 081234567801 | 5 | Aktif&#10;211011400002 | Siti Aminah | Sistem Informasi S1 | Reguler B | 081234567802 | 3&#10;211011400003 | Ahmad Fauzi | 081234567803"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs sm:text-sm text-slate-900 dark:text-white font-mono leading-relaxed focus:ring-2 focus:ring-unpam-navy">{{ old('bulk_text') }}</textarea>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">
                    Baris kosong atau diawali tanda <code class="px-1 rounded bg-slate-100 dark:bg-slate-700">#</code> dilewati otomatis. Nomor WA format 08xxx dinormalisasi otomatis ke 628xxx.
                </p>
            </div>

            <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Jika ada NIM yang sudah terdaftar:</p>
                <div class="flex flex-wrap gap-4 text-xs sm:text-sm text-slate-800 dark:text-slate-200">
                    <label class="inline-flex items-center gap-2 cursor-pointer min-h-[36px]">
                        <input type="radio" name="duplicate_mode" value="update" @checked(old('duplicate_mode', 'update') !== 'skip') class="text-unpam-navy">
                        <span>Perbarui data mahasiswa yang sudah ada</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer min-h-[36px]">
                        <input type="radio" name="duplicate_mode" value="skip" @checked(old('duplicate_mode') === 'skip') class="text-unpam-navy">
                        <span>Lewati NIM yang sudah ada</span>
                    </label>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.students.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-xs transition-colors cursor-pointer">
                    Proses &amp; Simpan Daftar Teks
                </button>
            </div>
        </form>
    </section>
</div>

<!-- Template Kartu Baris Baru -->
<template id="studentRowTemplate">
    <div class="student-row-card bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-4 sm:p-5 space-y-4 shadow-xs" data-row-index="__IDX__">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <span class="row-number-badge inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-unpam-navy dark:bg-slate-700 text-white text-xs font-extrabold">
                    Mahasiswa #<span class="row-number">__NUM__</span>
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        onclick="duplicateStudentRow(this)"
                        class="min-h-[38px] px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-xs font-semibold text-slate-800 dark:text-slate-200 transition-colors cursor-pointer">
                    Duplikat Prodi &amp; Kelas
                </button>
                <button type="button"
                        onclick="removeStudentRow(this)"
                        class="min-h-[38px] px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-xs font-bold text-red-700 dark:text-red-300 transition-colors cursor-pointer">
                    Hapus Baris
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_nim___IDX__">
                    NIM (12 Digit) <span class="text-red-600 dark:text-red-400">*</span>
                </label>
                <input type="text"
                       id="row_nim___IDX__"
                       name="rows[__IDX__][nim]"
                       maxlength="12"
                       inputmode="numeric"
                       placeholder="211011400001"
                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="lg:col-span-5">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_name___IDX__">
                    Nama Lengkap <span class="text-red-600 dark:text-red-400">*</span>
                </label>
                <input type="text"
                       id="row_name___IDX__"
                       name="rows[__IDX__][name]"
                       maxlength="150"
                       placeholder="Nama lengkap mahasiswa"
                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_wa___IDX__">
                    Nomor WhatsApp <span class="text-red-600 dark:text-red-400">*</span>
                </label>
                <input type="text"
                       id="row_wa___IDX__"
                       name="rows[__IDX__][whatsapp_number]"
                       maxlength="30"
                       placeholder="0812xxx atau 62812xxx"
                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_prodi___IDX__">
                    Program Studi
                </label>
                <select id="row_prodi___IDX__"
                        name="rows[__IDX__][study_program]"
                        class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                    @foreach($studyPrograms as $program)
                        <option value="{{ $program }}">{{ $program }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_reg___IDX__">
                    Kelas Reguler
                </label>
                <select id="row_reg___IDX__"
                        name="rows[__IDX__][regular_class]"
                        class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                    @foreach($regularClasses as $key => $label)
                        <option value="{{ $key }}" @selected($key === 'reg_a')>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_sem___IDX__">
                    Semester
                </label>
                <input type="number"
                       id="row_sem___IDX__"
                       name="rows[__IDX__][semester]"
                       min="1"
                       max="14"
                       value="1"
                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1" for="row_notes___IDX__">
                    Catatan (Opsional)
                </label>
                <input type="text"
                       id="row_notes___IDX__"
                       name="rows[__IDX__][notes]"
                       maxlength="500"
                       placeholder="Catatan tambahan"
                       class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
            <label class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-200 cursor-pointer">
                <input type="checkbox"
                       name="rows[__IDX__][is_active]"
                       value="1"
                       checked
                       class="w-4 h-4 rounded border-slate-400 dark:border-slate-600 text-unpam-navy">
                <span>Aktif menerima reminder pembayaran WhatsApp</span>
            </label>
        </div>
    </div>
</template>

<script>
    (function () {
        let nextRowIndex = {{ count($initialRows) }};

        window.switchBulkMode = function (mode) {
            const rowsPanel = document.getElementById('panel-mode-rows');
            const textPanel = document.getElementById('panel-mode-text');
            const btnRows = document.getElementById('tab-btn-rows');
            const btnText = document.getElementById('tab-btn-text');

            if (!rowsPanel || !textPanel || !btnRows || !btnText) return;

            const activeClasses = ['bg-unpam-navy', 'text-white', 'shadow-xs'];
            const inactiveClasses = ['bg-slate-50', 'dark:bg-slate-900/60', 'text-slate-700', 'dark:text-slate-300', 'hover:bg-slate-100', 'dark:hover:bg-slate-700'];

            if (mode === 'text') {
                rowsPanel.classList.add('hidden');
                textPanel.classList.remove('hidden');
                btnText.setAttribute('aria-selected', 'true');
                btnRows.setAttribute('aria-selected', 'false');
                btnText.classList.add(...activeClasses);
                btnText.classList.remove(...inactiveClasses);
                btnRows.classList.remove(...activeClasses);
                btnRows.classList.add(...inactiveClasses);
            } else {
                textPanel.classList.add('hidden');
                rowsPanel.classList.remove('hidden');
                btnRows.setAttribute('aria-selected', 'true');
                btnText.setAttribute('aria-selected', 'false');
                btnRows.classList.add(...activeClasses);
                btnRows.classList.remove(...inactiveClasses);
                btnText.classList.remove(...activeClasses);
                btnText.classList.add(...inactiveClasses);
            }
        };

        function refreshRowNumbers() {
            const container = document.getElementById('studentRowsContainer');
            if (!container) return;
            const cards = container.querySelectorAll('.student-row-card');
            cards.forEach((card, i) => {
                const numEl = card.querySelector('.row-number');
                if (numEl) numEl.textContent = String(i + 1);
            });
            const countLabel = document.getElementById('rowsCountLabel');
            if (countLabel) countLabel.textContent = String(cards.length);
        }

        window.addStudentRows = function (count) {
            const container = document.getElementById('studentRowsContainer');
            const template = document.getElementById('studentRowTemplate');
            if (!container || !template) return;

            for (let i = 0; i < count; i++) {
                const idx = nextRowIndex++;
                const html = template.innerHTML
                    .replaceAll('__IDX__', String(idx))
                    .replaceAll('__NUM__', String(container.querySelectorAll('.student-row-card').length + 1));
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const node = wrapper.firstElementChild;
                if (node) container.appendChild(node);
            }

            refreshRowNumbers();
        };

        window.duplicateStudentRow = function (btn) {
            const card = btn.closest('.student-row-card');
            const container = document.getElementById('studentRowsContainer');
            const template = document.getElementById('studentRowTemplate');
            if (!card || !container || !template) return;

            const idx = nextRowIndex++;
            const html = template.innerHTML
                .replaceAll('__IDX__', String(idx))
                .replaceAll('__NUM__', String(container.querySelectorAll('.student-row-card').length + 1));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const newCard = wrapper.firstElementChild;
            if (!newCard) return;

            const srcProdi = card.querySelector('select[name$="[study_program]"]')?.value;
            const srcReg = card.querySelector('select[name$="[regular_class]"]')?.value;
            const srcSem = card.querySelector('input[name$="[semester]"]')?.value;
            const srcNotes = card.querySelector('input[name$="[notes]"]')?.value;

            if (srcProdi) newCard.querySelector('select[name$="[study_program]"]').value = srcProdi;
            if (srcReg) newCard.querySelector('select[name$="[regular_class]"]').value = srcReg;
            if (srcSem) newCard.querySelector('input[name$="[semester]"]').value = srcSem;
            if (srcNotes) newCard.querySelector('input[name$="[notes]"]').value = srcNotes;

            card.after(newCard);
            refreshRowNumbers();
            newCard.querySelector('input[name$="[nim]"]')?.focus();
        };

        window.removeStudentRow = function (btn) {
            const container = document.getElementById('studentRowsContainer');
            const card = btn.closest('.student-row-card');
            if (!container || !card) return;

            const cards = container.querySelectorAll('.student-row-card');
            if (cards.length <= 1) {
                card.querySelectorAll('input[type="text"]').forEach(el => { el.value = ''; });
                const semInput = card.querySelector('input[name$="[semester]"]');
                if (semInput) semInput.value = '1';
                return;
            }

            card.remove();
            refreshRowNumbers();
        };

        const textArea = document.getElementById('bulk_text');
        const lineCountEl = document.getElementById('textLineCount');

        function updateTextLineCount() {
            if (!textArea || !lineCountEl) return;
            const lines = textArea.value
                .split(/\r\n|\r|\n/)
                .map(line => line.trim())
                .filter(line => line !== '' && !line.startsWith('#') && !line.startsWith('//'));
            lineCountEl.textContent = String(lines.length);
        }

        window.fillBulkTextExample = function () {
            if (!textArea) return;
            textArea.value = [
                '211011400001 | Budi Santoso | Teknik Informatika S1 | Reguler A | 081234567801 | 5 | Mahasiswa Aktif',
                '211011400002 | Siti Aminah | Sistem Informasi S1 | Reguler B | 081234567802 | 3 | Mahasiswa Malam',
                '211011400003 | Ahmad Fauzi | Manajemen S1 | Reguler CK | 6281234567803 | 7 | Kelas Kamis'
            ].join('\n');
            updateTextLineCount();
            textArea.focus();
        };

        window.clearBulkText = function () {
            if (!textArea) return;
            textArea.value = '';
            updateTextLineCount();
            textArea.focus();
        };

        if (textArea) {
            textArea.addEventListener('input', updateTextLineCount);
            updateTextLineCount();
        }
    })();
</script>
@endsection
