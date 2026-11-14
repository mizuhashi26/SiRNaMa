<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin - SiRNaMa UNPAM')</title>
    <meta name="robots" content="noindex, nofollow">
    <script>
        // Theme init
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Sidebar collapsed init
        if (localStorage.getItem('admin_sidebar_collapsed') === 'true' && window.innerWidth >= 1024) {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 antialiased transition-colors duration-200">
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

        .dark .bg-unpam-navy {
            background-color: #0b1322 !important;
        }

        .dark .text-unpam-navy {
            color: #facc15 !important;
        }

        .dark .border-unpam-navy {
            border-color: #1e293b !important;
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

        /* Tampilan Sidebar Collapsed (Desktop: Hanya Ikon) */
        html.sidebar-collapsed aside#admin-sidebar {
            width: 5rem !important;
            /* 80px */
        }

        html.sidebar-collapsed .sidebar-text,
        html.sidebar-collapsed .sidebar-section-title,
        html.sidebar-collapsed .sidebar-full-brand,
        html.sidebar-collapsed .sidebar-user-detail,
        html.sidebar-collapsed .sidebar-public-text {
            display: none !important;
        }

        html.sidebar-collapsed .sidebar-collapsed-brand {
            display: flex !important;
        }

        html.sidebar-collapsed .sidebar-menu-link {
            justify-content: center !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            position: relative;
        }

        html.sidebar-collapsed .sidebar-badge {
            position: absolute !important;
            top: 0.25rem !important;
            right: 0.25rem !important;
            min-width: 1rem !important;
            height: 1rem !important;
            font-size: 9px !important;
            padding: 0 0.25rem !important;
        }

        html.sidebar-collapsed .sidebar-footer-box {
            flex-direction: column !important;
            align-items: center !important;
            gap: 0.75rem !important;
        }

        /* Styling Eksplisit Pop Up Konfirmasi Kustom (Tema Web & Dark Mode) */
        #unpam-confirm-modal .unpam-modal-box {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
        }

        .dark #unpam-confirm-modal .unpam-modal-box {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .dark #unpam-confirm-modal #unpam-modal-title {
            color: #ffffff !important;
        }

        .dark #unpam-confirm-modal #unpam-modal-message {
            color: #cbd5e1 !important;
        }

        .dark #unpam-confirm-modal .unpam-modal-icon-box {
            background-color: rgba(120, 53, 15, 0.4) !important;
            border-color: #b45309 !important;
            color: #fcd34d !important;
        }
    </style>

    <!-- Layout Induk Dua Kolom (Side Navbar + Main Workstation) -->
    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- Backdrop Overlay untuk Mobile Sidebar -->
        <div id="admin-sidebar-backdrop"
            class="fixed inset-0 bg-slate-950/70 z-40 hidden lg:hidden backdrop-blur-xs transition-opacity duration-200"
            role="presentation"></div>

        <!-- SIDE NAVBAR (Sidebar Kiri - Bisa Buka Tutup) -->
        <aside id="admin-sidebar" aria-label="Navigasi Utama Admin"
            class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] lg:w-64 xl:w-72 2xl:w-80 bg-slate-900 text-slate-200 border-r border-slate-800 flex flex-col justify-between transform -translate-x-full lg:translate-x-0 lg:static lg:h-screen lg:sticky lg:top-0 transition-all duration-200 ease-in-out shrink-0 shadow-xl lg:shadow-none">

            <div class="flex flex-col flex-1 overflow-y-auto">
                <!-- Header Sidebar: Logo & Tombol Tutup/Collapse -->
                <div
                    class="p-4 sm:p-5 border-b border-slate-800/80 flex items-center justify-between gap-3 bg-slate-950/40">
                    @php
                        $adminHomeUrl = auth()->user()->isAdminChat() ? route('admin.live-chat.index') : route('admin.dashboard');
                    @endphp
                    @php($resolvedLogoUrl = \App\Models\AppSetting::resolveLogoUrl($brandLogoImage))
                    <!-- Brand Saat Sidebar Terbuka (Full) -->
                    <a href="{{ $adminHomeUrl }}"
                        class="sidebar-full-brand flex items-center gap-3 group focus-visible:outline-unpam-gold min-w-0">
                        @if($resolvedLogoUrl)
                            <img src="{{ $resolvedLogoUrl }}" alt="Logo Kampus"
                                class="h-9 w-auto max-w-[80px] object-contain bg-transparent shrink-0 drop-shadow" />
                        @else
                            <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Kampus"
                                class="h-9 w-9 object-contain bg-transparent shrink-0 drop-shadow" />
                        @endif
                        <div class="leading-tight min-w-0">
                            <span
                                class="block font-black text-sm text-white tracking-tight leading-snug break-words group-hover:text-amber-400 transition-colors">
                                SiRNaMa Admin
                            </span>
                            <span class="block text-[11px] font-medium text-slate-400 leading-snug break-words">
                                {{ $brandAppName }}
                            </span>
                        </div>
                    </a>

                    <!-- Brand Saat Sidebar Ditutup (Hanya Ikon) -->
                    <a href="{{ $adminHomeUrl }}"
                        class="sidebar-collapsed-brand hidden w-full items-center justify-center py-1 group focus-visible:outline-unpam-gold"
                        title="Dashboard Admin - SiRNaMa {{ $brandAppName }}">
                        @if($resolvedLogoUrl)
                            <img src="{{ $resolvedLogoUrl }}" alt="Logo Kampus"
                                class="h-8 w-auto max-w-[40px] object-contain bg-transparent shrink-0 drop-shadow" />
                        @else
                            <img src="{{ asset('images/unpam-logo.svg') }}" alt="Logo Kampus"
                                class="h-8 w-8 object-contain bg-transparent shrink-0 drop-shadow" />
                        @endif
                    </a>

                    <!-- Tombol Tutup Sidebar di Mobile -->
                    <button type="button" id="admin-sidebar-close"
                        class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800"
                        aria-label="Tutup navigasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Tombol Collapse Sidebar di Desktop (Header Sidebar) -->
                    <button type="button" id="admin-sidebar-desktop-toggle"
                        class="hidden lg:inline-flex sidebar-full-brand p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                        title="Tutup / Perkecil Sidebar (Hanya Ikon)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>
                </div>

                <!-- Navigasi Menu Vertikal -->
                <nav class="p-3.5 sm:p-4 space-y-6 flex-1">
                    <!-- Kelompok Menu Utama -->
                    <div class="space-y-1">
                        <span
                            class="sidebar-section-title block px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            Menu Utama
                        </span>

                        @if(!auth()->user()->isAdminChat())
                            <a href="{{ route('admin.dashboard') }}" title="Ringkasan Tugas"
                                class="sidebar-menu-link min-h-[44px] flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-slate-950' : 'text-slate-400' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span class="sidebar-text leading-snug break-words">Ringkasan Tugas</span>
                            </a>

                            <a href="{{ route('admin.informations.index') }}" title="Informasi Kampus"
                                class="sidebar-menu-link min-h-[44px] flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ request()->routeIs('admin.informations.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.informations.*') ? 'text-slate-950' : 'text-slate-400' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                                <span class="sidebar-text leading-snug break-words">Informasi Kampus</span>
                            </a>

                            <a href="{{ route('admin.reminders.index') }}" title="Pengingat Mahasiswa"
                                class="sidebar-menu-link min-h-[44px] flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ request()->routeIs('admin.reminders.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.reminders.*') ? 'text-slate-950' : 'text-slate-400' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="sidebar-text leading-snug break-words">Pengingat Mahasiswa</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.live-chat.index') }}" title="Meja Layanan Live Chat"
                            class="sidebar-menu-link min-h-[44px] flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ request()->routeIs('admin.live-chat.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.live-chat.*') ? 'text-slate-950' : 'text-slate-400' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span class="sidebar-text leading-snug break-words">Meja Layanan (Chat)</span>
                            </div>
                            @if($waitingAdminCount > 0)
                                <span
                                    class="sidebar-badge inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-full text-[10px] font-black leading-none bg-red-600 text-white shrink-0 shadow-xs"
                                    title="{{ $waitingAdminCount }} mahasiswa menunggu respons">
                                    {{ $waitingAdminCount > 99 ? '99+' : $waitingAdminCount }}
                                </span>
                            @endif
                        </a>
                    </div>

                    <!-- Kelompok Menu Khusus Super Admin -->
                    @if(auth()->user()->isSuperAdmin())
                        <div class="space-y-1 pt-2 border-t border-slate-800/80">
                            <span
                                class="sidebar-section-title block px-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                                Administrasi &amp; Sistem
                            </span>

                            <a href="{{ route('admin.students.index') }}" title="Data Mahasiswa"
                                class="sidebar-menu-link min-h-[44px] flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ request()->routeIs('admin.students.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.students.*') ? 'text-slate-950' : 'text-slate-400' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="sidebar-text leading-snug break-words">Data Mahasiswa</span>
                            </a>

                            <a href="{{ route('admin.users.index') }}" title="Kelola Akun Admin"
                                class="sidebar-menu-link min-h-[44px] flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-slate-950' : 'text-slate-400' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="sidebar-text leading-snug break-words">Kelola Admin</span>
                            </a>

                            <a href="{{ route('admin.settings.index') }}" title="Pengaturan Sistem & API"
                                class="sidebar-menu-link min-h-[44px] flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-unpam-gold text-slate-950 font-black shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.*') ? 'text-slate-950' : 'text-slate-400' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="sidebar-text leading-snug break-words">Pengaturan &amp; API</span>
                            </a>
                        </div>
                    @endif
                </nav>
            </div>

            <!-- Footer Sidebar: Profile & Logout -->
            <div class="p-3.5 sm:p-4 border-t border-slate-800/80 bg-slate-950/60 space-y-3">
                <a href="{{ route('public.index') }}" target="_blank" rel="noopener"
                    title="Buka Website Mahasiswa (Tab Baru)"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-800 text-xs text-slate-300 hover:text-white transition-colors border border-slate-700/60">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-unpam-gold shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span class="sidebar-public-text">Lihat Web Publik</span>
                    </span>
                    <span class="sidebar-public-text text-[10px] text-slate-500 font-mono">&rarr;</span>
                </a>

                <div class="sidebar-footer-box flex items-center justify-between gap-2 pt-1">
                    <div class="flex items-center gap-2.5 min-w-0" title="{{ auth()->user()->name ?? 'Admin' }}">
                        <div
                            class="w-8 h-8 rounded-full bg-unpam-gold text-slate-950 font-black text-xs flex items-center justify-center shrink-0 shadow-xs uppercase">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="sidebar-user-detail min-w-0 leading-tight">
                            <span class="block text-xs font-bold text-white leading-snug break-words">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase">
                                {{ auth()->user()->role_label }}
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="shrink-0"
                        data-confirm="Apakah Anda yakin ingin keluar dari sesi admin?">
                        @csrf
                        <button type="submit" title="Keluar sesi admin" aria-label="Keluar sesi admin"
                            class="min-h-[42px] min-w-[42px] inline-flex items-center justify-center p-2 rounded-lg bg-slate-800 hover:bg-red-700 text-slate-300 hover:text-white transition-colors border border-slate-700/60 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- AREA KONTEN UTAMA (Sisi Kanan) -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">

            <!-- HEADER ATAS (Search Bar & Top Utilities) -->
            <header
                class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-3 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-2.5 sm:gap-3 shadow-2xs">

                <div class="flex items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
                    <!-- Tombol Toggle Mobile Sidebar -->
                    <button type="button" id="admin-sidebar-open"
                        class="lg:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-700 shrink-0"
                        aria-label="Buka menu navigasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Tombol Buka / Tutup Sidebar Desktop (Toggle Collapse) -->
                    <button type="button" id="admin-sidebar-collapse-btn" aria-label="Buka / Tutup Sidebar"
                        title="Buka / Tutup Sidebar (Hanya Ikon)"
                        class="hidden lg:inline-flex items-center justify-center min-h-[40px] min-w-[40px] p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-2xs shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h10M4 18h16" />
                        </svg>
                    </button>

                    <!-- SEARCH BAR GLOBAL (Pengganti Navbar di Header) -->
                    @if(!auth()->user()->isAdminChat())
                        <form action="{{ route('admin.informations.index') }}" method="GET"
                            class="w-full max-w-md xl:max-w-xl 2xl:max-w-2xl relative">
                            <div class="relative flex items-center">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="search" name="q" value="{{ request('q') }}"
                                    placeholder="Cari informasi kampus..."
                                    aria-label="Cari informasi kampus"
                                    class="w-full min-h-[40px] pl-10 pr-8 sm:pr-10 py-1.5 text-xs sm:text-sm 2xl:text-base rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-unpam-navy transition-all">
                                @if(request('q'))
                                    <a href="{{ route('admin.informations.index') }}"
                                        class="absolute right-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs">
                                        &times;
                                    </a>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>

                <!-- UTILITIES KANAN: Shortcut Web, Toggle Mode Malam, Status Akun -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="{{ route('public.index') }}" target="_blank" rel="noopener"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-colors"
                        title="Buka Website Mahasiswa (Tab Baru)">
                        <svg class="w-3.5 h-3.5 text-unpam-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Portal Web</span>
                    </a>

                    <!-- Toggle Mode Malam / Terang -->
                    <button type="button" id="admin-theme-toggle" aria-label="Alihkan mode malam / terang"
                        title="Mode Malam / Terang"
                        class="min-h-[40px] min-w-[40px] inline-flex items-center justify-center p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-amber-500 dark:text-amber-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-2xs">
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

                    <!-- Chip Profil Admin di Header -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                        <div
                            class="w-8 h-8 rounded-full bg-unpam-navy dark:bg-slate-700 text-white font-black text-xs flex items-center justify-center shadow-xs uppercase">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="text-left leading-tight hidden xl:block">
                            <span class="block text-xs font-bold text-slate-800 dark:text-white truncate max-w-[120px]">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                            <span class="block text-[10px] text-slate-500 dark:text-slate-400">
                                {{ auth()->user()->role_label }}
                            </span>
                        </div>

                        <!-- Ikon Exit Header -->
                        <form action="{{ route('logout') }}" method="POST" class="inline-flex items-center ml-1"
                            data-confirm="Apakah Anda yakin ingin keluar dari sesi admin?">
                            @csrf
                            <button type="submit" title="Keluar sesi admin" aria-label="Keluar sesi admin"
                                class="min-h-[38px] min-w-[38px] inline-flex items-center justify-center p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- KONTEN UTAMA HALAMAN ADMIN (Fleksibel & Proporsional di Layar Lebar) -->
            <main
                class="flex-1 w-full max-w-none 2xl:max-w-[2200px] mx-auto px-3.5 sm:px-6 lg:px-8 2xl:px-12 py-5 sm:py-8 space-y-6">
                @if(session('success'))
                    <div role="status"
                        class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-400 dark:border-emerald-700 text-emerald-950 dark:text-emerald-200 px-4 py-3 rounded-xl text-xs sm:text-sm font-medium shadow-2xs">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div role="alert"
                        class="bg-amber-50 dark:bg-amber-950/60 border border-amber-400 dark:border-amber-700 text-amber-950 dark:text-amber-200 px-4 py-3 rounded-xl text-xs sm:text-sm font-medium shadow-2xs">
                        {{ session('warning') }}
                    </div>
                @endif

                @if($errors->any())
                    <div role="alert"
                        class="bg-red-50 dark:bg-red-950/60 border border-red-400 dark:border-red-700 text-red-950 dark:text-red-200 px-4 py-3 rounded-xl text-xs sm:text-sm space-y-1 shadow-2xs">
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

            <!-- FOOTER ADMIN -->
            <footer
                class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-3.5 px-4 sm:px-6 lg:px-8 2xl:px-12 text-xs text-slate-500 dark:text-slate-400">
                <div
                    class="w-full max-w-none 2xl:max-w-[2200px] mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <span>SiRNaMa (Sistem Informasi, Reminder, dan Layanan Mahasiswa) &bull; {{ $brandAppName }}</span>
                    <span>{{ $brandAppName }}</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- POP UP PERINGATAN & KONFIRMASI SESUAI DESAIN WEB (UNPAM THEME) -->
    <div id="unpam-confirm-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs transition-opacity duration-200"
        role="dialog" aria-modal="true" aria-labelledby="unpam-modal-title">
        <div
            class="unpam-modal-box bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 transform transition-all duration-200">
            <div class="flex items-start gap-3.5">
                <div id="unpam-modal-icon-box"
                    class="unpam-modal-icon-box w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-300 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 id="unpam-modal-title"
                        class="unpam-modal-title text-base font-extrabold text-slate-900 dark:text-white leading-tight">
                        Konfirmasi Tindakan
                    </h3>
                    <p id="unpam-modal-message"
                        class="unpam-modal-message text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Apakah Anda yakin ingin melanjutkan tindakan ini?
                    </p>
                </div>
            </div>

            <!-- Susunan Tombol: Kiri = Ya / Setuju (Polos Biasa), Kanan = Batal (Tombol Merah) -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-end gap-3">
                <button type="button" id="unpam-modal-btn-confirm"
                    class="min-h-[44px] px-5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 text-xs sm:text-sm font-semibold transition-colors shadow-2xs cursor-pointer">
                    Ya, Lanjutkan
                </button>
                <button type="button" id="unpam-modal-btn-cancel"
                    class="min-h-[44px] px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs sm:text-sm font-bold transition-colors shadow-xs cursor-pointer">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPT LOGIKA SIDEBAR, POP UP PERINGATAN & THEME TOGGLE -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Theme toggle mode malam / terang
            const themeBtn = document.getElementById('admin-theme-toggle');

            function toggleTheme() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            }

            if (themeBtn) themeBtn.addEventListener('click', toggleTheme);

            // Buka Tutup Desktop Sidebar (Collapsed / Expanded)
            function toggleDesktopSidebar() {
                const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            }

            const collapseBtn = document.getElementById('admin-sidebar-collapse-btn');
            const desktopToggle = document.getElementById('admin-sidebar-desktop-toggle');
            if (collapseBtn) collapseBtn.addEventListener('click', toggleDesktopSidebar);
            if (desktopToggle) desktopToggle.addEventListener('click', toggleDesktopSidebar);

            // Drawer Mobile Sidebar Toggle
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            const openBtn = document.getElementById('admin-sidebar-open');
            const closeBtn = document.getElementById('admin-sidebar-close');

            function openSidebar() {
                if (sidebar) sidebar.classList.remove('-translate-x-full');
                if (backdrop) backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.add('-translate-x-full');
                if (backdrop) backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
            }

            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);

            // POP UP ALERT BIASA (1 Tombol Saja)
            window.unpamAlert = function (message, title = 'Pemberitahuan', buttonText = 'Mengerti') {
                window.unpamConfirm(message, function () { }, {
                    title: title,
                    confirmText: buttonText,
                    isAlert: true
                });
            };

            // LOGIKA MODAL POP UP PERINGATAN KUSTOM
            window.unpamConfirm = function (message, onConfirm, options = {}) {
                const modal = document.getElementById('unpam-confirm-modal');
                const msgEl = document.getElementById('unpam-modal-message');
                const titleEl = document.getElementById('unpam-modal-title');
                const btnConfirm = document.getElementById('unpam-modal-btn-confirm');
                const btnCancel = document.getElementById('unpam-modal-btn-cancel');

                if (!modal) {
                    if (options.isAlert) { alert(message); } else if (confirm(message)) { onConfirm(); }
                    return;
                }

                if (titleEl) titleEl.textContent = options.title || 'Konfirmasi Tindakan';
                if (msgEl) msgEl.textContent = message;
                if (btnConfirm) btnConfirm.textContent = options.confirmText || 'Ya, Lanjutkan';

                // Jika cuma butuh alert biasa, sembunyikan tombol Batal
                if (btnCancel) {
                    if (options.isAlert) {
                        btnCancel.classList.add('hidden');
                    } else {
                        btnCancel.classList.remove('hidden');
                        if (options.cancelText) btnCancel.textContent = options.cancelText;
                    }
                }

                modal.classList.remove('hidden');

                function onYes() {
                    cleanup();
                    if (typeof onConfirm === 'function') onConfirm();
                }

                function onNo() {
                    cleanup();
                }

                function onKeyDown(ev) {
                    if (ev.key === 'Escape') {
                        ev.preventDefault();
                        cleanup();
                    }
                }

                function onBackdropClick(ev) {
                    if (ev.target === modal) {
                        cleanup();
                    }
                }

                function cleanup() {
                    modal.classList.add('hidden');
                    if (btnCancel) btnCancel.classList.remove('hidden');
                    btnConfirm.removeEventListener('click', onYes);
                    btnCancel.removeEventListener('click', onNo);
                    document.removeEventListener('keydown', onKeyDown);
                    modal.removeEventListener('click', onBackdropClick);
                }

                btnConfirm.addEventListener('click', onYes);
                btnCancel.addEventListener('click', onNo);
                document.addEventListener('keydown', onKeyDown);
                modal.addEventListener('click', onBackdropClick);
                if (btnCancel && !options.isAlert) {
                    btnCancel.focus();
                } else if (btnConfirm) {
                    btnConfirm.focus();
                }
            };

            // Interseptor Otomatis Form dengan data-confirm atau onsubmit="return confirm(...)"
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form.dataset.unpamConfirmed === 'true') {
                    form.dataset.unpamConfirmed = '';
                    return;
                }

                const confirmMsg = form.getAttribute('data-confirm') || '';
                const onsubmitAttr = form.getAttribute('onsubmit') || '';
                let msg = confirmMsg;

                if (!msg && onsubmitAttr) {
                    const match = onsubmitAttr.match(/confirm\(['"]([^'"]+)['"]\)/);
                    if (match && match[1]) msg = match[1];
                }

                if (msg) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const submitter = e.submitter;
                    window.unpamConfirm(msg, function () {
                        form.dataset.unpamConfirmed = 'true';
                        if (submitter && submitter.name) {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = submitter.name;
                            hidden.value = submitter.value;
                            form.appendChild(hidden);
                        }
                        form.submit();
                    });
                    return false;
                }
            }, true);

            // Interseptor Otomatis Tombol dengan data-confirm atau onclick="return confirm(...)"
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('button[data-confirm], a[data-confirm], button[onclick*="confirm("], a[onclick*="confirm("]');
                if (!btn) return;

                if (btn.dataset.unpamConfirmed === 'true') {
                    btn.dataset.unpamConfirmed = '';
                    return;
                }

                let msg = btn.getAttribute('data-confirm') || '';
                if (!msg) {
                    const onclickAttr = btn.getAttribute('onclick') || '';
                    const match = onclickAttr.match(/confirm\(['"]([^'"]+)['"]\)/);
                    if (match && match[1]) msg = match[1];
                }

                if (msg) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    window.unpamConfirm(msg, function () {
                        btn.dataset.unpamConfirmed = 'true';
                        if (btn.type === 'submit' && btn.form) {
                            if (btn.name) {
                                const hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = btn.name;
                                hidden.value = btn.value;
                                btn.form.appendChild(hidden);
                            }
                            btn.form.submit();
                        } else if (btn.tagName === 'A' && btn.href) {
                            window.location.href = btn.href;
                        } else {
                            btn.click();
                        }
                    });
                    return false;
                }
            }, true);
        });
    </script>
</body>

</html>