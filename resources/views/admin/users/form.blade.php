@extends('layouts.admin')

@section('title', ($isEdit ? 'Ubah Akun Admin' : 'Tambah Akun Admin') . ' - Admin UNPAM')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 dark:border-slate-700 pb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">
                    {{ $isEdit ? 'Ubah Akun Admin' : 'Tambah Akun Admin' }}
                </h1>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    Nama ini tampil di Live Chat sebagai penanda siapa yang membalas mahasiswa.
                </p>
            </div>
            <a href="{{ route('admin.users.index') }}"
               class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('admin.users.update', $adminUser) : route('admin.users.store') }}"
              method="POST"
              autocomplete="off"
              class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-6 space-y-4 shadow-xs">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Nama Admin <span class="text-red-700 dark:text-red-400">*</span>
                </label>
                <input type="text" id="name" name="name" required maxlength="100"
                       value="{{ old('name', $adminUser->name) }}"
                       placeholder="Contoh: Admin A"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                    Email Login <span class="text-red-700 dark:text-red-400">*</span>
                </label>
                <input type="email" id="email" name="email" required maxlength="150"
                       value="{{ old('email', $adminUser->email) }}"
                       class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                        Kata Sandi @if(! $isEdit)<span class="text-red-700 dark:text-red-400">*</span>@endif
                    </label>
                    <input type="password" id="password" name="password" minlength="8"
                           @if(! $isEdit) required @endif
                           autocomplete="new-password"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                        {{ $isEdit ? 'Kosongkan jika tidak ingin mengganti.' : 'Minimal 8 karakter.' }}
                    </p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-slate-900 dark:text-white mb-1">
                        Ulangi Kata Sandi
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                           autocomplete="new-password"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-unpam-navy">
                </div>
            </div>

            <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 p-3.5 text-xs text-slate-700 dark:text-slate-300">
                <strong class="text-slate-900 dark:text-white">Hak akses:</strong>
                {{ $isEdit && $adminUser->isSuperAdmin()
                    ? 'Super Admin (semua menu, termasuk Mahasiswa, Pengaturan, dan Kelola Admin).'
                    : 'Admin (Ringkasan Tugas, Informasi Kampus, Pengingat Mahasiswa, dan Live Chat).' }}
            </div>

            <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="min-h-[44px] px-5 py-2 rounded-xl bg-unpam-navy dark:bg-slate-700 text-white text-sm font-bold hover:bg-slate-800 dark:hover:bg-slate-600 shadow-xs transition-colors">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Buat Akun Admin' }}
                </button>
            </div>
        </form>
    </div>
@endsection
