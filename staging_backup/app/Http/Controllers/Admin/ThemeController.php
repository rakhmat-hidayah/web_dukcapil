<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ThemeController extends Controller
{
    /**
     * Display theme customizer configurations.
     */
    public function index(): InertiaResponse
    {
        $settings = ThemeSetting::orderBy('group')->get();

        return Inertia::render('Admin/Theme/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update theme configurations in bulk.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings' => 'required|array',
            // File upload validators
            'site_logo' => 'nullable|image|max:2048',
            'site_logo_dark' => 'nullable|image|max:2048',
            'site_favicon' => 'nullable|image|max:1024',
            'site_hero_bg' => 'nullable|image|max:5120',
        ]);

        $settingsData = $request->input('settings', []);

        // 1. Process files if present
        $fileKeys = ['site_logo', 'site_logo_dark', 'site_favicon', 'site_hero_bg'];
        foreach ($fileKeys as $key) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = "{$key}_" . time() . ".{$extension}";
                
                // Store in public brand path
                $path = $file->storeAs('brand', $filename, 'public');
                $publicUrl = Storage::disk('public')->url($path);

                // Update settings values
                ThemeSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $publicUrl, 'type' => 'image', 'group' => 'brand']
                );
            }
        }

        // 2. Process text / color / font configurations
        foreach ($settingsData as $key => $value) {
            // Ignore file keys handled above
            if (in_array($key, $fileKeys)) {
                continue;
            }

            ThemeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Clear cache
        ThemeSetting::clearCache();

        ActivityLogger::log("Memperbarui konfigurasi visual / tema website", null, 'update_theme');

        return redirect()->back()->with('success', 'Tema website berhasil diperbarui.');
    }
}
