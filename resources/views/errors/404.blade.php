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
    <title>404 Halaman Tidak Ditemukan - {{ $brandAppName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary: {{ $themePrimary }};
            --color-accent: {{ $themeAccent }};
        }
        .bg-unpam-navy { background-color: var(--color-primary) !important; }
        .text-unpam-navy { color: var(--color-primary) !important; }
        .border-unpam-navy { border-color: var(--color-primary) !important; }
        .focus\:ring-unpam-navy:focus { --tw-ring-color: var(--color-primary) !important; }
        .focus\:border-unpam-navy:focus { border-color: var(--color-primary) !important; }

        .bg-unpam-gold { background-color: var(--color-accent) !important; }
        .text-unpam-gold { color: var(--color-accent) !important; }
        .border-unpam-gold { border-color: var(--color-accent) !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased selection:bg-unpam-gold selection:text-slate-950">

    <!-- Header & Navigasi -->
    <header class="bg-unpam-navy text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between py-3 gap-4">
                <a href="{{ $homeUrl }}" class="flex items-center gap-3.5 min-h-[44px] focus-visible:outline-unpam-gold">
                    @if($brandLogoImage && file_exists(public_path($brandLogoImage)))
                        <img src="{{ asset($brandLogoImage) }}" alt="Logo Kampus" class="h-12 sm:h-14 w-auto max-w-[120px] object-contain bg-transparent shrink-0" />
                    @else
                        <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Kampus" class="h-12 sm:h-14 w-12 sm:w-14 object-contain bg-transparent shrink-0 drop-shadow" />
                    @endif
                    <div>
                        <span class="block font-bold text-base sm:text-lg leading-tight text-white">Layanan Mahasiswa</span>
                        <span class="block text-xs text-slate-300">{{ $brandAppName }}</span>
                    </div>
                </a>

                <!-- Navigasi Tautan & Hamburger Toggle -->
                <div class="flex items-center gap-2">
                    <!-- Kontainer Navigasi Desktop dengan Transisi Collapse/Expand Halus -->
                    <nav id="desktop-nav-links"
                         aria-label="Navigasi Utama"
                         class="hidden md:flex items-center gap-2 overflow-hidden transition-all duration-300 ease-in-out max-w-[700px] opacity-100">
                        <a href="{{ $homeUrl }}"
                           class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Semua Informasi
                        </a>
                        <a href="{{ $academicUrl }}"
                           class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Akademik
                        </a>
                        <a href="{{ $paymentUrl }}"
                           class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium whitespace-nowrap text-slate-200 hover:bg-slate-800 hover:text-white transition-colors">
                            Info Pembayaran
                        </a>
                        @auth
                            <a href="{{ $adminUrl }}"
                               class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-semibold bg-unpam-gold text-slate-950 hover:bg-unpam-goldHover ml-1 shadow-xs whitespace-nowrap transition-colors">
                                Dashboard Admin
                            </a>
                        @endauth
                    </nav>

                    <!-- Tombol Hamburger / Toggle Menu di Ujung Kanan Navbar (Desktop & Mobile) -->
                    <button type="button"
                            id="navbar-hamburger-toggle"
                            aria-expanded="true"
                            aria-controls="desktop-nav-links mobile-nav-panel"
                            class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center p-2 rounded-lg border border-slate-600/80 bg-slate-800/80 hover:bg-slate-700 text-amber-300 hover:text-white transition-all shadow-xs focus-visible:outline-unpam-gold"
                            title="Buka / Tutup Menu Navigasi">
                        <span class="sr-only">Toggle Menu Navigasi</span>
                        <svg id="hamburger-icon" class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path class="line-top transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16"/>
                            <path class="line-mid transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 12h16"/>
                            <path class="line-bot transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Panel Navigasi Mobile -->
            <nav id="mobile-nav-panel" aria-label="Navigasi Seluler" class="hidden md:hidden pb-4 border-t border-slate-700/80 pt-3 space-y-1 transition-all duration-300">
                <a href="{{ $homeUrl }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">
                    Semua Informasi Kampus
                </a>
                <a href="{{ $academicUrl }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">
                    Informasi Akademik
                </a>
                <a href="{{ $paymentUrl }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">
                    Informasi Pembayaran Kuliah
                </a>
                @auth
                    <a href="{{ $adminUrl }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-semibold bg-unpam-gold text-slate-950 mt-1">
                        Ke Dashboard Admin
                    </a>
                @endauth
            </nav>
        </div>

        <!-- Garis Aksen Emas UNPAM -->
        <div class="h-0.5 bg-unpam-gold w-full" role="presentation"></div>
    </header>

    <!-- Konten Utama 404 -->
    <main class="flex-1 flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-xl mx-auto text-center space-y-6">
            <!-- Badge Status -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-900 text-xs font-bold uppercase tracking-wider shadow-xs">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Error 404 &bull; Halaman Tidak Ditemukan
            </div>

            <!-- Ilustrasi / Tipografi 404 -->
            <div class="relative">
                <p class="text-7xl sm:text-9xl font-black text-slate-200 tracking-tighter select-none font-mono">
                    404
                </p>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-xl sm:text-2xl font-extrabold text-unpam-navy bg-white/90 backdrop-blur-xs px-4 py-1 rounded-lg border border-slate-200 shadow-xs">
                        Halaman Tidak Tersedia
                    </span>
                </div>
            </div>

            <!-- Penjelasan Pesan -->
            <div class="space-y-2 max-w-md mx-auto">
                <h1 class="text-lg sm:text-xl font-bold text-slate-900">
                    Tautan yang Anda tuju tidak dapat ditemukan
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Alamat URL mungkin salah ketik, telah dihapus, atau sedang dipindahkan. Silakan kembali ke beranda atau gunakan menu navigasi di atas.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $homeUrl }}"
                   class="min-h-[44px] inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-unpam-navy text-white text-xs sm:text-sm font-bold hover:bg-slate-800 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Ke Beranda Informasi</span>
                </a>
                <button type="button"
                        onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ $homeUrl }}'"
                        class="min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-xs sm:text-sm font-semibold text-slate-800 hover:bg-slate-100 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali Sebelumnya</span>
                </button>
                @auth
                    <a href="{{ $adminUrl }}"
                       class="min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-amber-300 bg-amber-50 text-xs sm:text-sm font-bold text-amber-900 hover:bg-amber-100 transition-colors">
                        Dashboard Admin &rarr;
                    </a>
                @endauth
            </div>

            <!-- Kartu Navigasi Cepat -->
            <div class="pt-6 border-t border-slate-200">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Tautan Populer</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                    <a href="{{ $academicUrl }}" class="p-3 rounded-lg border border-slate-200 bg-white hover:border-unpam-navy/40 hover:shadow-xs transition-all group">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-unpam-navy flex items-center justify-between">
                            <span>Informasi Akademik</span>
                            <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Jadwal kuliah, ujian susulan, dan kalender akademik UNPAM.</p>
                    </a>
                    <a href="{{ $paymentUrl }}" class="p-3 rounded-lg border border-slate-200 bg-white hover:border-unpam-navy/40 hover:shadow-xs transition-all group">
                        <p class="text-xs font-bold text-slate-900 group-hover:text-unpam-navy flex items-center justify-between">
                            <span>Informasi Pembayaran</span>
                            <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Batas waktu tagihan, dispensasi biaya, dan ketentuan cicilan.</p>
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
            const toggleBtn = document.getElementById('navbar-hamburger-toggle');
            const desktopLinks = document.getElementById('desktop-nav-links');
            const mobilePanel = document.getElementById('mobile-nav-panel');
            const hamburgerIcon = document.getElementById('hamburger-icon');

            if (toggleBtn) {
                let isDesktopExpanded = true;
                let isMobileExpanded = false;

                function updateHamburgerVisual(isExpanded) {
                    if (isExpanded) {
                        toggleBtn.classList.remove('bg-amber-400', 'text-slate-950');
                        toggleBtn.classList.add('bg-slate-800/80', 'text-amber-300');
                        if (hamburgerIcon) hamburgerIcon.classList.remove('rotate-90');
                    } else {
                        toggleBtn.classList.add('bg-amber-400', 'text-slate-950');
                        toggleBtn.classList.remove('bg-slate-800/80', 'text-amber-300');
                        if (hamburgerIcon) hamburgerIcon.classList.add('rotate-90');
                    }
                }

                toggleBtn.addEventListener('click', function () {
                    const isDesktop = window.innerWidth >= 768;

                    if (isDesktop) {
                        isDesktopExpanded = !isDesktopExpanded;
                        toggleBtn.setAttribute('aria-expanded', String(isDesktopExpanded));

                        if (isDesktopExpanded) {
                            desktopLinks.classList.remove('max-w-0', 'opacity-0', 'pointer-events-none');
                            desktopLinks.classList.add('max-w-[700px]', 'opacity-100');
                            updateHamburgerVisual(true);
                        } else {
                            desktopLinks.classList.remove('max-w-[700px]', 'opacity-100');
                            desktopLinks.classList.add('max-w-0', 'opacity-0', 'pointer-events-none');
                            updateHamburgerVisual(false);
                        }
                    } else {
                        isMobileExpanded = !isMobileExpanded;
                        toggleBtn.setAttribute('aria-expanded', String(isMobileExpanded));

                        if (isMobileExpanded) {
                            mobilePanel.classList.remove('hidden');
                            updateHamburgerVisual(false);
                        } else {
                            mobilePanel.classList.add('hidden');
                            updateHamburgerVisual(true);
                        }
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 768) {
                        if (mobilePanel) mobilePanel.classList.add('hidden');
                        isMobileExpanded = false;
                        updateHamburgerVisual(isDesktopExpanded);
                    } else {
                        updateHamburgerVisual(!isMobileExpanded);
                    }
                });
            }
        });
    </script>
</body>
</html>
