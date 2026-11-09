<?php

namespace App\Http\Controllers;

use App\Models\CampusInformation;
use Illuminate\Http\Request;
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
}

