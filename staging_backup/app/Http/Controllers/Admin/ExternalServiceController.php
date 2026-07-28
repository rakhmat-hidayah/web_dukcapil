<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ExternalServiceController extends Controller
{
    /**
     * Display the External Services (SANAI Online) configuration form.
     */
    public function index(): InertiaResponse
    {
        $settings = WebsiteSetting::where('group', 'external_services')->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/ExternalServices/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the External Services settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sanai_name'             => 'required|string|max:255',
            'sanai_url'              => 'required|url|max:255',
            'sanai_description'      => 'nullable|string|max:500',
            'sanai_button_label'     => 'required|string|max:100',
            'sanai_is_active'        => 'required|boolean',
            'sanai_open_new_tab'     => 'required|boolean',
            'sanai_display_navbar'   => 'required|boolean',
            'sanai_display_homepage' => 'required|boolean',
            'sanai_display_footer'   => 'required|boolean',
        ]);

        foreach ($data as $key => $val) {
            WebsiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($val) ? ($val ? '1' : '0') : (string) $val,
                    'group' => 'external_services',
                ]
            );
        }

        WebsiteSetting::clearCache();

        ActivityLogger::log("Memperbarui konfigurasi Layanan SANAI Online", null, 'update_sanai_settings');

        return redirect()->back()->with('success', 'Konfigurasi Layanan SANAI Online berhasil diperbarui.');
    }
}
