<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ApiSettingController extends Controller
{
    /**
     * Display API configuration settings.
     */
    public function index(): InertiaResponse
    {
        $settings = ApiSetting::all()->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/ApiKeys/Settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update API configuration settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'api_rate_limit_public' => 'required|integer|min:1',
            'api_rate_limit_partner' => 'required|integer|min:1',
            'api_terms_of_service' => 'required|string',
        ]);

        ApiSetting::updateOrCreate(['key' => 'api_rate_limit_public'], ['value' => $request->api_rate_limit_public]);
        ApiSetting::updateOrCreate(['key' => 'api_rate_limit_partner'], ['value' => $request->api_rate_limit_partner]);
        ApiSetting::updateOrCreate(['key' => 'api_terms_of_service'], ['value' => $request->api_terms_of_service]);

        // Clear cache
        ApiSetting::clearCache();

        ActivityLogger::log("Memperbarui pengaturan rate-limiting & Ketentuan Layanan API", null, 'update_api_settings');

        return redirect()->back()->with('success', 'Pengaturan API berhasil diperbarui.');
    }
}
