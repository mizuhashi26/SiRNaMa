@extends('layouts.public')

@section('title', 'Kontak & Lokasi Kampus - Layanan Mahasiswa UNPAM')

@section('content')
    <!-- Header Banner -->
    <section class="bg-unpam-navy text-white border-b-2 border-unpam-gold/40 py-8 sm:py-10 lg:py-12 relative">
        <div class="w-full max-w-7xl 2xl:max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">
            <div
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-unpam-navyDark border border-unpam-gold/50 text-unpam-gold text-xs font-semibold mb-3 shadow-sm">
                <span class="inline-block w-2 h-2 rounded-full bg-unpam-gold"></span>
                <span>Pusat Bantuan &amp; Saluran Resmi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                Kontak &amp; Layanan Mahasiswa
            </h1>
            <p class="text-slate-200 text-sm sm:text-base leading-relaxed mt-2 max-w-3xl">
                Hubungi saluran WhatsApp Chatbot untuk layanan mandiri 24 jam atau hubungi kontak sekretariat kampus
                Universitas Pamulang.
            </p>
        </div>
    </section>

    <div class="w-full max-w-7xl 2xl:max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12 py-6 sm:py-8 lg:py-12 space-y-8 sm:space-y-10">
        <!-- Grid Saluran Utama: Kontak Resmi di Kiri, WhatsApp Bot CRM di Kanan -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

            <!-- Kolom 1 (KIRI, lg:col-span-7): Kontak Resmi Official Kampus UNPAM -->
            <div class="lg:col-span-7 space-y-6 order-2 lg:order-1">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-unpam-navy dark:text-amber-400">Official
                        Information</span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">
                        Kontak Resmi Universitas Pamulang
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1">
                        Informasi resmi rektorat, sekretariat, dan lokasi gedung kampus Universitas Pamulang.
                    </p>
                </div>

                <!-- Kontak Cepat (Email, Telepon, Web) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div
                        class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-4 space-y-1">
                        <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Telepon
                            Kantor</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">(021) 7412566</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Fax: (021) 74709855</p>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-4 space-y-1">
                        <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email
                            Resmi</p>
                        <a href="mailto:{{ $officialEmail }}"
                            class="text-sm font-bold text-unpam-navy dark:text-amber-400 hover:underline break-all block">
                            {{ $officialEmail }}
                        </a>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">humas@unpam.ac.id</p>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-4 space-y-1">
                        <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Website
                            Kampus</p>
                        <a href="{{ $officialWebsite }}" target="_blank" rel="noopener noreferrer"
                            class="text-sm font-bold text-unpam-navy dark:text-amber-400 hover:underline block">
                            unpam.ac.id &rarr;
                        </a>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Portal Utama UNPAM</p>
                    </div>
                </div>

                <!-- Jam Pelayanan Loket Kampus -->
                <div
                    class="bg-slate-100 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-700 dark:text-slate-300">
                    <div>
                        <span class="font-bold text-slate-900 dark:text-white block text-sm">Jam Operasional Pelayanan
                            Kampus</span>
                        <span>Senin s/d Jumat: <strong>08.00 - 19.00 WIB</strong> &amp; </span>
                        <span>Sabtu: <strong>08.00 - 15.00 WIB</strong> (Hari Minggu &amp; Libur Nasional
                            Tutup)</span>
                    </div>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 font-semibold text-slate-800 dark:text-slate-200 shrink-0">
                        Loket Pelayanan Mahasiswa
                    </span>
                </div>

                <!-- Daftar Gedung & Lokasi Kampus Resmi UNPAM -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Lokasi Gedung &amp; Kampus Resmi UNPAM
                    </h3>

                    <div class="space-y-3">
                        @foreach($officialCampuses as $campus)
                            <div
                                class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-4 space-y-1.5 hover:border-slate-400 dark:hover:border-slate-600 transition-colors shadow-xs">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <h4 class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">
                                        {{ $campus['name'] }}</h4>
                                    <span
                                        class="text-[11px] font-semibold bg-unpam-navy/10 dark:bg-slate-700 text-unpam-navy dark:text-amber-300 px-2.5 py-0.5 rounded">
                                        {{ $campus['badge'] }}
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                    {{ $campus['address'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $campus['phone'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Kolom 2 (KANAN, lg:col-span-5): WhatsApp Bot CRM & Jadwal Admin Membalas -->
            <div
                class="lg:col-span-5 bg-white dark:bg-slate-800 border-2 border-emerald-500/80 dark:border-emerald-600 rounded-2xl p-6 sm:p-7 shadow-sm space-y-5 order-1 lg:order-2">
                <div class="flex items-center justify-between gap-3">
                    <span
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/80 px-2.5 py-1 rounded-full border border-emerald-300 dark:border-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Chatbot 24/7 Aktif
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kanal Resmi Bot</span>
                </div>

                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white leading-snug">
                        WhatsApp Bot Layanan Mahasiswa
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        Nomor integrasi WhatsApp CRM kampus. Melayani informasi akademik, jadwal pembayaran, dan
                        eskalasi langsung ke Live Chat Admin.
                    </p>
                </div>

                <!-- Kartu Nomor WhatsApp Bot -->
                <div
                    class="bg-slate-50 dark:bg-slate-700/80 border border-emerald-300 dark:border-emerald-600 rounded-xl p-4 space-y-2 shadow-xs">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Nomor WhatsApp
                        Bot</p>
                    <div class="flex items-center justify-between gap-2">
                        <span
                            class="text-lg sm:text-xl font-mono font-extrabold text-emerald-800 dark:text-emerald-300 tracking-wide">
                            {{ $formattedBotPhone }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-300">
                        Ketik <strong>Halo</strong> atau <strong>Menu</strong> pada WhatsApp untuk memulai interaksi.
                    </p>
                </div>

                <!-- Informasi Jadwal Admin Membalas -->
                <div
                    class="bg-amber-50/80 dark:bg-slate-800/80 border border-amber-300 dark:border-slate-700 rounded-xl p-4 space-y-2.5">
                    <div class="flex items-center gap-2 text-amber-900 dark:text-amber-300">
                        <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xs font-bold uppercase tracking-wider">Jadwal Admin Membalas (Live Chat)</h3>
                    </div>
                    <ul class="text-xs text-slate-700 dark:text-slate-200 space-y-1.5 leading-relaxed">
                        <li class="flex items-center justify-between">
                            <span>Senin - Jumat:</span>
                            <span class="font-bold text-slate-900 dark:text-white">08.00 - 20.00 WIB</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span>Sabtu:</span>
                            <span class="font-bold text-slate-900 dark:text-white">08.00 - 15.00 WIB</span>
                        </li>
                        <li
                            class="flex items-start justify-between gap-2 text-slate-600 dark:text-slate-300 pt-1 border-t border-amber-200/80 dark:border-slate-600">
                            <span>Di luar jam tersebut:</span>
                            <span class="text-right font-medium text-amber-800 dark:text-amber-300">
                                Balas lambat atau pada jam kerja berikutnya</span>
                        </li>
                    </ul>
                    <p
                        class="text-[11px] text-slate-600 dark:text-slate-400 pt-1 border-t border-amber-200/60 dark:border-slate-600 leading-normal">
                        <em>Catatan:</em> Layanan <strong>Chatbot Otomatis tetap siaga 24/7</strong> untuk menjawab
                        informasi kampus kapan pun tanpa batas waktu.
                    </p>
                </div>

                <!-- Tombol Aksi WhatsApp & Web Chat -->
                <div class="space-y-2.5 pt-1">
                    <a href="https://wa.me/{{ $botPhoneClean }}?text=Halo%20Layanan%20Mahasiswa%20UNPAM" target="_blank"
                        rel="noopener noreferrer"
                        class="min-h-[48px] w-full inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 transition-colors shadow-md">
                        <!-- SVG WhatsApp -->
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path
                                d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                        </svg>
                        Hubungi via WhatsApp Bot
                    </a>

                    <button type="button" onclick="window.openUnpamChat && window.openUnpamChat();"
                        class="min-h-[44px] w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-unpam-navy dark:border-slate-600 bg-white dark:bg-slate-800 text-unpam-navy dark:text-amber-300 font-bold text-xs sm:text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-4 h-4 text-unpam-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Buka Widget Chatbot Website
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection
