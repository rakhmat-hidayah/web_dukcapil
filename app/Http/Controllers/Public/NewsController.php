<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NewsController extends Controller
{
    /**
     * Display public news feed listing.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = News::with(['category', 'author'])
            ->published()
            ->orderBy('published_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $news = $query->paginate(9)->withQueryString();
        $categories = NewsCategory::orderBy('sort_order')->get(['id', 'name', 'slug', 'color']);

        return Inertia::render('Public/News/Index', [
            'news' => $news,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Display specific news details.
     */
    public function show(string $slug): InertiaResponse
    {
        $item = News::with(['category', 'author', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views count safely
        $item->incrementViews();

        // Retrieve related articles
        $related = News::with('category')
            ->published()
            ->where('id', '!=', $item->id)
            ->where('news_category_id', $item->news_category_id)
            ->limit(3)
            ->get();

        return Inertia::render('Public/News/Show', [
            'news' => $item,
            'related' => $related,
        ]);
    }
}
