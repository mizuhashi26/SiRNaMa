@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Informasi Kampus' : 'Tambah Informasi Kampus') . ' - Admin CRM UNPAM')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 dark:border-slate-700 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                    {{ $isEdit ? 'Ubah Informasi Kampus' : 'Tambah Informasi Kampus Baru' }}
                </h1>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    Informasi yang dipublikasikan otomatis tersedia di Website, Chatbot Website, dan Chatbot WhatsApp.
                </p>
            </div>
            <a href="{{ route('admin.informations.index') }}"
               class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.informations.update', $information) : route('admin.informations.store') }}"
              method="POST"
              class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-6 space-y-5 shadow-xs">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="title" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Judul Informasi <span class="text-red-700 dark:text-red-400">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title', $information->title) }}"
                       required
                       placeholder="Contoh: Jadwal Ujian Akhir Semester (UAS) Ganjil"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:outline-unpam-gold">
            </div>

            <div>
                <label for="category" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Kategori Informasi <span class="text-red-700 dark:text-red-400">*</span>
                </label>
                <select id="category"
                        name="category"
                        required
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 text-sm text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:outline-unpam-gold">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $information->category) === $key)>
                            {{ $label }} {{ $key === 'pembayaran' ? '(Tampil pada Menu 2. Informasi Pembayaran Chatbot)' : '(Tampil pada Menu 1. Informasi Kampus Chatbot)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="summary" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Ringkasan Singkat (Opsional)
                </label>
                <textarea id="summary"
                          name="summary"
                          rows="2"
                          placeholder="Ringkasan 1-2 kalimat untuk kartu halaman depan dan daftar cepat chatbot..."
                          class="w-full px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:outline-unpam-gold">{{ old('summary', $information->summary) }}</textarea>
            </div>

            <div>
                <label for="content" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Isi Lengkap Informasi <span class="text-red-700 dark:text-red-400">*</span>
                </label>
                <textarea id="content"
                          name="content"
                          rows="8"
                          required
                          placeholder="Tuliskan isi pengumuman, jadwal, atau rincian prosedur secara jelas..."
                          class="w-full px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:outline-unpam-gold">{{ old('content', $information->content) }}</textarea>
            </div>

            <!-- Pengaturan Status Publikasi & Schedule Post -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white block">Status &amp; Penjadwalan Publikasi</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Pilih apakah informasi ingin langsung dipublikasikan, dijadwalkan tanggal tertentu, atau disimpan sebagai draft.</span>
                    </div>
                </div>

                @php
                    $currentStatus = 'publish';
                    if (!$information->is_published && !$isEdit) {
                        $currentStatus = 'publish';
                    } elseif (!$information->is_published) {
                        $currentStatus = 'draft';
                    } elseif ($information->published_at && $information->published_at > now()) {
                        $currentStatus = 'schedule';
                    }
                    $currentStatus = old('action_type', $currentStatus);
                @endphp

                <!-- Pilihan Mode: Publikasikan Sekarang, Jadwalkan (Schedule), atau Draft -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="cursor-pointer border rounded-xl p-3 flex items-start gap-2.5 transition-colors bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 hover:border-unpam-navy dark:hover:border-unpam-gold">
                        <input type="radio" name="action_type" value="publish" @checked($currentStatus === 'publish') onchange="toggleScheduleInput(this.value)" class="w-4 h-4 mt-0.5 text-unpam-navy">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">Publikasikan Langsung</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block">Tampil instan di Web &amp; Chatbot</span>
                        </div>
                    </label>

                    <label class="cursor-pointer border rounded-xl p-3 flex items-start gap-2.5 transition-colors bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 hover:border-unpam-navy dark:hover:border-unpam-gold">
                        <input type="radio" name="action_type" value="schedule" @checked($currentStatus === 'schedule') onchange="toggleScheduleInput(this.value)" class="w-4 h-4 mt-0.5 text-unpam-navy">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">Jadwalkan (Schedule)</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block">Rilis otomatis di tanggal &amp; jam tertentu</span>
                        </div>
                    </label>

                    <label class="cursor-pointer border rounded-xl p-3 flex items-start gap-2.5 transition-colors bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 hover:border-unpam-navy dark:hover:border-unpam-gold">
                        <input type="radio" name="action_type" value="draft" @checked($currentStatus === 'draft') onchange="toggleScheduleInput(this.value)" class="w-4 h-4 mt-0.5 text-unpam-navy">
                        <div>
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">Simpan sebagai Draft</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight block">Hanya tersimpan di admin, belum tayang</span>
                        </div>
                    </label>
                </div>

                <!-- Input Tanggal & Waktu Jadwal Publikasi (Muncul jika Schedule dipilih) -->
                <div id="schedule-datetime-container" class="{{ $currentStatus === 'schedule' ? '' : 'hidden' }} bg-slate-50 dark:bg-slate-850 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl p-4 space-y-2">
                    <label for="published_at" class="block text-xs font-bold text-slate-900 dark:text-white">
                        Tanggal &amp; Waktu Tayang Otomatis:
                    </label>
                    <input type="datetime-local"
                           id="published_at"
                           name="published_at"
                           value="{{ old('published_at', $information->published_at ? $information->published_at->format('Y-m-d\TH:i') : now()->addHours(1)->format('Y-m-d\TH:i')) }}"
                           class="min-h-[40px] px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs sm:text-sm focus:outline-unpam-gold">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Sebelum waktu tersebut tiba, informasi tidak akan muncul di portal mahasiswa maupun menu chatbot.
                    </p>
                </div>

                <!-- Sematkan sebagai Informasi Penting (Pin) -->
                <div class="flex items-start gap-2.5 pt-2">
                    <input type="checkbox"
                           id="is_pinned"
                           name="is_pinned"
                           value="1"
                           @checked(old('is_pinned', $information->is_pinned))
                           class="w-4 h-4 mt-1 rounded border-slate-400 text-unpam-navy">
                    <label for="is_pinned" class="text-sm text-slate-900 dark:text-slate-100">
                        <span class="font-bold block">Sematkan sebagai Informasi Penting (Pinned)</span>
                        <span class="text-xs text-slate-600 dark:text-slate-400">Ditampilkan dengan badge khusus di urutan teratas pada kategorinya.</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.informations.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                    Batal
                </a>
                <button type="submit"
                        id="submit-action-btn"
                        class="min-h-[44px] px-5 py-2.5 rounded-xl bg-unpam-navy text-white text-sm font-bold hover:bg-slate-800 shadow-sm">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan & Publikasikan' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleScheduleInput(val) {
            const container = document.getElementById('schedule-datetime-container');
            const submitBtn = document.getElementById('submit-action-btn');
            if (val === 'schedule') {
                container.classList.remove('hidden');
                submitBtn.textContent = 'Jadwalkan Publikasi';
            } else if (val === 'draft') {
                container.classList.add('hidden');
                submitBtn.textContent = 'Simpan sebagai Draft';
            } else {
                container.classList.add('hidden');
                submitBtn.textContent = '{{ $isEdit ? "Simpan Perubahan" : "Publikasikan Sekarang" }}';
            }
        }
    </script>
@endsection

