<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PageController extends Controller
{
    /**
     * Show dynamic profile/service pages.
     */
    public function show(string $slug)
    {
        if ($slug === 'profil-dinas-kependudukan-dan-pencatatan-sipil' || $slug === 'profil') {
            return redirect()->route('public.profile.index');
        }

        $page = Page::where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Public/Page', [
            'page' => $page,
        ]);
    }
}
