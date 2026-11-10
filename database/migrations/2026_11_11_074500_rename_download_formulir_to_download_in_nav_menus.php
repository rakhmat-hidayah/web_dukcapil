<?php

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
        MenuItem::where('label', 'Download Formulir')
            ->where('url', '/downloads')
            ->update(['label' => 'Download']);

        Cache::forget('nav_menus');
        Cache::forget('nav_menus_v2');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MenuItem::where('label', 'Download')
            ->where('url', '/downloads')
            ->update(['label' => 'Download Formulir']);

        Cache::forget('nav_menus');
        Cache::forget('nav_menus_v2');
    }
};
