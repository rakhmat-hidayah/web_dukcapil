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

    public function handle(Request $request, \Closure $next)
    {
        $response = parent::handle($request, $next);

        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if ($content && (str_contains($content, 'Rakyat') || str_contains($content, 'Aspirasi'))) {
                $newContent = str_replace([
                    'Aspirasi & Pengaduan Rakyat (Lapor!)',
                    'Aspirasi &amp; Pengaduan Rakyat (Lapor!)',
                    'Aspirasi \u0026 Pengaduan Rakyat (Lapor!)',
                    'Aspirasi \u0026amp; Pengaduan Rakyat (Lapor!)',
                    'Pengaduan Rakyat (Lapor)',
                    'Pengaduan Rakyat',
                ], [
                    'Aspirasi & Pengaduan Masyarakat (Lapor!)',
                    'Aspirasi &amp; Pengaduan Masyarakat (Lapor!)',
                    'Aspirasi \u0026 Pengaduan Masyarakat (Lapor!)',
                    'Aspirasi \u0026amp; Pengaduan Masyarakat (Lapor!)',
                    'Pengaduan Masyarakat (Lapor)',
                    'Pengaduan Masyarakat',
                ], $content);

                $response->setContent($newContent);
            }
        }

        return $response;
    }

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
                'user' => $user ? $user->only('id', 'username', 'name', 'email', 'is_active', 'last_login_at') : null,
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

            // Published pages list for dynamic navigation - cached as pure array
            'published_pages' => function () {
                $pages = Cache::remember('published_pages_v3', 300, function () {
                    return Page::where('status', 'published')
                        ->orderBy('sort_order')
                        ->orderBy('title')
                        ->get(['id', 'title', 'slug', 'show_in_menu', 'sort_order'])
                        ->toArray();
                });
                return is_array($pages) && !isset($pages['__PHP_Incomplete_Class_Name']) ? $pages : [];
            },

            // Flash messaging
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
