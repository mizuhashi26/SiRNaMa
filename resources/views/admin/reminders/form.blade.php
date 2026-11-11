@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Pengingat Mahasiswa' : 'Buat Pengingat Mahasiswa') . ' - Admin UNPAM')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    {{ $isEdit ? 'Ubah Pengingat Mahasiswa' : 'Buat Pengingat Mahasiswa' }}
                </h1>
                <p class="text-sm text-slate-700">
                    Target pengiriman saat ini: <strong>{{ $activeStudentCount }} mahasiswa aktif</strong>.
                </p>
            </div>
            <a href="{{ route('admin.reminders.index') }}"
                class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded border border-slate-300 bg-white text-sm font-semibold text-slate-800 hover:bg-slate-100">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.reminders.update', $reminder) : route('admin.reminders.store') }}"
            method="POST" class="bg-white border border-slate-300 rounded-lg p-6 space-y-5 shadow-sm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Kartu Template Pesan Siap Pakai (Opsional) -->
            <div class="bg-amber-50/60 border border-amber-300 rounded-xl p-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-unpam-gold text-slate-950 text-xs font-black">
                            &para;
                        </span>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Template Pesan</p>
                            <p class="text-[11px] text-slate-600">Pilih salah satu template di bawah untuk mengisi judul,
                                kategori, dan isi pesan secara otomatis.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 pt-1">
                    <div class="sm:col-span-8">
                        <label for="template-selector" class="sr-only">Pilih Template Pesan Pengingat</label>
                        <select id="template-selector"
                            class="w-full min-h-[40px] px-3 py-1.5 rounded-lg border border-slate-400 bg-white text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">
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
                            class="min-h-[40px] px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 text-xs sm:text-sm font-semibold transition-colors">
                            Kosongkan
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-bold text-slate-900 mb-1">
                        Judul Pengingat / Pengumuman <span class="text-red-700">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title', $reminder->title) }}" required
                        placeholder="Contoh: Pengingat Pembayaran UKT / Batas Pengisian KRS"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">
                </div>

                <div>
                    <label for="category" class="block text-sm font-bold text-slate-900 mb-1">
                        Kategori <span class="text-red-700">*</span>
                    </label>
                    <select id="category" name="category" required
                        class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 bg-white focus:ring-2 focus:ring-unpam-navy">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $reminder->category ?? 'pembayaran') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-bold text-slate-900 mb-1">
                    Tanggal &amp; Waktu Pengiriman Terjadwal (Opsional)
                </label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                    value="{{ old('scheduled_at', $reminder->scheduled_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
                <p class="text-xs text-slate-600 mt-1">
                    Sistem otomatis mengirim saat waktunya tiba, atau Anda dapat menekan tombol <strong>Kirim Manual
                        Sekarang</strong> kapan saja.
                </p>
            </div>

            <div>
                <label for="message" class="block text-sm font-bold text-slate-900 mb-1">
                    Isi Pesan WhatsApp <span class="text-red-700">*</span>
                </label>
                <textarea id="message" name="message" rows="6" required
                    placeholder="Tuliskan isi pengingat secara lengkap dan jelas..."
                    class="w-full px-3.5 py-2.5 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">{{ old('message', $reminder->message) }}</textarea>
                <p class="text-xs text-slate-600 mt-1">
                    Pesan akan otomatis dilengkapi salam pembuka, nama &amp; NIM mahasiswa penerima, serta tanda tangan
                    resmi kampus.
                </p>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.reminders.index') }}"
                    class="min-h-[44px] inline-flex items-center px-4 py-2 rounded border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit"
                    class="min-h-[44px] px-5 py-2 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-slate-800 shadow-sm">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan & Jadwalkan' }}
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

                if (hasExistingContent) {
                    const confirmChange = confirm('Ganti teks yang sudah ada dengan template terpilih?');
                    if (!confirmChange) return;
                }

                titleInput.value = tpl.title;
                if (categorySelect && tpl.category) {
                    categorySelect.value = tpl.category;
                }
                messageInput.value = tpl.message;
                messageInput.focus();
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
                    if (hasContent) {
                        const confirmReset = confirm('Kosongkan teks judul dan isi pesan?');
                        if (!confirmReset) return;
                    }
                    titleInput.value = '';
                    messageInput.value = '';
                    if (templateSelector) templateSelector.value = '';
                    titleInput.focus();
                });
            }
        });
    </script>
@endsection