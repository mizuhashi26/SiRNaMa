<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin - CRM Layanan Mahasiswa UNPAM')</title>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 flex flex-col antialiased transition-colors duration-200">
    @php
        $waitingAdminCount = \App\Models\Conversation::where('status', \App\Models\Conversation::STATUS_WAITING_ADMIN)->count();
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

    <!-- Top Navigation Bar Admin (Compact & Horizontal) -->
    <header class="bg-unpam-navy text-white sticky top-0 z-30 shadow-md border-b border-slate-700/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between py-1.5 sm:py-2 gap-3">
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('admin.dashboard') }}"
                        class="min-h-[38px] inline-flex items-center gap-2.5 focus-visible:outline-unpam-gold">
                        @if($brandLogoImage && file_exists(public_path($brandLogoImage)))
                            <img src="{{ asset($brandLogoImage) }}" alt="Logo Kampus"
                                class="h-8 sm:h-9 w-auto max-w-[100px] object-contain bg-transparent shrink-0" />
                        @else
                            <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Kampus"
                                class="h-8 sm:h-9 w-8 sm:w-9 object-contain bg-transparent shrink-0 drop-shadow" />
                        @endif
                        <div class="leading-tight">
                            <span class="block font-bold text-xs sm:text-sm text-white whitespace-nowrap">Admin Layanan</span>
                            <span class="block text-[10px] text-slate-300 hidden sm:block whitespace-nowrap">{{ $brandAppName }}</span>
                        </div>
                    </a>
                </div>

                <!-- Navigasi Desktop Berdesain Modern & Compact Horizontal -->
                <nav aria-label="Navigasi Dashboard Admin"
                    class="hidden lg:flex items-center gap-0.5 bg-slate-900/50 p-1 rounded-lg border border-slate-700/60 shadow-xs">
                    <a href="{{ route('admin.dashboard') }}"
                        class="min-h-[32px] sm:min-h-[34px] inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-200 hover:bg-slate-800/80 hover:text-white' }}">
                        Ringkasan
                    </a>
                    <a href="{{ route('admin.informations.index') }}"
                        class="min-h-[32px] sm:min-h-[34px] inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.informations.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-200 hover:bg-slate-800/80 hover:text-white' }}">
                        Informasi
                    </a>
                    <a href="{{ route('admin.reminders.index') }}"
                        class="min-h-[32px] sm:min-h-[34px] inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.reminders.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-200 hover:bg-slate-800/80 hover:text-white' }}">
                        Pengingat
                    </a>
                    <a href="{{ route('admin.live-chat.index') }}"
                        class="min-h-[32px] sm:min-h-[34px] inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.live-chat.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-200 hover:bg-slate-800/80 hover:text-white' }}">
                        <span>Live Chat</span>
                        @if($waitingAdminCount > 0)
                            <span
                                class="inline-flex items-center justify-center px-1.5 py-0.2 rounded-full text-[10px] font-black {{ request()->routeIs('admin.live-chat.*') ? 'bg-slate-950 text-white' : 'bg-amber-400 text-slate-950 animate-pulse' }}">
                                {{ $waitingAdminCount }}
                            </span>
                        @endif
                    </a>

                    @if(auth()->user()->isSuperAdmin())
                        <span class="w-px h-3.5 bg-slate-700 mx-0.5"></span>
                        <a href="{{ route('admin.students.index') }}"
                            class="min-h-[32px] sm:min-h-[34px] inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.students.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-200 hover:bg-slate-800/80 hover:text-white' }}">
                            Mahasiswa
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                            class="min-h-[32px] sm:min-h-[34px] inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.users.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-200 hover:bg-slate-800/80 hover:text-white' }}">
                            Kelola Admin
                        </a>
                        <a href="{{ route('admin.settings.index') }}"
                            class="min-h-[32px] sm:min-h-[34px] inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-200 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Pengaturan</span>
                        </a>
                    @endif
                </nav>

                <!-- Profile Chip, Mode Malam & Aksi Kanan -->
                <div class="hidden lg:flex items-center gap-2 shrink-0">
                    <!-- Tombol Alihkan Mode Malam / Terang -->
                    <button type="button"
                        id="admin-theme-toggle"
                        aria-label="Alihkan mode malam / terang"
                        title="Mode Malam / Terang"
                        class="min-h-[34px] min-w-[34px] inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-600/80 bg-slate-800/70 hover:bg-slate-700 text-amber-300 hover:text-white transition-all shadow-xs">
                        <svg class="w-4 h-4 hidden dark:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg class="w-4 h-4 inline-block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <a href="{{ route('public.index') }}" target="_blank" rel="noopener"
                        class="min-h-[34px] inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-slate-600/80 bg-slate-800/40 hover:bg-slate-800 text-xs font-semibold text-slate-200 hover:text-white transition-colors whitespace-nowrap"
                        title="Buka Halaman Website Publik">
                        <svg class="w-3.5 h-3.5 text-unpam-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Web Publik</span>
                    </a>

                    <!-- User Identity Badge -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-700/80">
                        <div
                            class="w-7 h-7 rounded-full bg-unpam-gold text-slate-950 font-black text-xs flex items-center justify-center shadow-xs uppercase shrink-0">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="text-left leading-tight hidden xl:block">
                            <span
                                class="block text-xs font-bold text-white max-w-[110px] truncate">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <span
                                class="inline-block text-[9px] font-extrabold uppercase px-1 rounded {{ auth()->user()->isSuperAdmin() ? 'bg-amber-400 text-slate-950' : 'bg-slate-700 text-slate-200' }}">
                                {{ auth()->user()->isSuperAdmin() ? 'Superadmin' : 'Admin' }}
                            </span>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="min-h-[34px] px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-red-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-600 transition-colors flex items-center gap-1 whitespace-nowrap"
                                title="Keluar dari sesi admin">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tombol Menu Mobile & Toggle Mode Malam Mobile -->
                <div class="lg:hidden flex items-center gap-2">
                    <button type="button"
                        id="admin-mobile-theme-toggle"
                        aria-label="Alihkan mode malam / terang"
                        class="min-h-[38px] min-w-[38px] inline-flex items-center justify-center p-1.5 rounded-lg border border-slate-600/80 bg-slate-800/80 text-amber-300">
                        <svg class="w-4 h-4 hidden dark:inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg class="w-4 h-4 inline-block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <button type="button" id="admin-mobile-toggle" aria-expanded="false" aria-controls="admin-mobile-nav"
                        class="min-h-[38px] px-2.5 py-1.5 rounded-lg border border-slate-500 text-xs font-bold text-white hover:bg-slate-800">
                        Menu Admin
                    </button>
                </div>
            </div>

            <!-- Panel Navigasi Mobile -->
            <nav id="admin-mobile-nav" aria-label="Navigasi Admin Seluler"
                class="hidden lg:hidden pb-4 pt-3 border-t border-slate-700 space-y-1.5">
                <a href="{{ route('admin.dashboard') }}"
                    class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Ringkasan Tugas
                </a>
                <a href="{{ route('admin.informations.index') }}"
                    class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.informations.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Informasi Kampus
                </a>
                <a href="{{ route('admin.reminders.index') }}"
                    class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.reminders.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Pengingat Mahasiswa
                </a>
                <a href="{{ route('admin.live-chat.index') }}"
                    class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.live-chat.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Live Chat Terpusat @if($waitingAdminCount > 0) ({{ $waitingAdminCount }} Menunggu) @endif
                </a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.students.index') }}"
                        class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.students.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Data Mahasiswa
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                        class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Kelola Admin
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                        class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Pengaturan Sistem &amp; API
                    </a>
                @endif
                <div class="pt-2 border-t border-slate-700 flex flex-wrap gap-2">
                    <a href="{{ route('public.index') }}" target="_blank" rel="noopener"
                        class="min-h-[44px] inline-flex items-center px-3 py-2 rounded border border-slate-500 text-xs font-semibold text-white">
                        Buka Website Publik
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="min-h-[44px] px-3.5 py-2 rounded bg-red-700 text-white text-xs font-semibold">
                            Keluar Sesi
                        </button>
                    </form>
                </div>
            </nav>
        </div>
        <!-- Garis Aksen Bawah Header Admin (UNPAM Gold) -->
        <div class="h-1 bg-gradient-to-r from-amber-400 via-unpam-gold to-amber-500 w-full" role="presentation"></div>
    </header>

    <!-- Konten Utama Admin -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">
        @if(session('success'))
            <div role="status"
                class="bg-emerald-50 border border-emerald-400 text-emerald-950 px-4 py-3 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div role="alert"
                class="bg-amber-50 border border-amber-400 text-slate-950 px-4 py-3 rounded-lg text-sm font-medium">
                {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            <div role="alert" class="bg-red-50 border border-red-400 text-red-950 px-4 py-3 rounded-lg text-sm space-y-1">
                <p class="font-bold">Periksa kembali input Anda:</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white dark:bg-slate-800 border-t border-slate-300 dark:border-slate-700 py-3 px-4 sm:px-6 text-xs text-slate-600 dark:text-slate-400">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <span>Portal Pengelolaan Layanan Mahasiswa UNPAM</span>
            <span>Universitas Pamulang</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Theme toggle mode malam / terang
            const themeBtn = document.getElementById('admin-theme-toggle');
            const mobileThemeBtn = document.getElementById('admin-mobile-theme-toggle');

            function toggleTheme() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            }

            if (themeBtn) themeBtn.addEventListener('click', toggleTheme);
            if (mobileThemeBtn) mobileThemeBtn.addEventListener('click', toggleTheme);

            // Mobile toggle
            const btn = document.getElementById('admin-mobile-toggle');
            const nav = document.getElementById('admin-mobile-nav');
            if (btn && nav) {
                btn.addEventListener('click', function () {
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    btn.setAttribute('aria-expanded', String(!expanded));
                    nav.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>

</html>