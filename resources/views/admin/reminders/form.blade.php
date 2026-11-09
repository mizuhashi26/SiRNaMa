@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Pengingat Pembayaran' : 'Buat Pengingat Pembayaran') . ' - Admin UNPAM')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    {{ $isEdit ? 'Ubah Pengingat Pembayaran' : 'Buat Pengingat Pembayaran' }}
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
              class="bg-white border border-slate-300 rounded-lg p-6 space-y-4">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="title" class="block text-sm font-bold text-slate-900 mb-1">
                    Judul Pengingat Pembayaran <span class="text-red-700">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title', $reminder->title) }}"
                       required
                       placeholder="Contoh: Pengingat Pembayaran Registrasi & UKT Semester Ganjil"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-bold text-slate-900 mb-1">
                    Tanggal & Waktu Pengiriman Terjadwal (Opsional)
                </label>
                <input type="datetime-local"
                       id="scheduled_at"
                       name="scheduled_at"
                       value="{{ old('scheduled_at', $reminder->scheduled_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">
                <p class="text-xs text-slate-600 mt-1">
                    Anda tetap dapat mengirim reminder kapan saja melalui tombol <strong>Kirim Manual</strong>. Untuk pengiriman otomatis sesuai jadwal, jalankan <code class="font-mono bg-slate-100 px-1 rounded">php artisan schedule:work</code> di terminal lokal.
                </p>
            </div>

            <div>
                <label for="message" class="block text-sm font-bold text-slate-900 mb-1">
                    Isi Pesan Reminder Umum <span class="text-red-700">*</span>
                </label>
                <textarea id="message"
                          name="message"
                          rows="6"
                          required
                          placeholder="Tuliskan informasi batas waktu pembayaran umum, tata cara pembayaran, atau imbauan akademik..."
                          class="w-full px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900">{{ old('message', $reminder->message) }}</textarea>
                <p class="text-xs text-slate-600 mt-1">
                    Sistem otomatis menambahkan sapaan nama/NIM mahasiswa pada pesan pengingat.
                </p>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.reminders.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded border border-slate-300 text-sm font-semibold text-slate-800 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Jadwal Reminder' }}
                </button>
            </div>
        </form>
    </div>
@endsection

