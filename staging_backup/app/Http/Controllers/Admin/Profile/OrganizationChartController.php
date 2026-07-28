<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use App\Models\Profile\OrganizationNode;
use App\Models\Profile\OrganizationPosition;
use App\Models\Profile\Official;
use App\Services\Profile\OrganizationChartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrganizationChartController extends Controller
{
    public function index(OrganizationChartService $chartService): InertiaResponse
    {
        $tree = $chartService->getTree();
        $positions = OrganizationPosition::orderBy('title')->get();
        $officials = Official::where('status', 'active')->orderBy('name')->get();
        $allNodes = OrganizationNode::with(['official', 'position'])->orderBy('sort_order')->get();

        return Inertia::render('Admin/Profile/OrgChart/VisualTreeEditor', [
            'tree' => $tree,
            'positions' => $positions,
            'officials' => $officials,
            'allNodes' => $allNodes,
        ]);
    }

    public function storeNode(Request $request, OrganizationChartService $chartService): RedirectResponse
    {
        $data = $request->validate([
            'node_title' => 'required|string|max:255',
            'position_id' => 'nullable|exists:organization_positions,id',
            'official_id' => 'nullable|exists:officials,id',
            'parent_id' => 'nullable|exists:organization_nodes,id',
            'color_code' => 'nullable|string|max:30',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'integer',
        ]);

        OrganizationNode::create($data);
        $chartService->clearCache();

        return redirect()->back()->with('success', 'Simpul organisasi berhasil ditambahkan.');
    }

    public function updateNode(Request $request, OrganizationNode $node, OrganizationChartService $chartService): RedirectResponse
    {
        $data = $request->validate([
            'node_title' => 'required|string|max:255',
            'position_id' => 'nullable|exists:organization_positions,id',
            'official_id' => 'nullable|exists:officials,id',
            'parent_id' => 'nullable|exists:organization_nodes,id',
            'color_code' => 'nullable|string|max:30',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $node->update($data);
        $chartService->clearCache();

        return redirect()->back()->with('success', 'Simpul organisasi ' . $node->node_title . ' berhasil diperbarui.');
    }

    public function destroyNode(OrganizationNode $node, OrganizationChartService $chartService): RedirectResponse
    {
        $title = $node->node_title;
        $node->delete();
        $chartService->clearCache();

        return redirect()->back()->with('success', 'Simpul organisasi ' . $title . ' berhasil dihapus.');
    }
}
