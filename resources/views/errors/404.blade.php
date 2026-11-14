@php
    $brandLogoText = \App\Models\AppSetting::getValue('app_logo_text', 'UNPAM');
    $brandLogoImage = \App\Models\AppSetting::getValue('app_logo_image', null);
    $brandAppName = \App\Models\AppSetting::getValue('app_institution_name', 'Universitas Pamulang');
    $brandFooterText = \App\Models\AppSetting::getValue('app_footer_note', 'Universitas Pamulang. Hak cipta dilindungi undang-undang.');
    $themePrimary = \App\Models\AppSetting::getValue('theme_primary_color', '#0F294A');
    $themeAccent = \App\Models\AppSetting::getValue('theme_accent_color', '#EAB308');

    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $baseUrl = ($scriptDir !== '' && $scriptDir !== '.') ? $scriptDir : '';
    $homeUrl = $baseUrl !== '' ? $baseUrl . '/' : url('/');
    $aboutUrl = $baseUrl !== '' ? $baseUrl . '/tentang' : url('/tentang');
    $contactUrl = $baseUrl !== '' ? $baseUrl . '/kontak' : url('/kontak');
    $loginUrl = $baseUrl !== '' ? $baseUrl . '/login' : url('/login');
    $academicUrl = $homeUrl . (str_contains($homeUrl, '?') ? '&' : '?') . 'kategori=akademik';
    $paymentUrl = $homeUrl . (str_contains($homeUrl, '?') ? '&' : '?') . 'kategori=pembayaran';
    $adminUrl = $baseUrl !== '' ? $baseUrl . '/admin' : url('/admin');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 Halaman Tidak Ditemukan - SiRNaMa {{ $brandAppName }}</title>
    <meta name="robots" content="noindex, nofollow">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
</head>

<body
    class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-unpam-gold selection:text-slate-950 transition-colors">

    <!-- Header & Navigasi -->
    <header class="bg-unpam-navy text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between py-3 gap-4">
                <a href="{{ $homeUrl }}"
                    class="flex items-center gap-3.5 min-h-[44px] focus-visible:outline-unpam-gold">
                    @php($resolvedLogoUrl = \App\Models\AppSetting::resolveLogoUrl($brandLogoImage))
                    @if($resolvedLogoUrl)
                        <img src="{{ $resolvedLogoUrl }}" alt="Logo Kampus"
                            class="h-12 sm:h-14 w-auto max-w-[120px] object-contain bg-transparent shrink-0" />
                    @else
                        <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Kampus"
                            class="h-12 sm:h-14 w-12 sm:w-14 object-contain bg-transparent shrink-0 drop-shadow" />
                    @endif
                    <div>
                        <span class="block font-bold text-base sm:text-lg leading-tight text-white">SiRNaMa</span>
                        <span class="block text-xs text-slate-300">{{ $brandAppName }}</span>
                    </div>
                </a>

                <!-- Navigasi Tautan & Toggle -->
                <div class="flex items-center gap-2">
                    <nav id="desktop-nav-links" aria-label="Navigasi Utama"
                        class="hidden md:flex items-center gap-2 overflow-hidden transition-all duration-300 ease-in-out max-w-[700px] opacity-100">
                        <a href="{{ $homeUrl }}"
                            class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Semua Informasi
                        </a>
                        <a href="{{ $academicUrl }}"
                            class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Akademik
                        </a>
                        <a href="{{ $paymentUrl }}"
                            class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Info Pembayaran
                        </a>
                        <a href="{{ $aboutUrl }}"
                            class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Tentang
                        </a>
                        <a href="{{ $contactUrl }}"
                            class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Kontak
                        </a>
                        @auth
                            <a href="{{ $adminUrl }}"
                                class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-semibold bg-unpam-gold text-slate-950 hover:bg-unpam-goldHover ml-1 shadow-xs whitespace-nowrap transition-colors">
                                Dashboard Admin
                            </a>
                        @endauth
                    </nav>

                    <!-- Tombol Toggle Tema Gelap / Terang -->
                    <button type="button" id="theme-toggle-404" aria-label="Toggle tema gelap"
                        class="min-h-[42px] min-w-[42px] inline-flex items-center justify-center p-2 rounded-lg border border-slate-700 bg-slate-800 text-amber-300 hover:bg-slate-700 transition-colors shadow-2xs">
                        <svg class="w-4 h-4 hidden dark:inline-block" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg class="w-4 h-4 inline-block dark:hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Tombol Hamburger (Mobile) -->
                    <button type="button" id="navbar-hamburger-toggle" aria-expanded="false"
                        class="md:hidden min-h-[42px] min-w-[42px] inline-flex items-center justify-center p-2 rounded-lg border border-slate-700 bg-slate-800 text-amber-300 hover:text-white transition-all shadow-xs">
                        <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Panel Navigasi Mobile -->
            <nav id="mobile-nav-panel" class="hidden md:hidden pb-4 border-t border-slate-700/80 pt-3 space-y-1">
                <a href="{{ $homeUrl }}"
                    class="block px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">Semua
                    Informasi</a>
                <a href="{{ $academicUrl }}"
                    class="block px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">Akademik</a>
                <a href="{{ $paymentUrl }}"
                    class="block px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">Info
                    Pembayaran</a>
                <a href="{{ $aboutUrl }}"
                    class="block px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">Tentang</a>
                <a href="{{ $contactUrl }}"
                    class="block px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">Kontak</a>
            </nav>
        </div>
        <div class="h-0.5 bg-unpam-gold w-full" role="presentation"></div>
    </header>

    <!-- Konten Utama 404 -->
    <main class="flex-1 flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-xl mx-auto text-center space-y-6">
            <!-- Badge Status -->
            <div
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-300 text-xs font-bold uppercase tracking-wider shadow-xs">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Error 404 &bull; Halaman Tidak Ditemukan
            </div>

            <!-- Ilustrasi Tipografi 404 -->
            <div class="relative">
                <p
                    class="text-7xl sm:text-9xl font-black text-slate-200 dark:text-slate-800 tracking-tighter select-none font-mono">
                    404
                </p>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span
                        class="text-xl sm:text-2xl font-extrabold text-unpam-navy dark:text-amber-400 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xs px-4 py-1 rounded-xl border border-slate-300 dark:border-slate-700 shadow-xs">
                        Halaman Tidak Tersedia
                    </span>
                </div>
            </div>

            <!-- Penjelasan Pesan -->
            <div class="space-y-2 max-w-md mx-auto">
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
                    Tautan yang Anda tuju tidak dapat ditemukan
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Alamat URL mungkin salah ketik, telah dihapus, atau sedang dipindahkan. Silakan kembali ke beranda
                    atau gunakan menu navigasi di atas.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $homeUrl }}"
                    class="min-h-[44px] inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-unpam-navy dark:bg-slate-800 text-white text-xs sm:text-sm font-bold hover:bg-slate-800 dark:hover:bg-slate-700 border border-transparent dark:border-slate-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Ke Beranda Informasi</span>
                </a>
                <button type="button"
                    onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ $homeUrl }}'"
                    class="min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali Sebelumnya</span>
                </button>
            </div>

            <!-- Kartu Navigasi Cepat -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800">
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Tautan
                    Populer</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                    <a href="{{ $academicUrl }}"
                        class="p-3.5 rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-unpam-navy/50 dark:hover:border-slate-600 shadow-2xs transition-all group">
                        <p
                            class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-unpam-navy dark:group-hover:text-amber-400 flex items-center justify-between">
                            <span>Informasi Akademik</span>
                            <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </p>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">Jadwal kuliah, ujian susulan,
                            dan kalender akademik UNPAM.</p>
                    </a>
                    <a href="{{ $paymentUrl }}"
                        class="p-3.5 rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-unpam-navy/50 dark:hover:border-slate-600 shadow-2xs transition-all group">
                        <p
                            class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-unpam-navy dark:group-hover:text-amber-400 flex items-center justify-between">
                            <span>Informasi Pembayaran</span>
                            <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </p>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">Batas waktu tagihan, dispensasi
                            biaya, dan ketentuan cicilan.</p>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 py-6 text-xs text-center">
        <div class="max-w-6xl mx-auto px-4 space-y-1">
            <p class="font-bold text-white">Portal Layanan {{ $brandAppName }}</p>
            <p class="text-slate-400">{{ $brandFooterText }}</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Theme toggle
            document.getElementById('theme-toggle-404')?.addEventListener('click', function () {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });

            // Mobile toggle
            const toggleBtn = document.getElementById('navbar-hamburger-toggle');
            const mobilePanel = document.getElementById('mobile-nav-panel');
            if (toggleBtn && mobilePanel) {
                toggleBtn.addEventListener('click', function () {
                    mobilePanel.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>

</html>