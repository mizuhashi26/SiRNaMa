<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - SiRNaMa UNPAM</title>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col justify-between antialiased transition-colors">
    @php
        $brandLogoImage = \App\Models\AppSetting::getValue('app_logo_image', null);
        $brandAppName = \App\Models\AppSetting::getValue('app_institution_name', 'Universitas Pamulang');
        $themePrimary = \App\Models\AppSetting::getValue('theme_primary_color', '#0F294A');
        $themeAccent = \App\Models\AppSetting::getValue('theme_accent_color', '#EAB308');
    @endphp

    <style>
        :root {
            --color-primary:
                {{ $themePrimary }}
            ;
            --color-accent:
                {{ $themeAccent }}
            ;
        }

        .bg-unpam-navy {
            background-color: var(--color-primary) !important;
        }

        .text-unpam-navy {
            color: var(--color-primary) !important;
        }

        .border-unpam-navy {
            border-color: var(--color-primary) !important;
        }

        .focus\:ring-unpam-navy:focus {
            --tw-ring-color: var(--color-primary) !important;
        }

        .focus\:border-unpam-navy:focus {
            border-color: var(--color-primary) !important;
        }

        .bg-unpam-gold {
            background-color: var(--color-accent) !important;
        }

        .text-unpam-gold {
            color: var(--color-accent) !important;
        }

        .border-unpam-gold {
            border-color: var(--color-accent) !important;
        }
    </style>
    <header class="bg-unpam-navy text-white py-2.5 px-4 border-b border-slate-800">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <a href="{{ route('public.index') }}"
                class="min-h-[44px] inline-flex items-center gap-3 font-bold text-sm sm:text-base text-white">
                @if($brandLogoImage && file_exists(public_path($brandLogoImage)))
                    <img src="{{ asset($brandLogoImage) }}" alt="Logo Kampus"
                        class="h-11 sm:h-12 w-auto max-w-[110px] object-contain bg-transparent shrink-0" />
                @else
                    <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Kampus"
                        class="h-11 sm:h-12 w-11 sm:w-12 object-contain bg-transparent shrink-0 drop-shadow" />
                @endif
                <div>
                    <span class="block font-bold leading-tight">SiRNaMa</span>
                    <span class="block text-xs font-normal text-slate-300">{{ $brandAppName }}</span>
                </div>
            </a>
            <div class="flex items-center gap-2.5">
                <button type="button" id="theme-toggle" aria-label="Toggle tema gelap"
                    class="min-h-[40px] min-w-[40px] inline-flex items-center justify-center p-2 rounded-xl border border-slate-700 bg-slate-800 text-amber-300 hover:bg-slate-700 transition-colors shadow-2xs">
                    <svg class="w-4 h-4 hidden dark:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="w-4 h-4 inline-block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
                <a href="{{ route('public.index') }}"
                    class="min-h-[44px] inline-flex items-center text-xs sm:text-sm text-slate-200 hover:text-white underline">
                    Ke Halaman Publik
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-4">
        <div
            class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6">
            <div class="space-y-1.5 border-b border-slate-200 dark:border-slate-800 pb-4">
                <span
                    class="inline-block text-xs font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-2.5 py-0.5 rounded">
                    Portal Pengelola
                </span>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">
                    Login Dashboard Admin
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                    Masuk untuk mengelola informasi kampus, pengingat pembayaran, dan percakapan mahasiswa.
                </p>
            </div>

            @if(session('success'))
                <div role="status"
                    class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 text-emerald-950 dark:text-emerald-200 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div role="alert"
                    class="bg-red-50 dark:bg-red-950/60 border border-red-300 dark:border-red-700 text-red-950 dark:text-red-200 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm space-y-1">
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
                    <label for="email" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">
                        Alamat Email Admin
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        placeholder="admin@unpam.ac.id"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-unpam-navy text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">
                        Kata Sandi
                    </label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                        placeholder="Masukkan kata sandi admin"
                        class="w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-unpam-navy text-sm">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="remember" name="remember" value="1"
                        class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-unpam-navy focus:ring-unpam-navy">
                    <label for="remember" class="text-sm text-slate-700 dark:text-slate-300">
                        Ingat sesi login saya di perangkat ini
                    </label>
                </div>

                <button type="submit" id="btn-login-submit"
                    class="w-full min-h-[44px] px-4 py-2.5 rounded-xl bg-unpam-navy text-white font-bold text-sm hover:opacity-95 transition-all shadow-xs flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                    <svg id="login-spinner" class="hidden w-4 h-4 animate-spin text-white" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span id="login-btn-text">Masuk ke Dashboard</span>
                </button>
            </form>
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-500 dark:text-slate-400">
        Portal Informasi & Layanan Mahasiswa Universitas Pamulang
    </footer>

    <script>
        document.getElementById('theme-toggle')?.addEventListener('click', function () {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        const loginForm = document.querySelector('form');
        const btnSubmit = document.getElementById('btn-login-submit');
        const spinner = document.getElementById('login-spinner');
        const btnText = document.getElementById('login-btn-text');

        if (loginForm && btnSubmit) {
            loginForm.addEventListener('submit', function () {
                btnSubmit.disabled = true;
                if (spinner) spinner.classList.remove('hidden');
                if (btnText) btnText.textContent = 'Memverifikasi Akun...';
            });
        }
    </script>
</body>

</html>