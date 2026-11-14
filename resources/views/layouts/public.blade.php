<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SiRNaMa - Pusat Informasi & Layanan Mahasiswa UNPAM')</title>
    <meta name="description" content="@yield('meta_description', 'SiRNaMa - Sistem Informasi Reminder dan Layanan Mahasiswa berbasis Web dan WhatsApp Gateway')">
    <meta name="keywords" content="@yield('meta_keywords', 'SiRNaMa, Sistem Informasi Layanan Mahasiswa, CRM Mahasiswa, Helpdesk Kampus')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'SiRNaMa - Pusat Informasi &amp; Layanan Mahasiswa UNPAM')">
    <meta property="og:description" content="@yield('meta_description', 'SiRNaMa - Sistem Informasi Reminder dan Layanan Mahasiswa berbasis Web dan WhatsApp Gateway')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="SiRNaMa">
    <meta property="og:locale" content="id_ID">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
    @php
        $brandLogoText = \App\Models\AppSetting::getValue('app_logo_text', 'UNPAM');
        $brandLogoImage = \App\Models\AppSetting::getValue('app_logo_image', null);
        $brandAppName = \App\Models\AppSetting::getValue('app_institution_name', 'Universitas Pamulang');
        $brandFooterText = \App\Models\AppSetting::getValue('app_footer_note', 'Universitas Pamulang. Hak cipta dilindungi undang-undang.');
        $themePrimary = \App\Models\AppSetting::getValue('theme_primary_color', '#0F294A');
        $themeAccent = \App\Models\AppSetting::getValue('theme_accent_color', '#EAB308');
    @endphp

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

        /* Dark mode overrides untuk elemen background navy agar mengikuti mode gelap pekat */
        .dark .bg-unpam-navy { background-color: #0b1322 !important; }
        .dark .text-unpam-navy { color: #facc15 !important; }
        .dark .border-unpam-navy { border-color: #1e293b !important; }

        .bg-unpam-gold { background-color: var(--color-accent) !important; }
        .text-unpam-gold { color: var(--color-accent) !important; }
        .border-unpam-gold { border-color: var(--color-accent) !important; }
    </style>

    <!-- Header & Navigasi Publik (Compact & Horizontal) -->
    <header class="bg-unpam-navy dark:bg-slate-950 text-white sticky top-0 z-30 shadow-md border-b dark:border-slate-800">
        <div class="w-full max-w-7xl 2xl:max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">
            <div class="flex items-center justify-between py-1.5 sm:py-2 gap-3">
                <a href="{{ route('public.index') }}" class="flex items-center gap-2.5 min-h-[38px] shrink-0 focus-visible:outline-unpam-gold">
                    @php($resolvedLogoUrl = \App\Models\AppSetting::resolveLogoUrl($brandLogoImage))
                    @if($resolvedLogoUrl)
                        <img src="{{ $resolvedLogoUrl }}" alt="Logo Kampus" class="h-8 sm:h-9 w-auto max-w-[100px] object-contain bg-transparent shrink-0" />
                    @else
                        <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Kampus" class="h-8 sm:h-9 w-8 sm:w-9 object-contain bg-transparent shrink-0 drop-shadow" />
                    @endif
                    <div class="leading-tight">
                        <span class="block font-bold text-sm sm:text-base leading-tight text-white whitespace-nowrap">SiRNaMa</span>
                        <span class="block text-[10px] text-slate-300 hidden sm:block whitespace-nowrap">{{ $brandAppName }}</span>
                    </div>
                </a>

                <!-- Navigasi Tautan, Mode Malam & Hamburger Toggle -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <!-- Kontainer Navigasi Tautan Desktop dengan Transisi Collapse/Expand Halus -->
                    <nav id="desktop-nav-links"
                         aria-label="Navigasi Utama"
                         class="hidden md:flex items-center gap-1 overflow-hidden transition-all duration-300 ease-in-out max-w-[850px] opacity-100">
                        <a href="{{ route('public.index') }}"
                           class="min-h-[34px] inline-flex items-center px-2.5 py-1 rounded text-xs sm:text-[13px] font-medium whitespace-nowrap {{ request()->routeIs('public.index') && !request('kategori') ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Semua Informasi
                        </a>
                        <a href="{{ route('public.index', ['kategori' => 'akademik']) }}"
                           class="min-h-[34px] inline-flex items-center px-2.5 py-1 rounded text-xs sm:text-[13px] font-medium whitespace-nowrap {{ request('kategori') === 'akademik' ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Akademik
                        </a>
                        <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}"
                           class="min-h-[34px] inline-flex items-center px-2.5 py-1 rounded text-xs sm:text-[13px] font-medium whitespace-nowrap {{ request('kategori') === 'pembayaran' ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Info Pembayaran
                        </a>
                        <a href="{{ route('public.about') }}"
                           class="min-h-[34px] inline-flex items-center px-2.5 py-1 rounded text-xs sm:text-[13px] font-medium whitespace-nowrap {{ request()->routeIs('public.about') ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Tentang
                        </a>
                        <a href="{{ route('public.contact') }}"
                           class="min-h-[34px] inline-flex items-center px-2.5 py-1 rounded text-xs sm:text-[13px] font-medium whitespace-nowrap {{ request()->routeIs('public.contact') ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Kontak
                        </a>
                        @auth
                            <a href="{{ route('admin.dashboard') }}"
                               class="min-h-[34px] inline-flex items-center px-2.5 py-1 rounded text-xs sm:text-[13px] font-semibold bg-unpam-gold text-slate-950 hover:bg-unpam-goldHover ml-1 shadow-xs whitespace-nowrap">
                                Dashboard Admin
                            </a>
                        @endauth
                    </nav>

                    <!-- Tombol Alihkan Mode Malam / Terang -->
                    <button type="button"
                            id="theme-toggle-btn"
                            aria-label="Alihkan mode malam / terang"
                            title="Mode Malam / Terang"
                            class="min-h-[34px] min-w-[34px] inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-600/80 bg-slate-800/80 hover:bg-slate-700 text-amber-300 hover:text-white transition-all shadow-xs focus-visible:outline-unpam-gold">
                        <!-- Ikon Matahari (Aktif saat dark) -->
                        <svg class="w-4 h-4 hidden dark:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Ikon Bulan (Aktif saat light) -->
                        <svg class="w-4 h-4 inline-block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Tombol Hamburger / Toggle Menu di Ujung Kanan Navbar (Desktop & Mobile) -->
                    <button type="button"
                            id="navbar-hamburger-toggle"
                            aria-expanded="true"
                            aria-controls="desktop-nav-links mobile-nav-panel"
                            class="min-h-[34px] min-w-[34px] inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-600/80 bg-slate-800/80 hover:bg-slate-700 text-amber-300 hover:text-white transition-all shadow-xs focus-visible:outline-unpam-gold"
                            title="Buka / Tutup Menu Navigasi">
                        <span class="sr-only">Toggle Menu Navigasi</span>
                        <!-- Ikon Hamburger 3 Garis dengan Animasi State -->
                        <svg id="hamburger-icon" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path class="line-top transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16"/>
                            <path class="line-mid transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 12h16"/>
                            <path class="line-bot transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu Mobile Reflow dengan Animasi Halus -->
            <nav id="mobile-nav-panel" aria-label="Navigasi Seluler" class="hidden md:hidden pb-3 border-t border-slate-700/80 pt-2 space-y-1 transition-all duration-300">
                <a href="{{ route('public.index') }}" class="block min-h-[40px] px-3 py-2 rounded text-xs sm:text-sm font-medium text-white hover:bg-slate-800 {{ request()->routeIs('public.index') && !request('kategori') ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Semua Informasi Kampus
                </a>
                <a href="{{ route('public.index', ['kategori' => 'akademik']) }}" class="block min-h-[40px] px-3 py-2 rounded text-xs sm:text-sm font-medium text-white hover:bg-slate-800 {{ request('kategori') === 'akademik' ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Informasi Akademik
                </a>
                <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}" class="block min-h-[40px] px-3 py-2 rounded text-xs sm:text-sm font-medium text-white hover:bg-slate-800 {{ request('kategori') === 'pembayaran' ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Informasi Pembayaran Kuliah
                </a>
                <a href="{{ route('public.about') }}" class="block min-h-[40px] px-3 py-2 rounded text-xs sm:text-sm font-medium text-white hover:bg-slate-800 {{ request()->routeIs('public.about') ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Tentang Layanan
                </a>
                <a href="{{ route('public.contact') }}" class="block min-h-[40px] px-3 py-2 rounded text-xs sm:text-sm font-medium text-white hover:bg-slate-800 {{ request()->routeIs('public.contact') ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Kontak &amp; WhatsApp Bot
                </a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block min-h-[40px] px-3 py-2 rounded text-xs sm:text-sm font-semibold bg-unpam-gold text-slate-950 mt-1">
                        Ke Dashboard Admin
                    </a>
                @endauth
            </nav>
        </div>
        <!-- Garis Pemisah Tegas Antara Header dan Konten Halaman (Aksen Gold UNPAM) -->
        <div class="h-0.5 bg-gradient-to-r from-amber-400 via-unpam-gold to-amber-500 w-full" role="presentation"></div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer id="public-main-footer" class="bg-slate-900 text-slate-200 border-t border-slate-800 mt-12">
        <div class="w-full max-w-7xl 2xl:max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12 py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 text-sm">
            <div class="space-y-1 max-w-xl">
                <p class="font-bold text-white">SiRNaMa &bull; {{ $brandAppName }}</p>
                <p class="text-slate-300">
                    {{ $brandFooterText }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('public.index') }}" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded text-slate-200 hover:text-white underline">
                    Beranda Informasi
                </a>
                <a href="{{ route('public.about') }}" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded text-slate-200 hover:text-white underline">
                    Tentang Layanan
                </a>
                <a href="{{ route('public.contact') }}" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded text-slate-200 hover:text-white underline">
                    Kontak &amp; Lokasi
                </a>
                <a href="{{ route('login') }}" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded text-slate-200 hover:text-white underline">
                    Portal Admin
                </a>
            </div>
        </div>
    </footer>

    <!-- Widget Chatbot Website Berbasis Menu -->
    @include('public.partials.chatbot-widget')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Theme toggle mode malam / terang
            const themeBtn = document.getElementById('theme-toggle-btn');
            if (themeBtn) {
                themeBtn.addEventListener('click', function () {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                });
            }

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

                // Sinkronkan status bila ukuran layar di-resize
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

