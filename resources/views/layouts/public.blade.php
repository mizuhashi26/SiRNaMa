<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pusat Informasi & Layanan Mahasiswa UNPAM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased">
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

        .bg-unpam-gold { background-color: var(--color-accent) !important; }
        .text-unpam-gold { color: var(--color-accent) !important; }
        .border-unpam-gold { border-color: var(--color-accent) !important; }
    </style>

    <!-- Header & Navigasi Publik -->
    <header class="bg-unpam-navy text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between py-3 gap-4">
                <a href="{{ route('public.index') }}" class="flex items-center gap-3.5 min-h-[44px] focus-visible:outline-unpam-gold">
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
                    <!-- Kontainer Navigasi Tautan Desktop dengan Transisi Collapse/Expand Halus -->
                    <nav id="desktop-nav-links"
                         aria-label="Navigasi Utama"
                         class="hidden md:flex items-center gap-2 overflow-hidden transition-all duration-300 ease-in-out max-w-[700px] opacity-100">
                        <a href="{{ route('public.index') }}"
                           class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium whitespace-nowrap {{ request()->routeIs('public.index') && !request('kategori') ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Semua Informasi
                        </a>
                        <a href="{{ route('public.index', ['kategori' => 'akademik']) }}"
                           class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium whitespace-nowrap {{ request('kategori') === 'akademik' ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Akademik
                        </a>
                        <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}"
                           class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium whitespace-nowrap {{ request('kategori') === 'pembayaran' ? 'bg-unpam-gold text-slate-950 font-bold shadow-xs' : 'text-slate-200 hover:bg-slate-800 hover:text-white transition-colors' }}">
                            Info Pembayaran
                        </a>
                        @auth
                            <a href="{{ route('admin.dashboard') }}"
                               class="min-h-[42px] inline-flex items-center px-3.5 py-2 rounded text-sm font-semibold bg-unpam-gold text-slate-950 hover:bg-unpam-goldHover ml-1 shadow-xs whitespace-nowrap">
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
                        <!-- Ikon Hamburger 3 Garis dengan Animasi State -->
                        <svg id="hamburger-icon" class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path class="line-top transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16"/>
                            <path class="line-mid transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 12h16"/>
                            <path class="line-bot transition-all duration-300 origin-center" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu Mobile Reflow dengan Animasi Halus -->
            <nav id="mobile-nav-panel" aria-label="Navigasi Seluler" class="hidden md:hidden pb-4 border-t border-slate-700/80 pt-3 space-y-1 transition-all duration-300">
                <a href="{{ route('public.index') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800 {{ request()->routeIs('public.index') && !request('kategori') ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Semua Informasi Kampus
                </a>
                <a href="{{ route('public.index', ['kategori' => 'akademik']) }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800 {{ request('kategori') === 'akademik' ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Informasi Akademik
                </a>
                <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800 {{ request('kategori') === 'pembayaran' ? 'bg-slate-800/90 text-amber-300 font-bold border-l-4 border-unpam-gold' : '' }}">
                    Informasi Pembayaran Kuliah
                </a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-semibold bg-unpam-gold text-slate-950 mt-1">
                        Ke Dashboard Admin
                    </a>
                @endauth
            </nav>
        </div>
        <!-- Garis Pemisah Tegas Antara Header dan Konten Halaman (Aksen Gold UNPAM) -->
        <div class="h-1 bg-gradient-to-r from-amber-400 via-unpam-gold to-amber-500 w-full" role="presentation"></div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-200 border-t border-slate-800 mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 text-sm">
            <div class="space-y-1 max-w-xl">
                <p class="font-bold text-white">Portal Layanan {{ $brandAppName }}</p>
                <p class="text-slate-300">
                    {{ $brandFooterText }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('public.index') }}" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded text-slate-200 hover:text-white underline">
                    Beranda Informasi
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

