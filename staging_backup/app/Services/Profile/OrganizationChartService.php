<?php

namespace App\Services\Profile;

use App\Models\Profile\OrganizationNode;
use Illuminate\Support\Facades\Cache;

class OrganizationChartService
{
    /**
     * Get full organization tree with eager loaded officials and positions.
     */
    public function getTree(): array
    {
        return Cache::remember('organization_chart_tree', 300, function () {
            $rootNodes = OrganizationNode::with([
                'official',
                'position',
                'children' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order', 'asc');
                },
                'children.official',
                'children.position',
                'children.children' => function ($q2) {
                    $q2->where('is_active', true)->orderBy('sort_order', 'asc');
                },
                'children.children.official',
                'children.children.position',
                'children.children.children' => function ($q3) {
                    $q3->where('is_active', true)->orderBy('sort_order', 'asc');
                },
                'children.children.children.official',
                'children.children.children.position',
            ])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

            return $rootNodes->toArray();
        });
    }

    /**
     * Clear org chart cache.
     */
    public function clearCache(): void
    {
        Cache::forget('organization_chart_tree');
    }
}
