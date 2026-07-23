<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Profile\OfficialDirectoryService;
use App\Services\Profile\OfficialProfileService;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OfficialDirectoryController extends Controller
{
    public function index(Request $request, OfficialDirectoryService $officialService): InertiaResponse
    {
        $filters = $request->only(['search', 'department']);
        $officials = $officialService->getOfficials($filters, 12);

        return Inertia::render('Public/Profile/Officials/Index', [
            'officials' => $officials,
            'filters' => $filters,
        ]);
    }

    public function show(string $identifier, OfficialProfileService $profileService): InertiaResponse
    {
        $official = $profileService->getOfficialDetail($identifier);

        if (!$official) {
            abort(404, 'Data Pejabat tidak ditemukan.');
        }

        $relatedNews = News::published()->limit(3)->get(['id', 'title', 'slug', 'thumbnail', 'published_at']);

        return Inertia::render('Public/Profile/Officials/Show', [
            'official' => $official,
            'relatedNews' => $relatedNews,
        ]);
    }
}
