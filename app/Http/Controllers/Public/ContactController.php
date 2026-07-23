<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContactController extends Controller
{
    /**
     * Display contact details and feedback form page.
     */
    public function index(): InertiaResponse
    {
        $settings = WebsiteSetting::getAllCached();

        return Inertia::render('Public/Contact', [
            'contactInfo' => [
                'address'          => $settings['office_address'] ?? 'Jl. Bhayangkara No. 01, Kel. Bada, Dompu, NTB 84211',
                'email'            => $settings['office_email'] ?? 'dukcapil@dompukab.go.id',
                'whatsapp'         => $settings['whatsapp_number'] ?? '628111222333',
                'work_hours'       => $settings['office_work_hours'] ?? 'Senin - Kamis: 08:00 - 16:00 WITA | Jumat: 08:00 - 11:30 WITA',
                'social_instagram' => $settings['social_instagram'] ?? 'https://instagram.com/dukcapil.dompu',
                'social_facebook'  => $settings['social_facebook'] ?? 'https://facebook.com/dukcapil.dompu',
                'map_lat'          => $settings['map_latitude'] ?? '-8.536780',
                'map_lng'          => $settings['map_longitude'] ?? '118.461295',
            ]
        ]);
    }
}
