@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Pengingat Mahasiswa' : 'Buat Pengingat Mahasiswa') . ' - Admin UNPAM')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 dark:border-slate-700 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                    {{ $isEdit ? 'Ubah Pengingat Mahasiswa' : 'Buat Pengingat Mahasiswa' }}
                </h1>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    Target pengiriman saat ini: <strong class="text-slate-900 dark:text-white">{{ $activeStudentCount }} mahasiswa aktif</strong>.
                </p>
            </div>
            <a href="{{ route('admin.reminders.index') }}"
                class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.reminders.update', $reminder) : route('admin.reminders.store') }}"
            method="POST" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-6 space-y-5 shadow-xs">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Kartu Template Pesan Siap Pakai (Opsional) -->
            <div class="bg-amber-50/60 dark:bg-amber-950/20 border border-amber-300 dark:border-amber-700/60 rounded-xl p-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-unpam-gold text-slate-950 text-xs font-black">
                            &para;
                        </span>
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-white">Template Pesan</p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400">Pilih salah satu template di bawah untuk mengisi judul,
                                kategori, dan isi pesan secara otomatis.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 pt-1">
                    <div class="sm:col-span-8">
                        <label for="template-selector" class="sr-only">Pilih Template Pesan Pengingat</label>
                        <select id="template-selector"
                            class="w-full min-h-[40px] px-3 py-1.5 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                            <option value="">-- Pilih Template Pesan (7 Template Tersedia) --</option>
                            <option value="pembayaran">Pembayaran &amp; Keuangan: Pengingat Batas Akhir Pembayaran Kuliah
                            </option>
                            <option value="akademik_krs">Akademik &amp; KRS: Pengingat Batas Pengisian KRS</option>
                            <option value="ujian">Jadwal Ujian: Informasi Pelaksanaan Ujian Semester (UTS/UAS)</option>
                            <option value="beasiswa">Pengumuman &amp; Beasiswa: Informasi Pendaftaran Program Beasiswa
                            </option>
                            <option value="administrasi">Administrasi &amp; Berkas: Pengingat Batas Penyerahan Berkas
                            </option>
                            <option value="kegiatan">Kegiatan &amp; Seminar: Informasi Agenda Seminar &amp; Kegiatan Kampus
                            </option>
                            <option value="umum">Pengingat Umum: Pengumuman Layanan Informasi Kampus</option>
                        </select>
                    </div>
                    <div class="sm:col-span-4 flex items-center gap-2">
                        <button type="button" id="btn-apply-template"
                            class="flex-1 min-h-[40px] px-3 py-1.5 rounded-lg bg-unpam-navy text-white text-xs sm:text-sm font-bold hover:bg-slate-800 transition-colors shadow-xs">
                            Terapkan
                        </button>
                        <button type="button" id="btn-reset-template"
                            title="Kosongkan judul dan isi pesan untuk mengetik manual dari nol"
                            class="min-h-[40px] px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs sm:text-sm font-semibold transition-colors">
                            Kosongkan
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                        Judul Pengingat / Pengumuman <span class="text-red-700 dark:text-red-400">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title', $reminder->title) }}" required
                        placeholder="Contoh: Pengingat Pembayaran UKT / Batas Pengisian KRS"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                </div>

                <div>
                    <label for="category" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                        Kategori <span class="text-red-700 dark:text-red-400">*</span>
                    </label>
                    <select id="category" name="category" required
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 text-sm text-slate-900 dark:text-white bg-white dark:bg-slate-900 focus:ring-2 focus:ring-unpam-navy">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $reminder->category ?? 'pembayaran') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Target Penerima Pesan (Semua / Personal / Beberapa Nomor) -->
            <div class="border border-slate-300 dark:border-slate-700 rounded-xl p-4 bg-slate-50/80 dark:bg-slate-900/40 space-y-3">
                <div>
                    <label class="block text-sm font-bold text-slate-900 dark:text-white mb-0.5">
                        Target Penerima Pengingat <span class="text-red-700 dark:text-red-400">*</span>
                    </label>
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Tentukan apakah pengingat dikirim ke seluruh mahasiswa aktif, perorangan (personal), atau beberapa nomor tertentu.
                    </p>
                </div>

                @php
                    $currentTargetType = old('target_type', $reminder->target_type ?? 'all');
                    $personalId = old('personal_student_id', (!empty($reminder->target_student_ids) && is_array($reminder->target_student_ids) ? $reminder->target_student_ids[0] : null));
                    $selectedIds = old('selected_student_ids', (is_array($reminder->target_student_ids) ? $reminder->target_student_ids : []));
                @endphp

                <!-- Pilihan Jenis Target (Radio Cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                    <label class="target-card cursor-pointer border rounded-lg p-3 flex items-start gap-2.5 transition-all bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500">
                        <input type="radio" name="target_type" value="all" class="mt-0.5 text-unpam-navy focus:ring-unpam-navy" @checked($currentTargetType === 'all')>
                        <div>
                            <span class="block text-xs font-bold text-slate-900 dark:text-white">Semua Mahasiswa</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">Broadcast {{ $activeStudentCount }} mhs</span>
                        </div>
                    </label>

                    <label class="target-card cursor-pointer border rounded-lg p-3 flex items-start gap-2.5 transition-all bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500">
                        <input type="radio" name="target_type" value="personal" class="mt-0.5 text-unpam-navy focus:ring-unpam-navy" @checked($currentTargetType === 'personal')>
                        <div>
                            <span class="block text-xs font-bold text-slate-900 dark:text-white">Personal (1 Penerima)</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">Kirim ke 1 orang/nomor</span>
                        </div>
                    </label>

                    <label class="target-card cursor-pointer border rounded-lg p-3 flex items-start gap-2.5 transition-all bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500">
                        <input type="radio" name="target_type" value="selected" class="mt-0.5 text-unpam-navy focus:ring-unpam-navy" @checked($currentTargetType === 'selected')>
                        <div>
                            <span class="block text-xs font-bold text-slate-900 dark:text-white">Beberapa Nomor</span>
                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">Pilih mhs / daftar nomor</span>
                        </div>
                    </label>
                </div>

                <!-- Panel Input Khusus Personal -->
                <div id="panel-target-personal" class="space-y-3 pt-2 {{ $currentTargetType === 'personal' ? '' : 'hidden' }}">
                    <div>
                        <label for="personal_student_id" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                            Pilih Mahasiswa Terdaftar
                        </label>
                        <select name="personal_student_id" id="personal_student_id"
                                class="w-full min-h-[42px] px-3 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                            <option value="">-- Pilih Mahasiswa Terdaftar ({{ $activeStudentCount }} Mahasiswa) --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}" @selected($personalId == $st->id)>
                                    {{ $st->name }} (NIM: {{ $st->nim }}) &bull; +{{ $st->whatsapp_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="custom_recipient_personal" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                            Atau Masukkan Nomor WhatsApp Manual (Jika Belum Terdaftar)
                        </label>
                        <input type="text"
                               id="custom_recipient_personal"
                               name="custom_recipients_personal"
                               value="{{ $currentTargetType === 'personal' ? old('custom_recipients', $reminder->custom_recipients) : '' }}"
                               placeholder="Contoh: 08123456789 atau Rian Pratama: 08123456789"
                               class="w-full min-h-[42px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Kosongkan kolom ini jika sudah memilih mahasiswa terdaftar di atas.</p>
                    </div>
                </div>

                <!-- Panel Input Khusus Beberapa Mahasiswa / Nomor Pilihan -->
                <div id="panel-target-selected" class="space-y-3 pt-2 {{ $currentTargetType === 'selected' ? '' : 'hidden' }}">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                                Centang Mahasiswa Penerima
                            </label>
                            <input type="text"
                                   id="search-student-checkbox"
                                   placeholder="Filter nama atau NIM..."
                                   class="text-xs px-2.5 py-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 placeholder-slate-400">
                        </div>
                        <div class="max-h-48 overflow-y-auto border border-slate-300 dark:border-slate-700 rounded-lg p-2 bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800" id="student-checkbox-list">
                            @foreach($students as $st)
                                @php $isTargetChecked = in_array($st->id, (array)$selectedIds); @endphp
                                <label class="student-item flex items-center justify-between p-2 hover:bg-slate-50 dark:hover:bg-slate-800/80 rounded cursor-pointer text-xs transition-colors" data-search="{{ strtolower($st->name . ' ' . $st->nim . ' ' . $st->whatsapp_number) }}">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox"
                                               name="selected_student_ids[]"
                                               value="{{ $st->id }}"
                                               @checked($isTargetChecked)
                                               class="text-unpam-navy focus:ring-unpam-navy rounded">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $st->name }}</span>
                                        <span class="text-slate-500 dark:text-slate-400">({{ $st->nim }})</span>
                                    </div>
                                    <span class="font-mono text-slate-400 dark:text-slate-400 text-[11px]">+{{ $st->whatsapp_number }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="custom_recipients_bulk" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                            Nomor WhatsApp Tambahan Lain (Manual, Opsional)
                        </label>
                        <textarea id="custom_recipients_bulk"
                                  name="custom_recipients_bulk"
                                  rows="2"
                                  placeholder="Masukkan nomor tambahan dipisahkan baris baru atau koma, misal:&#10;081299887766, 081311223344"
                                  class="w-full px-3 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">{{ $currentTargetType === 'selected' ? old('custom_recipients', $reminder->custom_recipients) : '' }}</textarea>
                    </div>
                </div>
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Tanggal &amp; Waktu Pengiriman Terjadwal (Opsional)
                </label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                    value="{{ old('scheduled_at', $reminder->scheduled_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                    Sistem otomatis mengirim saat waktunya tiba, atau Anda dapat menekan tombol <strong>Kirim Sekarang</strong> kapan saja.
                </p>
            </div>

            <div>
                <label for="message" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Isi Pesan WhatsApp <span class="text-red-700 dark:text-red-400">*</span>
                </label>
                <textarea id="message" name="message" rows="6" required
                    placeholder="Tuliskan isi pengingat secara lengkap dan jelas..."
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">{{ old('message', $reminder->message) }}</textarea>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                    Pesan akan otomatis dilengkapi salam pembuka, nama &amp; NIM mahasiswa penerima, serta tanda tangan resmi kampus.
                </p>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.reminders.index') }}"
                    class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" name="action_submit" value="save"
                    class="min-h-[44px] px-4 py-2 rounded-xl border border-unpam-navy dark:border-amber-400 bg-white dark:bg-slate-800 text-unpam-navy dark:text-amber-300 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 shadow-xs transition-colors">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Jadwal' }}
                </button>
                <button type="submit" name="action_submit" value="send_now"
                    data-confirm="Kirim pengingat ini sekarang ke penerima WhatsApp yang dipilih?"
                    class="min-h-[44px] inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 shadow-xs transition-colors">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                    <span>Kirim Sekarang</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const templateSelector = document.getElementById('template-selector');
            const btnApply = document.getElementById('btn-apply-template');
            const btnReset = document.getElementById('btn-reset-template');
            const titleInput = document.getElementById('title');
            const categorySelect = document.getElementById('category');
            const messageInput = document.getElementById('message');

            const templates = {
                pembayaran: {
                    category: 'pembayaran',
                    title: 'Pengingat Batas Akhir Pembayaran Kuliah',
                    message: 'Halo Rekan Mahasiswa, kami mengingatkan bahwa batas akhir pembayaran biaya perkuliahan semester berjalan adalah tanggal [Tanggal Batas Pembayaran]. Silakan lakukan pembayaran melalui saluran resmi yang tersedia. Abaikan pesan ini apabila Anda telah menyelesaikan pembayaran. Terima kasih.'
                },
                akademik_krs: {
                    category: 'akademik',
                    title: 'Pengingat Batas Pengisian Kartu Rencana Studi (KRS)',
                    message: 'Halo Rekan Mahasiswa, masa pengisian dan perubahan Kartu Rencana Studi (KRS) akan berakhir pada tanggal [Tanggal Batas KRS]. Pastikan seluruh rencana mata kuliah Anda telah dipilih dan disetujui oleh Dosen Pembimbing Akademik.'
                },
                ujian: {
                    category: 'ujian',
                    title: 'Informasi Pelaksanaan Ujian Semester',
                    message: 'Halo Rekan Mahasiswa, pelaksanaan Ujian Tengah/Akhir Semester akan dimulai pada tanggal [Tanggal Mulai Ujian]. Harap periksa jadwal ujian Anda dan pastikan kartu peserta ujian telah dicetak serta persyaratan administrasi telah terpenuhi.'
                },
                beasiswa: {
                    category: 'pengumuman',
                    title: 'Informasi Pendaftaran Program Beasiswa',
                    message: 'Halo Rekan Mahasiswa, pendaftaran program beasiswa periode ini telah resmi dibuka. Bagi mahasiswa yang memenuhi kriteria, silakan memeriksa syarat, ketentuan, serta alur pendaftaran melalui website kampus atau layanan chatbot.'
                },
                administrasi: {
                    category: 'administrasi',
                    title: 'Pengingat Batas Penyerahan Berkas Administrasi',
                    message: 'Halo Rekan Mahasiswa, kami mengingatkan bagi mahasiswa yang belum melengkapi berkas administrasi agar segera menyerahkannya ke loket pelayanan atau melalui portal layanan sebelum tanggal [Tanggal Batas].'
                },
                kegiatan: {
                    category: 'kegiatan',
                    title: 'Informasi Agenda Seminar & Kegiatan Kampus',
                    message: 'Halo Rekan Mahasiswa, ikuti agenda seminar/kegiatan kampus yang akan diselenggarakan pada tanggal [Tanggal Kegiatan]. Tempat: [Lokasi/Media Daring]. Informasi dan pendaftaran dapat diakses melalui: [Link Pendaftaran].'
                },
                umum: {
                    category: 'umum',
                    title: 'Pengumuman Layanan Informasi Kampus',
                    message: 'Halo Rekan Mahasiswa, berikut informasi penting mengenai [Topik Pengumuman]. Apabila memiliki kendala atau pertanyaan lebih lanjut, silakan hubungi layanan bantuan melalui chatbot website atau WhatsApp resmi.'
                }
            };

            function applyTemplate(key) {
                if (!key || !templates[key]) return;

                const tpl = templates[key];
                const hasExistingContent = (titleInput.value.trim() !== '' || messageInput.value.trim() !== '');

                const doApply = function () {
                    titleInput.value = tpl.title;
                    if (categorySelect && tpl.category) {
                        categorySelect.value = tpl.category;
                    }
                    messageInput.value = tpl.message;
                    messageInput.focus();
                };

                if (hasExistingContent) {
                    if (typeof window.unpamConfirm === 'function') {
                        window.unpamConfirm('Ganti teks yang sudah ada dengan template terpilih?', doApply, {
                            title: 'Terapkan Template',
                            confirmText: 'Ya, Ganti Teks'
                        });
                        return;
                    } else if (!confirm('Ganti teks yang sudah ada dengan template terpilih?')) {
                        return;
                    }
                }

                doApply();
            }

            if (btnApply && templateSelector) {
                btnApply.addEventListener('click', function () {
                    const selected = templateSelector.value;
                    if (!selected) {
                        alert('Silakan pilih salah satu template pada menu pilihan terlebih dahulu.');
                        return;
                    }
                    applyTemplate(selected);
                });
            }

            if (templateSelector) {
                templateSelector.addEventListener('change', function () {
                    if (this.value) {
                        applyTemplate(this.value);
                    }
                });
            }

            if (btnReset) {
                btnReset.addEventListener('click', function () {
                    const hasContent = (titleInput.value.trim() !== '' || messageInput.value.trim() !== '');
                    const doReset = function () {
                        titleInput.value = '';
                        messageInput.value = '';
                        if (templateSelector) templateSelector.value = '';
                        titleInput.focus();
                    };

                    if (hasContent) {
                        if (typeof window.unpamConfirm === 'function') {
                            window.unpamConfirm('Kosongkan teks judul dan isi pesan?', doReset, {
                                title: 'Kosongkan Formulir',
                                confirmText: 'Ya, Kosongkan'
                            });
                            return;
                        } else if (!confirm('Kosongkan teks judul dan isi pesan?')) {
                            return;
                        }
                    }

                    doReset();
                });
            }

            // Target Type Switcher
            const targetRadios = document.querySelectorAll('input[name="target_type"]');
            const panelPersonal = document.getElementById('panel-target-personal');
            const panelSelected = document.getElementById('panel-target-selected');

            targetRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (panelPersonal) panelPersonal.classList.toggle('hidden', this.value !== 'personal');
                    if (panelSelected) panelSelected.classList.toggle('hidden', this.value !== 'selected');
                });
            });

            // Student checkbox search filter
            const searchCheckbox = document.getElementById('search-student-checkbox');
            const studentItems = document.querySelectorAll('.student-item');

            if (searchCheckbox && studentItems.length > 0) {
                searchCheckbox.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    studentItems.forEach(item => {
                        const searchData = item.getAttribute('data-search') || '';
                        if (searchData.includes(query)) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    });
                });
            }
        });
    </script>
@endsection
