<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use App\Models\Profile\ProfileSection;
use App\Models\Profile\Official;
use App\Models\Profile\OrganizationNode;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProfileDashboardController extends Controller
{
    public function index(): InertiaResponse
    {
        $stats = [
            'total_sections' => ProfileSection::count(),
            'active_sections' => ProfileSection::where('is_enabled', true)->count(),
            'total_officials' => Official::count(),
            'active_officials' => Official::where('status', 'active')->count(),
            'total_nodes' => OrganizationNode::count(),
            'active_nodes' => OrganizationNode::where('is_active', true)->count(),
        ];

        $sections = ProfileSection::with('setting')->orderBy('sort_order')->get();
        $recentOfficials = Official::orderBy('updated_at', 'desc')->limit(5)->get();

        return Inertia::render('Admin/Profile/Dashboard', [
            'stats' => $stats,
            'sections' => $sections,
            'recentOfficials' => $recentOfficials,
        ]);
    }
}
