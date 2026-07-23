<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\ThemeSetting;
use App\Models\WebsiteSetting;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        return [
            ...parent::share($request),
            
            // Auth details with granular roles and permissions
            'auth' => [
                'user' => $user ? $user->only('id', 'name', 'email') : null,
                'roles' => $user ? $user->getRoleNames() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
            ],
            
            // Cached branding and site settings
            'theme_settings' => ThemeSetting::getAllCached(),
            'website_settings' => WebsiteSetting::getAllCached(),

            // Dynamic navigation menus for frontend
            'nav_menus' => Cache::remember('nav_menus', 300, function () {
                $menus = Menu::where('is_active', true)
                    ->with(['items' => function ($q) {
                        $q->where('is_active', true)
                          ->whereNull('parent_id')
                          ->orderBy('sort_order')
                          ->with(['children' => function ($q2) {
                              $q2->where('is_active', true)->orderBy('sort_order');
                          }]);
                    }])
                    ->get();

                $result = [];
                foreach ($menus as $menu) {
                    $result[$menu->location] = [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'location' => $menu->location,
                        'items' => $menu->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'label' => $item->label,
                                'url' => $item->url,
                                'target' => $item->target,
                                'sort_order' => $item->sort_order,
                                'children' => $item->children->map(function ($child) {
                                    return [
                                        'id' => $child->id,
                                        'label' => $child->label,
                                        'url' => $child->url,
                                        'target' => $child->target,
                                        'sort_order' => $child->sort_order,
                                    ];
                                })->toArray(),
                            ];
                        })->toArray(),
                    ];
                }
                return $result;
            }),

            // Published pages list for dynamic navigation
            'published_pages' => Cache::remember('published_pages', 300, function () {
                return Page::where('status', 'published')
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get(['id', 'title', 'slug', 'show_in_menu', 'sort_order']);
            }),

            // Flash messaging
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
