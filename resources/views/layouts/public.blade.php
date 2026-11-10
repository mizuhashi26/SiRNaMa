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
    @endphp

    <!-- Header & Navigasi Publik -->
    <header class="bg-unpam-navy text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between py-3.5 gap-4">
                <a href="{{ route('public.index') }}" class="flex items-center gap-3 min-h-[44px] focus-visible:outline-unpam-gold">
                    @if($brandLogoImage)
                        <img src="{{ $brandLogoImage }}" alt="{{ $brandLogoText }}" class="h-10 w-auto object-contain rounded bg-white p-0.5 shadow-sm" />
                    @endif
                    <div class="w-10 h-10 rounded-lg bg-unpam-gold text-slate-950 font-extrabold flex items-center justify-center text-sm tracking-tight shrink-0 shadow-sm border border-amber-300">
                        {{ $brandLogoText }}
                    </div>
                    <div>
                        <span class="block font-bold text-base sm:text-lg leading-tight text-white">Layanan Mahasiswa</span>
                        <span class="block text-xs text-slate-300">{{ $brandAppName }}</span>
                    </div>
                </a>

                <nav aria-label="Navigasi Utama" class="hidden md:flex items-center gap-2">
                    <a href="{{ route('public.index') }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request()->routeIs('public.index') && !request('kategori') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Semua Informasi
                    </a>
                    <a href="{{ route('public.index', ['kategori' => 'akademik']) }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request('kategori') === 'akademik' ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Akademik
                    </a>
                    <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request('kategori') === 'pembayaran' ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Info Pembayaran
                    </a>
                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                           class="min-h-[44px] inline-flex items-center px-4 py-2 rounded text-sm font-semibold bg-unpam-gold text-slate-950 hover:bg-unpam-goldHover ml-2 shadow-sm">
                            Dashboard Admin
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="min-h-[44px] inline-flex items-center px-4 py-2 rounded text-sm font-semibold border border-slate-400 text-white hover:bg-slate-800 ml-2">
                            Login Admin
                        </a>
                    @endauth
                </nav>

                <button type="button"
                        id="mobile-menu-toggle"
                        aria-expanded="false"
                        aria-controls="mobile-nav-panel"
                        class="md:hidden min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded border border-slate-600 px-3 text-xs font-semibold text-white hover:bg-slate-800">
                    Menu
                </button>
            </div>

            <!-- Menu Mobile Reflow -->
            <nav id="mobile-nav-panel" aria-label="Navigasi Seluler" class="hidden md:hidden pb-4 border-t border-slate-700 pt-3 space-y-1">
                <a href="{{ route('public.index') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">
                    Semua Informasi Kampus
                </a>
                <a href="{{ route('public.index', ['kategori' => 'akademik']) }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">
                    Informasi Akademik
                </a>
                <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium text-white hover:bg-slate-800">
                    Informasi Pembayaran Kuliah
                </a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-semibold bg-unpam-gold text-slate-950">
                        Ke Dashboard Admin
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-semibold bg-slate-800 text-white">
                        Login Admin CRM
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
                <p class="font-bold text-white">Portal Layanan {{ $brandAppName }} ({{ $brandLogoText }})</p>
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
            const mobileBtn = document.getElementById('mobile-menu-toggle');
            const mobilePanel = document.getElementById('mobile-nav-panel');
            if (mobileBtn && mobilePanel) {
                mobileBtn.addEventListener('click', function () {
                    const expanded = mobileBtn.getAttribute('aria-expanded') === 'true';
                    mobileBtn.setAttribute('aria-expanded', String(!expanded));
                    mobilePanel.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>

