<?php

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $headerMenu = Menu::where('location', 'header')->first();

        if ($headerMenu) {
            $existing = MenuItem::where('menu_id', $headerMenu->id)
                ->whereNull('parent_id')
                ->where(function ($q) {
                    $q->where('url', '/downloads')
                      ->orWhere('label', 'Download Formulir');
                })
                ->first();

            if (!$existing) {
                // Shift items at or after sort_order 8 (like Kontak) up by 1
                MenuItem::where('menu_id', $headerMenu->id)
                    ->whereNull('parent_id')
                    ->where('sort_order', '>=', 8)
                    ->increment('sort_order');

                MenuItem::create([
                    'menu_id'    => $headerMenu->id,
                    'parent_id'  => null,
                    'label'      => 'Download Formulir',
                    'url'        => '/downloads',
                    'target'     => '_self',
                    'sort_order' => 8,
                    'is_active'  => true,
                ]);
            }
        }

        Cache::forget('nav_menus');
        Cache::forget('nav_menus_v2');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $headerMenu = Menu::where('location', 'header')->first();

        if ($headerMenu) {
            $item = MenuItem::where('menu_id', $headerMenu->id)
                ->whereNull('parent_id')
                ->where('url', '/downloads')
                ->where('label', 'Download Formulir')
                ->first();

            if ($item) {
                $order = $item->sort_order;
                $item->delete();

                // Shift back
                MenuItem::where('menu_id', $headerMenu->id)
                    ->whereNull('parent_id')
                    ->where('sort_order', '>', $order)
                    ->decrement('sort_order');
            }
        }

        Cache::forget('nav_menus');
        Cache::forget('nav_menus_v2');
    }
};
