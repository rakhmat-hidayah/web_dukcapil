<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\News;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SearchController extends Controller
{
    /**
     * Handle global keywords search across all CMS tables.
     */
    public function search(Request $request): InertiaResponse
    {
        $keyword = $request->query('q', '');

        $news = [];
        $pages = [];
        $downloads = [];

        if (!empty($keyword)) {
            // 1. Search News
            $news = News::published()
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('excerpt', 'like', "%{$keyword}%")
                ->limit(5)
                ->get(['title', 'slug', 'excerpt', 'published_at']);

            // 2. Search Pages
            $pages = Page::where('status', 'published')
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%")
                ->limit(5)
                ->get(['title', 'slug']);

            // 3. Search Downloads
            $downloads = Download::where('status', 'published')
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->limit(5)
                ->get(['id', 'title', 'file_type', 'file_size']);
        }

        return Inertia::render('Public/Search', [
            'keyword' => $keyword,
            'newsResults' => $news,
            'pageResults' => $pages,
            'downloadResults' => $downloads,
        ]);
    }
}
