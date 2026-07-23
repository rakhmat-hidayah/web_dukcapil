<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MenuController extends Controller
{
    /**
     * Display menu list and items builder.
     */
    public function index(Request $request): InertiaResponse
    {
        $menus = Menu::orderBy('name')->get();
        $selectedMenu = null;

        if ($menus->count() > 0) {
            $menuId = $request->query('menu_id', $menus->first()->id);
            $selectedMenu = Menu::with(['allItems' => function ($q) {
                $q->orderBy('sort_order');
            }])->find($menuId);
        }

        return Inertia::render('Admin/Menus/Index', [
            'menus' => $menus,
            'selectedMenu' => $selectedMenu,
        ]);
    }

    /**
     * Clear menu cache keys.
     */
    private function clearMenuCache(): void
    {
        Cache::forget('nav_menus');
        Cache::forget('nav_menus_v2');
    }

    /**
     * Store a new navigation menu container.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'required|in:header,footer,sidebar',
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'location' => $request->location,
            'is_active' => true,
        ]);

        ActivityLogger::log("Membuat Menu Navigasi: {$menu->name}", $menu, 'create_menu');

        $this->clearMenuCache();

        return redirect()->route('admin.menus.index', ['menu_id' => $menu->id])->with('success', 'Menu berhasil dibuat.');
    }

    /**
     * Delete a menu container.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $menuName = $menu->name;
        $menu->delete(); // cascades or delete items first

        ActivityLogger::log("Menghapus Menu Navigasi: {$menuName}", null, 'delete_menu');

        $this->clearMenuCache();

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    /**
     * Store a MenuItem node inside a menu.
     */
    public function storeItem(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'url' => 'nullable|string|max:255',
            'target' => 'required|in:_self,_blank',
            'parent_id' => 'nullable|exists:menu_items,id',
            'icon' => 'nullable|string|max:50',
        ]);

        $maxSort = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $request->parent_id)
            ->max('sort_order') ?? 0;

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $request->parent_id,
            'label' => $request->label,
            'url' => $request->url,
            'target' => $request->target,
            'icon' => $request->icon,
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        $this->clearMenuCache();

        return redirect()->back()->with('success', 'Item menu berhasil ditambahkan.');
    }

    /**
     * Update a MenuItem node.
     */
    public function updateItem(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'url' => 'nullable|string|max:255',
            'target' => 'required|in:_self,_blank',
            'icon' => 'nullable|string|max:50',
        ]);

        $menuItem->update($request->only(['label', 'url', 'target', 'icon']));

        $this->clearMenuCache();

        return redirect()->back()->with('success', 'Item menu berhasil diperbarui.');
    }

    /**
     * Delete a MenuItem node.
     */
    public function destroyItem(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();
        $this->clearMenuCache();
        return redirect()->back()->with('success', 'Item menu berhasil dihapus.');
    }

    /**
     * Reorder menu items tree hierarchy.
     */
    public function reorderItems(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
            'items.*.sort' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            MenuItem::where('id', $item['id'])->update([
                'parent_id' => $item['parent_id'],
                'sort_order' => $item['sort'],
            ]);
        }

        ActivityLogger::log("Menata ulang urutan menu: {$menu->name}", $menu, 'reorder_menu_items');

        $this->clearMenuCache();

        return redirect()->back()->with('success', 'Urutan menu berhasil diperbarui.');
    }
}
