@extends('layouts.public')

@section('title', 'Pusat Informasi Kampus & Layanan Mahasiswa - SiRNaMa UNPAM')

@section('content')
    <!-- Hero Fungsional & Pencarian -->
    <section
        class="bg-unpam-navy dark:bg-slate-950 text-white border-b-2 border-unpam-gold/40 dark:border-slate-800 py-8 sm:py-10 lg:py-12 relative">
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Pemisah Visual dan Penanda Konteks -->
            <div
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-unpam-navyDark dark:bg-slate-900 border border-unpam-gold/50 dark:border-amber-400/40 text-unpam-gold text-xs font-semibold mb-4 shadow-sm">
                <span class="inline-block w-2 h-2 rounded-full bg-unpam-gold"></span>
                <span>Pusat Informasi & Layanan Terpadu Mahasiswa</span>
            </div>

            <div class="max-w-4xl space-y-3 sm:space-y-4">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    Pusat Informasi Akademik, Jadwal Pembayaran, dan Layanan Mahasiswa
                </h1>
                <p class="text-slate-200 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                    Seluruh informasi pada halaman ini bersumber dari pusat data CRM yang sama dengan layanan Chatbot
                    Website dan Chatbot WhatsApp mahasiswa.
                </p>

                <form action="{{ route('public.index') }}" method="GET" class="pt-2 flex flex-col sm:flex-row gap-2.5">
                    @if($selectedCategory !== '')
                        <input type="hidden" name="kategori" value="{{ $selectedCategory }}">
                    @endif
                    <div class="flex-1">
                        <label for="search-info-input" class="sr-only">Cari informasi kampus</label>
                        <input type="search" id="search-info-input" name="q" value="{{ $search }}"
                            placeholder="Cari jadwal ujian, kalender akademik, atau informasi pembayaran..."
                            class="w-full min-h-[44px] px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 text-sm border border-slate-300 dark:border-slate-700 focus:outline-unpam-gold">
                    </div>
                    <button type="submit"
                        class="min-h-[44px] px-5 py-2.5 rounded bg-unpam-gold text-slate-950 font-bold text-sm hover:bg-unpam-goldHover shadow-sm shrink-0">
                        Cari Informasi
                    </button>
                    @if($search !== '' || $selectedCategory !== '')
                        <a href="{{ route('public.index') }}"
                            class="min-h-[44px] inline-flex items-center justify-center px-4 py-2.5 rounded border border-slate-400 text-white text-sm font-medium hover:bg-slate-800 shrink-0">
                            Reset Filter
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </section>

    <div
        class="w-full max-w-7xl 2xl:max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12 py-6 sm:py-8 lg:py-10 space-y-8 sm:space-y-10">
        <!-- Sorotan Informasi Pembayaran Umum (Bila Tersedia) -->
        @if($paymentInformations->isNotEmpty() && ($selectedCategory === '' || $selectedCategory === 'pembayaran'))
            <section aria-labelledby="heading-payment-highlight"
                class="bg-amber-50/80 dark:bg-slate-900 border-2 border-amber-400 dark:border-amber-500/60 rounded-2xl p-5 sm:p-6 shadow-xs">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-amber-200 dark:border-slate-700">
                    <div>
                        <h2 id="heading-payment-highlight"
                            class="text-lg sm:text-xl 2xl:text-2xl font-black text-slate-900 dark:text-white">
                            Pengumuman &amp; Jadwal Pembayaran Kuliah
                        </h2>
                        <p class="text-xs sm:text-sm 2xl:text-base text-slate-700 dark:text-slate-300 mt-0.5">
                            Informasi jadwal pembayaran dan administrasi perkuliahan semester berjalan.
                        </p>
                    </div>
                    <a href="{{ route('public.index', ['kategori' => 'pembayaran']) }}"
                        class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl bg-unpam-navy dark:bg-slate-800 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 dark:hover:bg-slate-700 border border-slate-700 dark:border-slate-600 self-start sm:self-auto transition-colors">
                        Lihat Semua Info Pembayaran
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                    @foreach($paymentInformations as $payInfo)
                        <article
                            class="bg-white dark:bg-slate-800 border border-amber-200/70 dark:border-slate-700 rounded-xl p-4 flex flex-col justify-between shadow-xs hover:border-amber-300 dark:hover:border-slate-600 dark:hover:bg-slate-800/90 transition-all">
                            <div class="space-y-2">
                                <span
                                    class="inline-block text-xs font-bold bg-amber-100 dark:bg-amber-950/70 text-slate-950 dark:text-amber-300 border border-amber-300 dark:border-amber-600/70 px-2.5 py-0.5 rounded-md">
                                    {{ $payInfo->category_label }}
                                </span>
                                <h3 class="font-bold text-base 2xl:text-lg text-slate-900 dark:text-white leading-snug">
                                    <a href="{{ route('public.show', $payInfo->slug) }}"
                                        class="hover:text-unpam-navy dark:hover:text-amber-300 hover:underline transition-colors">
                                        {{ $payInfo->title }}
                                    </a>
                                </h3>
                                <p class="text-xs sm:text-sm 2xl:text-base text-slate-700 dark:text-slate-300 leading-relaxed">
                                    {{ $payInfo->summary ?: \Illuminate\Support\Str::limit(strip_tags($payInfo->content), 120) }}
                                </p>
                            </div>
                            <div
                                class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                                <span>{{ $payInfo->published_at?->format('d M Y') ?? $payInfo->created_at->format('d M Y') }}</span>
                                <a href="{{ route('public.show', $payInfo->slug) }}"
                                    class="font-bold text-unpam-navy dark:text-amber-300 hover:underline min-h-[36px] inline-flex items-center">
                                    Baca Detail
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Filter Kategori & Daftar Informasi Kampus -->
        <section aria-labelledby="heading-directory">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <div>
                    <h2 id="heading-directory" class="text-xl font-bold text-slate-900 dark:text-white">
                        Direktori Informasi Kampus
                    </h2>
                    <p class="text-sm text-slate-700 dark:text-slate-300">
                        Pilih kategori informasi atau gunakan tombol Chat Layanan Mahasiswa di kanan bawah layar.
                    </p>
                </div>
            </div>

            <!-- Baris Filter Kategori (Tap Target >= 44px) -->
            <div class="flex flex-wrap gap-2 mb-6" role="navigation" aria-label="Filter Kategori Informasi">
                <a href="{{ route('public.index', array_filter(['q' => $search])) }}"
                    class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-lg text-xs sm:text-sm font-semibold border {{ $selectedCategory === '' ? 'bg-unpam-navy text-white border-unpam-navy shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    Semua Kategori
                </a>
                @foreach($categories as $key => $label)
                    <a href="{{ route('public.index', array_filter(['kategori' => $key, 'q' => $search])) }}"
                        class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-lg text-xs sm:text-sm font-semibold border {{ $selectedCategory === $key ? 'bg-unpam-navy text-white border-unpam-navy shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if($informations->isEmpty())
                <!-- Empty State (Anti-Slop R-27) -->
                <div
                    class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-8 text-center max-w-xl mx-auto my-4 space-y-3">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Informasi pada Filter Ini</h3>
                    <p class="text-sm text-slate-700 dark:text-slate-300">
                        Tidak ditemukan informasi kampus yang sesuai dengan pencarian atau kategori yang dipilih.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('public.index') }}"
                            class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-lg bg-unpam-navy text-white text-sm font-semibold hover:bg-unpam-navyDark shadow-xs">
                            Tampilkan Semua Informasi
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($informations as $info)
                        <article
                            class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl p-5 flex flex-col justify-between hover:border-slate-400 dark:hover:border-slate-600 dark:hover:bg-slate-800/90 transition-all shadow-xs hover:shadow-sm">
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span
                                        class="inline-block text-xs font-semibold px-2.5 py-1 rounded {{ $info->category === 'pembayaran' ? 'bg-amber-100 dark:bg-slate-700 text-slate-950 dark:text-amber-300 border border-amber-300 dark:border-amber-600/50' : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600' }}">
                                        {{ $info->category_label }}
                                    </span>
                                    @if($info->is_pinned)
                                        <span
                                            class="inline-block text-xs font-bold px-2 py-0.5 rounded bg-unpam-navy dark:bg-slate-900 text-white border border-slate-600">
                                            Penting
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
                                    <a href="{{ route('public.show', $info->slug) }}"
                                        class="hover:text-unpam-navy dark:hover:text-amber-300 hover:underline transition-colors">
                                        {{ $info->title }}
                                    </a>
                                </h3>

                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                    {{ $info->summary ?: \Illuminate\Support\Str::limit(strip_tags($info->content), 140) }}
                                </p>
                            </div>

                            <div
                                class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                                <span>Dipublikasikan:
                                    {{ $info->published_at?->format('d M Y') ?? $info->created_at->format('d M Y') }}</span>
                                <a href="{{ route('public.show', $info->slug) }}"
                                    class="min-h-[40px] inline-flex items-center font-bold text-unpam-navy dark:text-amber-300 hover:underline transition-colors">
                                    Baca Selengkapnya
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $informations->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection