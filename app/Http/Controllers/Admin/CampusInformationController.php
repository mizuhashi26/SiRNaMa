<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampusInformationController extends Controller
{
    public function index(Request $request): View
    {
        $category = (string) $request->query('category', '');
        $search = trim((string) $request->query('q', ''));

        $informations = CampusInformation::query()
            ->when($category !== '' && array_key_exists($category, CampusInformation::CATEGORIES), function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.informations.index', [
            'informations' => $informations,
            'categories' => CampusInformation::CATEGORIES,
            'selectedCategory' => $category,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.informations.form', [
            'information' => new CampusInformation([
                'category' => 'pengumuman',
                'is_published' => true,
                'is_pinned' => false,
            ]),
            'categories' => CampusInformation::CATEGORIES,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::in(array_keys(CampusInformation::CATEGORIES))],
            'summary' => ['nullable', 'string', 'max:400'],
            'content' => ['required', 'string'],
            'action_type' => ['nullable', 'string', 'in:draft,publish,schedule'],
            'published_at' => ['nullable', 'date'],
            'is_pinned' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Judul informasi wajib diisi.',
            'category.required' => 'Kategori informasi wajib dipilih.',
            'content.required' => 'Isi informasi lengkap wajib diisi.',
            'published_at.date' => 'Format tanggal dan waktu jadwal publikasi tidak valid.',
        ]);

        $actionType = $validated['action_type'] ?? ($request->boolean('is_published') ? 'publish' : 'draft');
        $isPublished = ($actionType === 'publish' || $actionType === 'schedule');
        $publishedAt = match($actionType) {
            'draft' => null,
            'schedule' => !empty($validated['published_at']) ? Carbon::parse($validated['published_at']) : now(),
            default => now(),
        };

        CampusInformation::create([
            'title' => $validated['title'],
            'slug' => $this->generateUniqueSlug($validated['title']),
            'category' => $validated['category'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'is_published' => $isPublished,
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $publishedAt,
        ]);

        $statusMsg = match($actionType) {
            'draft' => 'Informasi disimpan sebagai Draft (belum dipublikasikan).',
            'schedule' => 'Informasi berhasil dijadwalkan untuk dipublikasikan pada ' . ($publishedAt ? $publishedAt->format('d/m/Y H:i') : '') . ' WIB.',
            default => 'Informasi kampus berhasil dipublikasikan dan langsung tersinkron ke kanal publik & chatbot.',
        };

        return redirect()->route('admin.informations.index')->with('success', $statusMsg);
    }

    public function edit(CampusInformation $information): View
    {
        return view('admin.informations.form', [
            'information' => $information,
            'categories' => CampusInformation::CATEGORIES,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, CampusInformation $information): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::in(array_keys(CampusInformation::CATEGORIES))],
            'summary' => ['nullable', 'string', 'max:400'],
            'content' => ['required', 'string'],
            'action_type' => ['nullable', 'string', 'in:draft,publish,schedule'],
            'published_at' => ['nullable', 'date'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $actionType = $validated['action_type'] ?? ($request->boolean('is_published') ? 'publish' : 'draft');
        $isPublished = ($actionType === 'publish' || $actionType === 'schedule');
        $publishedAt = match($actionType) {
            'draft' => null,
            'schedule' => !empty($validated['published_at']) ? Carbon::parse($validated['published_at']) : ($information->published_at ?? now()),
            default => $information->published_at && $information->published_at <= now() ? $information->published_at : now(),
        };

        $information->update([
            'title' => $validated['title'],
            'slug' => $information->title !== $validated['title']
                ? $this->generateUniqueSlug($validated['title'], $information->id)
                : $information->slug,
            'category' => $validated['category'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'is_published' => $isPublished,
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $publishedAt,
        ]);

        $statusMsg = match($actionType) {
            'draft' => 'Informasi diperbarui sebagai Draft.',
            'schedule' => 'Jadwal publikasi informasi berhasil diperbarui.',
            default => 'Informasi kampus berhasil diperbarui dan dipublikasikan.',
        };

        return redirect()->route('admin.informations.index')->with('success', $statusMsg);
    }

    public function destroy(CampusInformation $information): RedirectResponse
    {
        $information->delete();

        return redirect()->route('admin.informations.index')
            ->with('success', 'Informasi kampus berhasil dihapus.');
    }

    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'informasi-kampus';
        $slug = $base;
        $counter = 2;

        while (
            CampusInformation::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

