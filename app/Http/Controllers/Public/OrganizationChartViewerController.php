<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Profile\OrganizationChartService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrganizationChartViewerController extends Controller
{
    public function show(OrganizationChartService $chartService): InertiaResponse
    {
        $tree = $chartService->getTree();

        return Inertia::render('Public/Profile/OrgChart', [
            'tree' => $tree,
        ]);
    }
}
