<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Services\DownloadPolicyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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

        $downloads = $query->paginate(12)->withQueryString();

        $downloads->getCollection()->transform(function ($doc) {
            return array_merge($doc->toArray(), [
                'download_url'     => route('public.downloads.stream', $doc->id),
                'check_url'        => route('public.downloads.check', $doc->id),
                'file_size_human'  => DownloadPolicyService::formatBytes((int) ($doc->file_size ?? 0)),
            ]);
        });

        $categories = DownloadCategory::whereNull('parent_id')->with('children')->orderBy('sort_order')->get();

        return Inertia::render('Public/Downloads', [
            'downloads'  => $downloads,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'category']),
        ]);
    }
}
