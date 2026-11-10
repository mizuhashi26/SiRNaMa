@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Akun Admin' : 'Tambah Akun Admin') . ' - Admin UNPAM')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">
                    {{ $isEdit ? 'Ubah Akun Admin' : 'Tambah Akun Admin' }}
                </h1>
                <p class="text-sm text-slate-700">
                    Nama ini tampil di Live Chat sebagai penanda siapa yang membalas mahasiswa.
                </p>
            </div>
            <a href="{{ route('admin.users.index') }}"
               class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded border border-slate-300 bg-white text-sm font-semibold text-slate-800 hover:bg-slate-100">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.users.update', $adminUser) : route('admin.users.store') }}"
              method="POST"
              autocomplete="off"
              class="bg-white border border-slate-300 rounded-lg p-6 space-y-4 shadow-sm">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-bold text-slate-900 mb-1">
                    Nama Admin <span class="text-red-700">*</span>
                </label>
                <input type="text" id="name" name="name" required maxlength="100"
                       value="{{ old('name', $adminUser->name) }}"
                       placeholder="Contoh: Admin A"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-slate-900 mb-1">
                    Email Login <span class="text-red-700">*</span>
                </label>
                <input type="email" id="email" name="email" required maxlength="150"
                       value="{{ old('email', $adminUser->email) }}"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-bold text-slate-900 mb-1">
                        Kata Sandi @if(! $isEdit)<span class="text-red-700">*</span>@endif
                    </label>
                    <input type="password" id="password" name="password" minlength="8"
                           @if(! $isEdit) required @endif
                           autocomplete="new-password"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">
                    <p class="text-xs text-slate-600 mt-1">
                        {{ $isEdit ? 'Kosongkan jika tidak ingin mengganti.' : 'Minimal 8 karakter.' }}
                    </p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-slate-900 mb-1">
                        Ulangi Kata Sandi
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                           autocomplete="new-password"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 text-sm text-slate-900 focus:ring-2 focus:ring-unpam-navy">
                </div>
            </div>

            <div class="rounded bg-slate-50 border border-slate-200 p-3 text-xs text-slate-700">
                <strong>Hak akses:</strong>
                {{ $isEdit && $adminUser->isSuperAdmin()
                    ? 'Super Admin (semua menu, termasuk Mahasiswa, Pengaturan, dan Kelola Admin).'
                    : 'Admin (Ringkasan Tugas, Informasi Kampus, Pengingat Mahasiswa, dan Live Chat).' }}
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-slate-800 shadow-sm">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Buat Akun Admin' }}
                </button>
            </div>
        </form>
    </div>
@endsection

