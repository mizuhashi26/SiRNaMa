<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin - CRM Layanan Mahasiswa UNPAM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 flex flex-col antialiased">
    @php
        $waitingAdminCount = \App\Models\Conversation::where('status', \App\Models\Conversation::STATUS_WAITING_ADMIN)->count();
    @endphp

    <!-- Top Navigation Bar Admin -->
    <header class="bg-unpam-navy text-white sticky top-0 z-30 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between py-3 gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="min-h-[44px] inline-flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded bg-unpam-gold text-slate-950 font-extrabold text-xs tracking-tight border border-amber-300">
                            UNPAM
                        </span>
                        <div>
                            <span class="block font-bold text-sm sm:text-base text-white leading-tight">Dashboard Admin Layanan</span>
                            <span class="block text-[11px] text-slate-300">Universitas Pamulang</span>
                        </div>
                    </a>
                </div>

                <!-- Navigasi Desktop -->
                <nav aria-label="Navigasi Dashboard Admin" class="hidden lg:flex items-center gap-1.5">
                    <a href="{{ route('admin.dashboard') }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Ringkasan Tugas
                    </a>
                    <a href="{{ route('admin.informations.index') }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.informations.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Informasi Kampus
                    </a>
                    <a href="{{ route('admin.reminders.index') }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.reminders.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Reminder Pembayaran
                    </a>
                    <a href="{{ route('admin.live-chat.index') }}"
                       class="min-h-[44px] inline-flex items-center gap-2 px-3.5 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.live-chat.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        <span>Live Chat</span>
                        @if($waitingAdminCount > 0)
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-extrabold {{ request()->routeIs('admin.live-chat.*') ? 'bg-slate-950 text-white' : 'bg-amber-400 text-slate-950 animate-pulse' }}">
                                {{ $waitingAdminCount }} Menunggu
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.students.index') }}"
                       class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded text-sm font-medium {{ request()->routeIs('admin.students.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                        Data Mahasiswa
                    </a>
                </nav>

                <div class="hidden lg:flex items-center gap-2">
                    <a href="{{ route('public.index') }}"
                       target="_blank"
                       rel="noopener"
                       class="min-h-[44px] inline-flex items-center px-3 py-2 rounded border border-slate-500 text-xs font-semibold text-white hover:bg-slate-800">
                        Lihat Website Publik
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="min-h-[44px] px-3.5 py-2 rounded bg-slate-800 hover:bg-red-700 text-white text-xs font-semibold border border-slate-600">
                            Keluar ({{ auth()->user()->name ?? 'Admin' }})
                        </button>
                    </form>
                </div>

                <!-- Tombol Menu Mobile -->
                <button type="button"
                        id="admin-mobile-toggle"
                        aria-expanded="false"
                        aria-controls="admin-mobile-nav"
                        class="lg:hidden min-h-[44px] px-3 py-2 rounded border border-slate-500 text-xs font-bold text-white hover:bg-slate-800">
                    Menu Admin
                </button>
            </div>

            <!-- Panel Navigasi Mobile -->
            <nav id="admin-mobile-nav" aria-label="Navigasi Admin Seluler" class="hidden lg:hidden pb-4 pt-3 border-t border-slate-700 space-y-1.5">
                <a href="{{ route('admin.dashboard') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Ringkasan Tugas
                </a>
                <a href="{{ route('admin.informations.index') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.informations.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Informasi Kampus
                </a>
                <a href="{{ route('admin.reminders.index') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.reminders.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Reminder Pembayaran
                </a>
                <a href="{{ route('admin.live-chat.index') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.live-chat.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Live Chat Terpusat @if($waitingAdminCount > 0) ({{ $waitingAdminCount }} Menunggu) @endif
                </a>
                <a href="{{ route('admin.students.index') }}" class="block min-h-[44px] px-3 py-2.5 rounded text-sm font-medium {{ request()->routeIs('admin.students.*') ? 'bg-unpam-gold text-slate-950 font-bold' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                    Data Mahasiswa
                </a>
                <div class="pt-2 border-t border-slate-700 flex flex-wrap gap-2">
                    <a href="{{ route('public.index') }}" target="_blank" rel="noopener" class="min-h-[44px] inline-flex items-center px-3 py-2 rounded border border-slate-500 text-xs font-semibold text-white">
                        Buka Website Publik
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="min-h-[44px] px-3.5 py-2 rounded bg-red-700 text-white text-xs font-semibold">
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
            <div role="status" class="bg-emerald-50 border border-emerald-400 text-emerald-950 px-4 py-3 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div role="alert" class="bg-amber-50 border border-amber-400 text-slate-950 px-4 py-3 rounded-lg text-sm font-medium">
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

    <footer class="bg-white border-t border-slate-300 py-4 px-4 sm:px-6 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <span>Portal Pengelolaan Layanan Mahasiswa UNPAM</span>
            <span>Universitas Pamulang</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

