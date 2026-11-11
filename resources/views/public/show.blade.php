@extends('layouts.public')

@section('title', $information->title . ' - Informasi Mahasiswa UNPAM')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 space-y-8">
        <nav aria-label="Breadcrumb" class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 flex flex-wrap items-center gap-2">
            <a href="{{ route('public.index') }}" class="font-semibold text-unpam-navy dark:text-amber-400 hover:underline min-h-[36px] inline-flex items-center">
                Beranda Informasi
            </a>
            <span>/</span>
            <a href="{{ route('public.index', ['kategori' => $information->category]) }}" class="font-semibold text-unpam-navy dark:text-amber-400 hover:underline min-h-[36px] inline-flex items-center">
                {{ $information->category_label }}
            </a>
            <span>/</span>
            <span class="text-slate-600 dark:text-slate-400">{{ $information->title }}</span>
        </nav>

        <article class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-6 sm:p-8 space-y-6">
            <header class="space-y-3 border-b border-slate-200 dark:border-slate-700 pb-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-block text-xs font-bold px-2.5 py-1 rounded {{ $information->category === 'pembayaran' ? 'bg-amber-100 text-slate-950 border border-amber-300 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-700' : 'bg-slate-200 text-slate-900 dark:bg-slate-700 dark:text-slate-200' }}">
                        {{ $information->category_label }}
                    </span>
                    <span class="text-xs text-slate-600 dark:text-slate-400">
                        Dipublikasikan pada {{ $information->published_at?->format('d F Y, H:i') ?? $information->created_at->format('d F Y, H:i') }} WIB
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white leading-tight">
                    {{ $information->title }}
                </h1>

                @if($information->summary)
                    <p class="text-base font-medium text-slate-700 dark:text-slate-300 leading-relaxed">
                        {{ $information->summary }}
                    </p>
                @endif
            </header>

            <div class="prose max-w-none text-slate-900 dark:text-slate-100 leading-relaxed space-y-4 text-sm sm:text-base whitespace-pre-line">{{ $information->content }}</div>

            <footer class="pt-6 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Kode Referensi Chatbot: Ketik <strong>INFO {{ $information->id }}</strong> pada Chatbot Website atau WhatsApp.
                </p>
                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            onclick="window.openUnpamChat && window.openUnpamChat();"
                            class="min-h-[44px] px-4 py-2 rounded bg-unpam-navy text-white text-xs sm:text-sm font-semibold hover:bg-unpam-navyDark">
                        Tanyakan via Chatbot / Admin
                    </button>
                    <a href="{{ route('public.index') }}"
                       class="min-h-[44px] inline-flex items-center px-4 py-2 rounded border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-slate-100 text-xs sm:text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600">
                        Kembali ke Daftar
                    </a>
                </div>
            </footer>
        </article>

        @if($relatedInformations->isNotEmpty())
            <section aria-labelledby="related-heading" class="space-y-4">
                <h2 id="related-heading" class="text-lg font-bold text-slate-900 dark:text-white">
                    Informasi Lain pada Kategori {{ $information->category_label }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($relatedInformations as $related)
                        <article class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded p-4 flex flex-col justify-between">
                            <div class="space-y-1.5">
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                                    <a href="{{ route('public.show', $related->slug) }}" class="hover:underline">
                                        {{ $related->title }}
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-700 dark:text-slate-300">
                                    {{ $related->summary ?: \Illuminate\Support\Str::limit(strip_tags($related->content), 90) }}
                                </p>
                            </div>
                            <a href="{{ route('public.show', $related->slug) }}" class="mt-3 text-xs font-bold text-unpam-navy dark:text-amber-400 hover:underline inline-flex items-center min-h-[36px]">
                                Baca Informasi
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

