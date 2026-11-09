<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - CRM Layanan Mahasiswa UNPAM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 flex flex-col justify-between antialiased">
    <header class="bg-unpam-navy text-white py-3 px-4 border-b border-slate-800">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <a href="{{ route('public.index') }}" class="min-h-[44px] inline-flex items-center gap-2.5 font-bold text-sm sm:text-base text-white">
                <span class="px-2 py-1 rounded bg-unpam-gold text-slate-950 font-extrabold text-xs">UNPAM</span>
                <span>CRM Layanan Mahasiswa (Simulasi)</span>
            </a>
            <a href="{{ route('public.index') }}" class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-slate-200 hover:text-white underline">
                Ke Halaman Publik
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white border border-slate-300 rounded-lg shadow-sm p-6 sm:p-8 space-y-6">
            <div class="space-y-1.5 border-b border-slate-200 pb-4">
                <span class="inline-block text-xs font-bold uppercase tracking-wider bg-amber-100 text-slate-900 border border-amber-300 px-2.5 py-0.5 rounded">
                    Portal Pengelola
                </span>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">
                    Login Dashboard Admin CRM
                </h1>
                <p class="text-xs sm:text-sm text-slate-700">
                    Masuk untuk mengelola informasi kampus, reminder pembayaran umum, dan percakapan mahasiswa.
                </p>
            </div>

            @if(session('success'))
                <div role="status" class="bg-emerald-50 border border-emerald-300 text-emerald-950 px-3.5 py-2.5 rounded text-xs sm:text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert" class="bg-red-50 border border-red-300 text-red-950 px-3.5 py-2.5 rounded text-xs sm:text-sm space-y-1">
                    <p class="font-bold">Autentikasi Gagal:</p>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-900 mb-1">
                        Alamat Email Admin
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', 'admin@unpam-simulasi.ac.id') }}"
                           required
                           autocomplete="email"
                           placeholder="admin@unpam-simulasi.ac.id"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 bg-white text-slate-900 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-900 mb-1">
                        Kata Sandi
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="Masukkan kata sandi admin"
                           class="w-full min-h-[44px] px-3.5 py-2 rounded border border-slate-400 bg-white text-slate-900 text-sm">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox"
                           id="remember"
                           name="remember"
                           value="1"
                           class="w-4 h-4 rounded border-slate-400 text-unpam-navy">
                    <label for="remember" class="text-sm text-slate-800">
                        Ingat sesi login saya di perangkat ini
                    </label>
                </div>

                <button type="submit"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded bg-unpam-navy text-white font-bold text-sm hover:bg-unpam-navyDark">
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="bg-slate-50 border border-slate-200 rounded p-3 text-xs text-slate-700 space-y-1">
                <p class="font-bold text-slate-900">Akun Demo Default (Seeder):</p>
                <p>Email: <code class="font-mono bg-white px-1 py-0.5 border border-slate-200 rounded">admin@unpam-simulasi.ac.id</code></p>
                <p>Password: <code class="font-mono bg-white px-1 py-0.5 border border-slate-200 rounded">password123</code></p>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-600">
        Simulasi Akademik CRM Layanan Mahasiswa - Studi Kasus UNPAM
    </footer>
</body>
</html>

