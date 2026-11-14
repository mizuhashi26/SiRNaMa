@extends('layouts.admin')

@section('title', 'Data Mahasiswa - Dashboard Admin UNPAM')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman & Aksi Utama -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Data Mahasiswa</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                Kelola data mahasiswa penerima pesan reminder WhatsApp dan layanan chatbot informasi.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button"
                    onclick="document.getElementById('importModal').classList.remove('hidden')"
                    class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Import Excel / CSV
            </button>
            <a href="{{ route('admin.students.bulk-create') }}"
               class="min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-unpam-gold hover:bg-amber-400 text-slate-950 text-xs sm:text-sm font-extrabold shadow-xs border border-amber-300 transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>+ Tambah Massal</span>
            </a>
            <a href="{{ route('admin.students.create') }}"
               class="min-h-[44px] inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-unpam-navy dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white text-xs sm:text-sm font-bold shadow-xs transition-colors">
                <span>+ Tambah Mahasiswa</span>
            </a>
            @if($students->total() > 0)
                <button type="button"
                        onclick="confirmDeleteAll()"
                        class="min-h-[44px] inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 text-xs sm:text-sm font-bold transition-colors"
                        title="Hapus seluruh data mahasiswa dari database">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Semua
                </button>
            @endif
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <form action="{{ route('admin.students.index') }}" method="GET" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label for="q" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Cari NIM, Nama, atau No WhatsApp</label>
            <input type="search"
                   id="q"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Ketik 12 digit NIM, nama mahasiswa, atau nomor WA..."
                   class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-unpam-navy">
        </div>
        <div class="sm:w-48">
            <label for="regular_class" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Kelas Reguler</label>
            <select id="regular_class"
                    name="regular_class"
                    class="w-full min-h-[44px] px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-sm text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:ring-2 focus:ring-unpam-navy">
                <option value="">Semua Reguler</option>
                @foreach($regularClasses as $key => $label)
                    <option value="{{ $key }}" @selected($regularClass === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-48">
            <label for="status" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Status Reminder WA</label>
            <select id="status"
                    name="status"
                    class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-sm text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:ring-2 focus:ring-unpam-navy">
                <option value="">Semua Status</option>
                <option value="active" @selected($status === 'active')>Aktif Menerima</option>
                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="min-h-[44px] px-5 py-2 rounded-lg bg-unpam-navy dark:bg-slate-700 text-white text-sm font-bold hover:bg-slate-800 dark:hover:bg-slate-600 transition-colors shadow-xs">
                Cari Data
            </button>
            @if($search !== '' || $status !== '' || $regularClass !== '')
                <a href="{{ route('admin.students.index') }}" class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Form Pembungkus Bulk Delete -->
    <form id="bulkDeleteForm" action="{{ route('admin.students.bulk-delete') }}" method="POST">
        @csrf

        <!-- Bar Aksi Hapus Beberapa Terpilih (Muncul jika ada checkbox dipilih) -->
        <div id="bulkActionBar" class="hidden bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 rounded-xl p-3.5 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                    <span id="selectedCount">0</span> mahasiswa dipilih
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="unselectAllCheckboxes()"
                        class="min-h-[38px] px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                    Batalkan Pilihan
                </button>
                <button type="submit"
                        data-confirm="Yakin ingin menghapus data mahasiswa yang dipilih? Tindakan ini tidak dapat dibatalkan."
                        class="min-h-[38px] px-4 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Terpilih
                </button>
            </div>
        </div>

        @if($students->isEmpty())
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 sm:p-10 text-center space-y-4 shadow-xs">
                <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Data Mahasiswa</h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto">
                        Tambahkan data mahasiswa secara manual (satuan atau massal tanpa file) atau upload file Excel / CSV berisi data mahasiswa untuk pengiriman reminder.
                    </p>
                </div>
                <div class="flex flex-wrap justify-center gap-2.5 pt-2">
                    <button type="button"
                            onclick="document.getElementById('importModal').classList.remove('hidden')"
                            class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs sm:text-sm font-bold hover:bg-emerald-700 shadow-xs cursor-pointer">
                        Upload Excel / CSV
                    </button>
                    <a href="{{ route('admin.students.bulk-create') }}"
                       class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl bg-unpam-gold hover:bg-amber-400 text-slate-950 text-xs sm:text-sm font-extrabold border border-amber-300 shadow-xs transition-colors">
                        + Tambah Massal
                    </a>
                    <a href="{{ route('admin.students.create') }}"
                       class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl bg-unpam-navy dark:bg-slate-700 text-white text-xs sm:text-sm font-bold hover:bg-slate-800 dark:hover:bg-slate-600 shadow-xs">
                        + Tambah Manual
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/70 text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider">
                                <th class="py-3.5 px-4 w-12 text-center">
                                    <input type="checkbox"
                                           id="selectAllCheckbox"
                                           class="rounded border-slate-300 text-unpam-navy focus:ring-unpam-navy cursor-pointer w-4 h-4"
                                           title="Pilih Semua di Halaman Ini">
                                </th>
                                <th class="py-3.5 px-4 font-bold">NIM (12 Digit)</th>
                                <th class="py-3.5 px-4 font-bold min-w-[160px]">Nama Mahasiswa</th>
                                <th class="py-3.5 px-4 font-bold min-w-[160px]">Program Studi</th>
                                <th class="py-3.5 px-4 font-bold">Reguler</th>
                                <th class="py-3.5 px-4 font-bold">WhatsApp</th>
                                <th class="py-3.5 px-4 font-bold">Status</th>
                                <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($students as $student)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition-colors">
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="checkbox"
                                               name="selected_ids[]"
                                               value="{{ $student->id }}"
                                               class="student-checkbox rounded border-slate-300 text-unpam-navy focus:ring-unpam-navy cursor-pointer w-4 h-4">
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs font-bold text-slate-900 dark:text-white">
                                        {{ $student->nim }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-900 dark:text-white block">{{ $student->name }}</span>
                                        @if($student->notes)
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $student->notes }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                                        <span class="font-medium text-slate-900 dark:text-white block">{{ $student->study_program }}</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Semester {{ $student->semester }}</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600 whitespace-nowrap">
                                            {{ $student->regular_class_label }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-slate-800 dark:text-slate-200">
                                        +{{ $student->whatsapp_number }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($student->is_active)
                                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-900 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.students.edit', $student) }}"
                                               class="min-h-[38px] inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors">
                                                Ubah
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan {{ $students->firstItem() ?? 0 }} - {{ $students->lastItem() ?? 0 }} dari total {{ $students->total() }} data mahasiswa.
                </p>
                <div>
                    {{ $students->links() }}
                </div>
            </div>
        @endif
    </form>
</div>

<!-- Form Hapus Semua Mahasiswa (Tersembunyi) -->
<form id="deleteAllForm" action="{{ route('admin.students.delete-all') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Upload Excel / CSV -->
<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 space-y-5 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-start justify-between gap-3 border-b border-slate-200 dark:border-slate-700 pb-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div class="min-w-0">
                    <h2 id="modalTitle" class="text-base font-bold text-slate-900 dark:text-white leading-snug">Upload Data Mahasiswa Sekaligus</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Dukungan format file: Excel (.xlsx, .xls) atau CSV (.csv)</p>
                </div>
            </div>
            <button type="button"
                    onclick="document.getElementById('importModal').classList.add('hidden')"
                    aria-label="Tutup modal impor"
                    class="min-w-[36px] min-h-[36px] rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-lg shrink-0 cursor-pointer">
                &times;
            </button>
        </div>

        <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="import_file" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    Pilih File Spreadsheet Excel / CSV <span class="text-red-500">*</span>
                </label>
                <input type="file"
                       id="import_file"
                       name="file"
                       required
                       accept=".xlsx,.xls,.csv,.txt"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-200 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Ukuran berkas maksimal 5MB.</p>
            </div>

            <!-- Petunjuk Kolom Header -->
            <div class="bg-slate-50 dark:bg-slate-900/60 rounded-xl p-3.5 border border-slate-200 dark:border-slate-700 text-xs space-y-2 text-slate-600 dark:text-slate-400">
                <p class="font-bold text-slate-800 dark:text-slate-200">Ketentuan Format Kolom:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>nim</strong>: Wajib 12 digit angka (contoh: <code class="bg-slate-200 dark:bg-slate-700 px-1 rounded">211011400001</code>).</li>
                    <li><strong>nama</strong>: Nama lengkap mahasiswa.</li>
                    <li><strong>jurusan</strong>: Program studi (disesuaikan otomatis).</li>
                    <li><strong>nomor_wa</strong>: Format diawali 62 atau 08 (contoh: <code class="bg-slate-200 dark:bg-slate-700 px-1 rounded">628123456789</code>).</li>
                    <li><strong>semester</strong>: Angka 1 - 14 (opsional, default 1).</li>
                </ul>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">Butuh contoh format?</span>
                    <a href="{{ route('admin.students.template') }}" class="text-emerald-700 dark:text-emerald-400 font-bold hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh Template CSV
                    </a>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2.5 pt-2">
                <button type="button"
                        onclick="document.getElementById('importModal').classList.add('hidden')"
                        class="min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-semibold text-xs sm:text-sm">
                    Batal
                </button>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-xs inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Unggah &amp; Proses Impor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.student-checkbox');
        const bulkBar = document.getElementById('bulkActionBar');
        const countSpan = document.getElementById('selectedCount');

        function updateSelection() {
            let checkedCount = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) checkedCount++;
            });

            if (countSpan) countSpan.textContent = checkedCount;

            if (bulkBar) {
                if (checkedCount > 0) {
                    bulkBar.classList.remove('hidden');
                } else {
                    bulkBar.classList.add('hidden');
                }
            }

            if (selectAll) {
                selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateSelection();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelection);
        });

        window.unselectAllCheckboxes = function () {
            if (selectAll) selectAll.checked = false;
            checkboxes.forEach(cb => { cb.checked = false; });
            updateSelection();
        };

        window.confirmDeleteAll = function () {
            const msg = 'PERINGATAN: Apakah Anda benar-benar yakin ingin MENGHAPUS SEMUA data mahasiswa di database? Seluruh nomor penerima reminder akan dikosongkan.';
            if (typeof window.unpamConfirm === 'function') {
                window.unpamConfirm(msg, function () {
                    const form = document.getElementById('deleteAllForm');
                    if (form) form.submit();
                }, { title: 'Peringatan Hapus Seluruh Mahasiswa', confirmText: 'Ya, Hapus Semua' });
            } else if (confirm(msg)) {
                const form = document.getElementById('deleteAllForm');
                if (form) form.submit();
            }
        };

        const importModal = document.getElementById('importModal');
        if (importModal) {
            importModal.addEventListener('click', function (ev) {
                if (ev.target === importModal) importModal.classList.add('hidden');
            });
            document.addEventListener('keydown', function (ev) {
                if (ev.key === 'Escape' && !importModal.classList.contains('hidden')) {
                    importModal.classList.add('hidden');
                }
            });
        }
    });
</script>
@endsection
