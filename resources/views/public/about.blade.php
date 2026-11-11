@extends('layouts.public')

@section('title', 'Tentang CRM Layanan Mahasiswa UNPAM')

@section('content')
    <!-- Header Banner -->
    <section class="bg-unpam-navy text-white border-b-2 border-unpam-gold/40 py-10 sm:py-12 relative">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-unpam-navyDark border border-unpam-gold/50 text-unpam-gold text-xs font-semibold mb-3 shadow-sm">
                <span class="inline-block w-2 h-2 rounded-full bg-unpam-gold"></span>
                <span>Mengenal Sistem Layanan</span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                Tentang CRM Layanan Mahasiswa
            </h1>
            <p class="text-slate-200 text-sm sm:text-base leading-relaxed mt-2 max-w-2xl">
                Sistem pengelolaan informasi kampus, pengingat pembayaran, dan layanan bantuan mahasiswa terintegrasi WhatsApp &amp; Website.
            </p>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12 space-y-12">
        <!-- Pengantar & Profil Aplikasi -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-unpam-navy dark:text-amber-300 bg-amber-100 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700 px-2.5 py-1 rounded">
                    Sistem CRM Mandiri
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white leading-tight">
                    Mewujudkan Layanan Informasi Mahasiswa yang Cepat, Akurat, dan Mudah Dijangkau
                </h2>
                <p class="text-sm sm:text-base text-slate-700 dark:text-slate-300 leading-relaxed">
                    Aplikasi ini dirancang sebagai platform CRM terpadu yang membantu pengelola kampus mempublikasikan informasi akademik, mengirimkan reminder batas pembayaran secara massal melalui WhatsApp, serta menangani konsultasi dan pertanyaan mahasiswa secara real-time.
                </p>
                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Melalui konsep <em>Single Source of Truth</em>, setiap informasi yang diperbarui oleh admin di dashboard akan langsung tersedia secara konsisten di portal website publik, chatbot interaktif website, dan WhatsApp Bot mahasiswa.
                </p>
            </div>

            <!-- Kartu Sorotan Nilai Utama -->
            <div class="lg:col-span-5 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl p-6 sm:p-7 shadow-sm space-y-4">
                <h3 class="font-bold text-base text-slate-900 dark:text-white pb-2 border-b border-slate-200 dark:border-slate-700">
                    Nilai &amp; Fokus Pelayanan
                </h3>

                <ul class="space-y-3.5 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                        <div>
                            <strong class="text-slate-900 dark:text-white block font-semibold">Akses Tanpa Hambatan</strong>
                            <span>Mahasiswa dapat mengakses seluruh pengumuman dan chatbot tanpa perlu login akun.</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                        <div>
                            <strong class="text-slate-900 dark:text-white block font-semibold">Integrasi WhatsApp Dua Arah</strong>
                            <span>Penerimaan pesan mahasiswa via Webhook dan pengiriman reminder melalui Fonnte API Gateway.</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 font-bold flex items-center justify-center shrink-0 mt-0.5">✓</span>
                        <div>
                            <strong class="text-slate-900 dark:text-white block font-semibold">Transisi Bot ke Live Chat</strong>
                            <span>Mahasiswa dapat berpindah dari menu otomatis ke petugas admin kapan pun membutuhkan bantuan spesifik.</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- 4 Modul Inti Layanan CRM -->
        <div class="space-y-6 pt-4">
            <div class="border-b border-slate-200 dark:border-slate-800 pb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-unpam-navy dark:text-amber-400">Fitur Unggulan</span>
                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">
                    Empat Pilar Utama CRM Layanan
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Pilar 1 -->
                <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 space-y-2.5 shadow-xs flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-lg bg-unpam-navy text-unpam-gold flex items-center justify-center font-black text-base">
                            01
                        </div>
                        <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug">Portal Informasi Satu Pintu</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pusat publikasi pengumuman akademik, jadwal ujian, kalender akademik, dan info kegiatan terbitan kampus.
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold text-unpam-navy dark:text-amber-400 pt-2 block border-t border-slate-100 dark:border-slate-700">Kategori Lengkap</span>
                </div>

                <!-- Pilar 2 -->
                <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 space-y-2.5 shadow-xs flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-lg bg-unpam-gold text-slate-950 flex items-center justify-center font-black text-base">
                            02
                        </div>
                        <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug">Reminder WhatsApp Terjadwal</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Pengiriman pengingat massal batas pembayaran kuliah dan administrasi berkala secara otomatis atau manual.
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-800 dark:text-slate-200 pt-2 block border-t border-slate-100 dark:border-slate-700">Fonnte Gateway</span>
                </div>

                <!-- Pilar 3 -->
                <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 space-y-2.5 shadow-xs flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-base">
                            03
                        </div>
                        <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug">Chatbot Berbasis Menu</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Layanan interaktif 24/7 di website &amp; WhatsApp untuk menjawab kebutuhan informasi secara instan tanpa antre.
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-400 pt-2 block border-t border-slate-100 dark:border-slate-700">Respons Instan</span>
                </div>

                <!-- Pilar 4 -->
                <div class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 space-y-2.5 shadow-xs flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="w-10 h-10 rounded-lg bg-slate-800 dark:bg-slate-700 text-white flex items-center justify-center font-black text-base">
                            04
                        </div>
                        <h4 class="font-bold text-base text-slate-900 dark:text-white leading-snug">Live Chat Admin Terpusat</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Panel antrean percakapan terpadu dengan status Bot Aktif, Menunggu Admin, Ambil Alih, dan Akhiri Sesi.
                        </p>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 pt-2 block border-t border-slate-100 dark:border-slate-700">Satu Dashboard</span>
                </div>
            </div>
        </div>

        <!-- Penjelasan Studi Kasus & Catatan Transparansi -->
        <div class="bg-slate-100 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-2xl p-6 sm:p-8 space-y-3">
            <div class="flex items-center gap-2 text-slate-800 dark:text-slate-200 font-bold text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span>Catatan Transparansi Studi Kasus</span>
            </div>
            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                Aplikasi ini merupakan implementasi mandiri proyek CRM Layanan Mahasiswa dengan studi kasus <strong>Universitas Pamulang (UNPAM)</strong>. Aplikasi beroperasi secara independen (<em>standalone</em>) dan memanfaatkan nomor dummy untuk simulasi WhatsApp guna keperluan demonstrasi perkuliahan.
            </p>
        </div>

        <!-- Call to Action -->
        <div class="bg-unpam-navy text-white rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 shadow-sm">
            <div class="space-y-1.5 max-w-xl">
                <h3 class="text-lg sm:text-xl font-extrabold text-white">Butuh Informasi Kampus Sekarang?</h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Jelajahi direktori pengumuman kampus atau hubungi chatbot WhatsApp untuk mendapatkan panduan cepat.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('public.index') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl bg-unpam-gold text-slate-950 font-bold text-xs sm:text-sm hover:bg-amber-400 transition-colors shadow-sm">
                    Lihat Informasi
                </a>
                <a href="{{ route('public.contact') }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-400 bg-white/10 text-white font-semibold text-xs sm:text-sm hover:bg-white/20 transition-colors">
                    Kontak &amp; WhatsApp Bot
                </a>
            </div>
        </div>
    </div>
@endsection

