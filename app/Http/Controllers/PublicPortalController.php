<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\CampusInformation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = (string) $request->query('kategori', '');
        $search = trim((string) $request->query('q', ''));

        $informations = CampusInformation::published()
            ->when(
                $selectedCategory !== '' && array_key_exists($selectedCategory, CampusInformation::CATEGORIES),
                fn ($q) => $q->where('category', $selectedCategory)
            )
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        $paymentInformations = CampusInformation::published()
            ->where('category', 'pembayaran')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('public.index', [
            'informations' => $informations,
            'paymentInformations' => $paymentInformations,
            'categories' => CampusInformation::CATEGORIES,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
        ]);
    }

    public function show(string $slug): View
    {
        $information = CampusInformation::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedInformations = CampusInformation::published()
            ->where('category', $information->category)
            ->where('id', '!=', $information->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('public.show', [
            'information' => $information,
            'relatedInformations' => $relatedInformations,
            'categories' => CampusInformation::CATEGORIES,
        ]);
    }

    public function about(): View
    {
        return view('public.about', [
            'appName' => AppSetting::getValue('app_institution_name', 'Universitas Pamulang'),
            'logoText' => AppSetting::getValue('app_logo_text', 'UNPAM'),
            'categories' => CampusInformation::CATEGORIES,
        ]);
    }

    public function contact(): View
    {
        $rawPhone = (string) AppSetting::getValue('admin_contact_phone', '6281234567890');
        $botPhoneClean = Student::normalizePhone($rawPhone);
        if ($botPhoneClean === '') {
            $botPhoneClean = '6281234567890';
        }

        $formattedBotPhone = '+' . substr($botPhoneClean, 0, 2) . ' ' . substr($botPhoneClean, 2, 3) . '-' . substr($botPhoneClean, 5, 4) . '-' . substr($botPhoneClean, 9);

        $officialCampuses = [
            [
                'name' => 'Kampus 1 UNPAM (Pusat Pamulang)',
                'address' => 'Jl. Surya Kencana No. 1, Pamulang Barat, Kec. Pamulang, Kota Tangerang Selatan, Banten 15417',
                'phone' => '(021) 7412566 / (021) 74709855',
                'badge' => 'Kampus Utama / Rektorat',
            ],
            [
                'name' => 'Kampus 2 UNPAM (Viktor Serpong)',
                'address' => 'Jl. Puspitek Raya No. 46, Buaran, Kec. Serpong, Kota Tangerang Selatan, Banten 15310',
                'phone' => '(021) 7412566',
                'badge' => 'Gedung Viktor',
            ],
            [
                'name' => 'Kampus 3 UNPAM (Witana Harja)',
                'address' => 'Jl. Witana Harja No. 18b, Pamulang Barat, Kec. Pamulang, Kota Tangerang Selatan, Banten 15417',
                'phone' => '(021) 7412566',
                'badge' => 'Gedung Witana Harja',
            ],
        ];

        return view('public.contact', [
            'appName' => AppSetting::getValue('app_institution_name', 'Universitas Pamulang'),
            'botPhoneClean' => $botPhoneClean,
            'formattedBotPhone' => $formattedBotPhone,
            'officialCampuses' => $officialCampuses,
            'officialEmail' => 'info@unpam.ac.id',
            'officialWebsite' => 'https://unpam.ac.id',
        ]);
    }

    /**
     * Generate dynamic XML sitemap for public pages and published campus information.
     */
    public function sitemap(): Response
    {
        $nowIso = now()->toAtomString();
        $urls = [
            [
                'loc' => route('public.index'),
                'lastmod' => $nowIso,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('public.about'),
                'lastmod' => $nowIso,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('public.contact'),
                'lastmod' => $nowIso,
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
        ];

        try {
            $informations = CampusInformation::published()
                ->orderByDesc('updated_at')
                ->get(['slug', 'updated_at', 'published_at']);

            if ($informations->isNotEmpty() && $informations->first()->updated_at) {
                $urls[0]['lastmod'] = $informations->first()->updated_at->toAtomString();
            }

            foreach ($informations as $info) {
                $urls[] = [
                    'loc' => route('public.show', $info->slug),
                    'lastmod' => ($info->updated_at ?? $info->published_at ?? now())->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        } catch (\Throwable) {
            // Keep base public URLs indexed even when DB query fails during startup/cold start.
        }

        $xmlLines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $item) {
            $xmlLines[] = '    <url>';
            $xmlLines[] = '        <loc>' . htmlspecialchars($item['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            $xmlLines[] = '        <lastmod>' . htmlspecialchars($item['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</lastmod>';
            $xmlLines[] = '        <changefreq>' . $item['changefreq'] . '</changefreq>';
            $xmlLines[] = '        <priority>' . $item['priority'] . '</priority>';
            $xmlLines[] = '    </url>';
        }

        $xmlLines[] = '</urlset>';

        return response(implode("\n", $xmlLines) . "\n", 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}

