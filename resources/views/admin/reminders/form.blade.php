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
              method="POST"
              class="bg-white border border-slate-300 rounded-lg p-6 space-y-4 shadow-sm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="title" class="block text-sm font-bold text-slate-900 mb-1">
                        Judul Pengingat / Pengumuman <span class="text-red-700">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $reminder->title) }}"
                           required
                           placeholder="Contoh: Pengingat Pembayaran UKT / Batas Pengisian KRS"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">
                </div>

                <div>
                    <label for="category" class="block text-sm font-bold text-slate-900 mb-1">
                        Kategori <span class="text-red-700">*</span>
                    </label>
                    <select id="category"
                            name="category"
                            required
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
                <input type="datetime-local"
                       id="scheduled_at"
                       name="scheduled_at"
                       value="{{ old('scheduled_at', $reminder->scheduled_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
                <p class="text-xs text-slate-600 mt-1">
                    Sistem otomatis mengirim saat waktunya tiba, atau Anda dapat menekan tombol <strong>Kirim Manual Sekarang</strong> kapan saja.
                </p>
            </div>

            <div>
                <label for="message" class="block text-sm font-bold text-slate-900 mb-1">
                    Isi Pesan WhatsApp <span class="text-red-700">*</span>
                </label>
                <textarea id="message"
                          name="message"
                          rows="6"
                          required
                          placeholder="Tuliskan isi pengingat secara lengkap dan jelas..."
                          class="w-full px-3.5 py-2.5 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">{{ old('message', $reminder->message) }}</textarea>
                <p class="text-xs text-slate-600 mt-1">
                    Pesan akan otomatis dilengkapi salam pembuka, nama &amp; NIM mahasiswa penerima, serta tanda tangan resmi kampus.
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
@endsection
