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
        $settings = WebsiteSetting::allAsArray();
        
        $selectedIdsRaw = $settings['mobile_service_selected_services'] ?? null;
        $selectedIds = json_decode($selectedIdsRaw, true);
        if (is_array($selectedIds)) {
            $selectedIds = array_map('intval', $selectedIds);
        }

        $customServicesRaw = $settings['mobile_service_custom_services'] ?? null;
        $customServices = json_decode($customServicesRaw, true) ?: [];

        $query = ServiceRequirement::where('is_active', true)->orderBy('sort_order');
        
        // If selected services are configured in CMS, only display those checked services
        if (is_array($selectedIds) && count($selectedIds) > 0) {
            $query->whereIn('id', $selectedIds);
        }

        $baseServices = $query->get()->toArray();

        // Include custom services if selected in CMS
        $activeCustomServices = [];
        if (is_array($customServices)) {
            foreach ($customServices as $custom) {
                $cId = (int)($custom['id'] ?? 0);
                if (!is_array($selectedIds) || in_array($cId, $selectedIds)) {
                    $activeCustomServices[] = [
                        'id'          => $cId,
                        'title'       => $custom['title'] ?? 'Layanan Kustom',
                        'description' => $custom['description'] ?? '',
                        'icon'        => $custom['icon'] ?? '📝',
                        'color'       => '#4f46e5',
                        'slug'        => '',
                        'is_custom'   => true,
                    ];
                }
            }
        }

        $services = array_merge($baseServices, $activeCustomServices);

        return Inertia::render('Public/MobileServices/Index', [
            'scheduleSettings' => $settings,
            'services'         => $services,
        ]);
    }
}
