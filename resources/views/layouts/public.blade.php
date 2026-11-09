<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pusat Informasi & Layanan Mahasiswa - Studi Kasus UNPAM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased">
    <!-- Banner Simulasi Akademik Wajib -->
    <div class="bg-unpam-goldSoft border-b border-amber-300 text-slate-900 text-xs sm:text-sm py-2 px-4">
        <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <p class="font-medium">
                <span class="inline-block font-bold uppercase tracking-wider bg-amber-300 text-slate-950 px-2 py-0.5 rounded mr-1.5">Simulasi Akademik</span>
                Aplikasi CRM mandiri untuk tugas mata kuliah (Studi Kasus UNPAM). Bukan sistem atau pengumuman resmi Universitas Pamulang.
            </p>
            <a href="#chatbot-trigger" onclick="window.openUnpamChat && window.openUnpamChat(); return false;" class="underline font-semibold text-slate-900 hover:text-unpam-navy">
                Buka Layanan Chat Mahasiswa
            </a>
        </div>
    </div>

    <!-- Header & Navigasi Publik -->
    <header class="bg-unpam-navy text-white border-b border-slate-800 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between py-3 gap-4">
                <a href="{{ route('public.index') }}" class="flex items-center gap-3 min-h-[44px] focus-visible:outline-unpam-gold">
                    <div class="w-10 h-10 rounded bg-unpam-gold text-slate-950 font-extrabold flex items-center justify-center text-sm tracking-tight shrink-0">
                        UNPAM
                    </div>
                    <div>
                        <span class="block font-bold text-base sm:text-lg leading-tight text-white">CRM Layanan Mahasiswa</span>
                        <span class="block text-xs text-slate-200">Studi Kasus Universitas Pamulang (Simulasi)</span>
                    </div>
                </a>

                <nav aria-label="Navigasi Utama" class="hidden md:flex items-center gap-2">
                    <a href="{{ route('public.index') }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request()->routeIs('public.index') && !request('kategori') ? 'bg-unpam-gold text-slate-950 font-semibold' : 'text-white hover:bg-slate-800' }}">
                        Semua Informasi
                    </a>
                    <a href="{{ route('public.index', ['kategori' => 'akademik']) }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request('kategori') === 'akademik' ? 'bg-unpam-gold text-slate-950 font-semibold' : 'text-white hover:bg-slate-800' }}">
                        Akademik
                    </a>
                    <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request('kategori') === 'pembayaran' ? 'bg-unpam-gold text-slate-950 font-semibold' : 'text-white hover:bg-slate-800' }}">
                        Info Pembayaran
                    </a>
                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                           class="min-h-[44px] inline-flex items-center px-4 py-2 rounded text-sm font-semibold bg-unpam-gold text-slate-950 hover:bg-unpam-goldHover ml-2">
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
    </header>

    <!-- Konten Utama -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-200 border-t border-slate-800 mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 text-sm">
            <div class="space-y-1 max-w-xl">
                <p class="font-bold text-white">CRM Pengelolaan Informasi, Reminder, dan Layanan Mahasiswa</p>
                <p class="text-slate-300">
                    Studi Kasus: Universitas Pamulang (UNPAM). Aplikasi ini berdiri sendiri (standalone) dengan data simulasi untuk keperluan demonstrasi akademik.
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

