<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Models\ServiceRequirement;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MobileServiceController extends Controller
{
    /**
     * Render the public Mobile Service Schedule page.
     */
    public function index(): InertiaResponse
    {
        $settings = WebsiteSetting::where('group', 'informasi_layanan')->pluck('value', 'key')->toArray();
        
        $selectedIdsRaw = $settings['mobile_service_selected_services'] ?? null;
        $selectedIds = json_decode($selectedIdsRaw, true);

        $query = ServiceRequirement::where('is_active', true)->orderBy('sort_order');
        
        // If selected services are configured, only display those checked services
        if (is_array($selectedIds) && count($selectedIds) > 0) {
            $query->whereIn('id', $selectedIds);
        }

        $services = $query->get();

        return Inertia::render('Public/MobileServices/Index', [
            'scheduleSettings' => $settings,
            'services' => $services,
        ]);
    }
}
