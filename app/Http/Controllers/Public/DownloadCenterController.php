<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\DownloadCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\URL;

class DownloadCenterController extends Controller
{
    /**
     * Render the public downloads page.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = Download::with('category')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Return secure temporary signed URLs in download list
        $downloads = $query->paginate(12)->withQueryString();

        $downloads->getCollection()->transform(function ($doc) {
            $signedUrl = URL::temporarySignedRoute(
                'public.downloads.stream',
                now()->addMinutes(30),
                ['id' => $doc->id]
            );

            return array_merge($doc->toArray(), [
                'download_url' => $signedUrl,
            ]);
        });

        $categories = DownloadCategory::whereNull('parent_id')->with('children')->orderBy('sort_order')->get();

        return Inertia::render('Public/Downloads', [
            'downloads' => $downloads,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }
}
