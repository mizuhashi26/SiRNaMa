@php
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $baseUrl = ($scriptDir !== '' && $scriptDir !== '.') ? $scriptDir : '';
    $homeUrl = $baseUrl !== '' ? $baseUrl . '/' : url('/');
    $loginUrl = $baseUrl !== '' ? $baseUrl . '/login' : url('/login');

    $cssUrl = null;
    $manifestPath = public_path('build/manifest.json');
    if (is_file($manifestPath)) {
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        if ($cssFile) {
            $cssUrl = ($baseUrl !== '' ? $baseUrl : '') . '/build/' . ltrim($cssFile, '/');
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 Not Found - CRM Layanan Mahasiswa UNPAM</title>
    @if($cssUrl)
        <link rel="stylesheet" href="{{ $cssUrl }}">
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 flex flex-col justify-between antialiased">
    <header class="bg-unpam-navy text-white py-3 px-4 border-b border-slate-800">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="inline-flex items-center gap-2.5 font-bold text-sm sm:text-base text-white">
                <span class="px-2 py-1 rounded bg-teal-500 text-white font-extrabold text-xs border border-teal-300">UNPAM</span>
                <span>Layanan Mahasiswa</span>
            </div>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white border border-slate-300 rounded-lg p-6 sm:p-8 text-center space-y-4">
            <span class="inline-block px-3 py-1 rounded bg-red-100 text-red-950 border border-red-300 font-mono text-xs font-bold">
                404 NOT FOUND
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                404 Not Found - Halaman Tidak Ditemukan
            </h1>
            <p class="text-sm text-slate-700 leading-relaxed">
                Alamat URL atau direktori yang Anda akses tidak tersedia pada sistem ini.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $homeUrl }}"
                   class="min-h-[44px] inline-flex items-center px-5 py-2.5 rounded bg-unpam-navy text-white text-sm font-bold hover:bg-unpam-navyDark">
                    Ke Beranda Informasi
                </a>
                <a href="{{ $loginUrl }}"
                   class="min-h-[44px] inline-flex items-center px-4 py-2.5 rounded border border-slate-300 bg-slate-100 text-slate-900 text-sm font-semibold hover:bg-slate-200">
                    Login Admin
                </a>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-600">
        Portal Informasi & Layanan Mahasiswa Universitas Pamulang
    </footer>
</body>
</html>
