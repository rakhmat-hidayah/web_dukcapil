<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class GalleryController extends Controller
{
    /**
     * Display public gallery grid.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = GalleryAlbum::where('is_published', true)->orderBy('sort_order');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $albums = $query->withCount('items')->get();

        return Inertia::render('Public/Gallery/Index', [
            'albums' => $albums,
            'filters' => $request->only('type'),
        ]);
    }

    /**
     * Show album media items.
     */
    public function show(string $slug): InertiaResponse
    {
        $album = GalleryAlbum::where('is_published', true)
            ->where('slug', $slug)
            ->with('items')
            ->firstOrFail();

        return Inertia::render('Public/Gallery/Show', [
            'album' => $album,
        ]);
    }
}
