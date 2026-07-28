<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Profile\ProfileService;
use App\Services\Profile\OrganizationChartService;
use App\Services\Profile\OfficialDirectoryService;
use App\Models\News;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProfilePageController extends Controller
{
    public function index(
        ProfileService $profileService,
        OrganizationChartService $chartService,
        OfficialDirectoryService $officialService
    ): InertiaResponse {
        $activeSections = $profileService->getActiveSections();
        $orgTree = $chartService->getTree();
        $officials = $officialService->getOfficials([], 6);
        $recentNews = News::published()->limit(3)->get(['id', 'title', 'slug', 'thumbnail', 'published_at']);

        return Inertia::render('Public/Profile/Index', [
            'sections' => $activeSections,
            'orgTree' => $orgTree,
            'officials' => $officials,
            'recentNews' => $recentNews,
        ]);
    }
}
