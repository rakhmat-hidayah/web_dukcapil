<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OfficeContactController extends Controller
{
    /**
     * Display the Office Contact & Social Media configuration form.
     */
    public function index(): InertiaResponse
    {
        $settings = WebsiteSetting::whereIn('group', ['office_contact', 'footer'])->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/OfficeContact/Index', [
            'settings' => [
                'office_address'    => $settings['office_address'] ?? 'Jl. Bhayangkara No. 01, Kel. Bada, Dompu, NTB 84211',
                'office_email'      => $settings['office_email'] ?? 'dukcapil@dompukab.go.id',
                'office_work_hours' => $settings['office_work_hours'] ?? 'Senin - Kamis: 08:00 - 16:00 WITA | Jumat: 08:00 - 11:30 WITA',
                'whatsapp_number'   => $settings['whatsapp_number'] ?? '628111222333',
                'social_instagram'  => $settings['social_instagram'] ?? 'https://instagram.com/dukcapil.dompu',
                'social_facebook'   => $settings['social_facebook'] ?? 'https://facebook.com/dukcapil.dompu',
                'map_latitude'      => $settings['map_latitude'] ?? '-8.536780',
                'map_longitude'     => $settings['map_longitude'] ?? '118.461295',
            ],
        ]);
    }

    /**
     * Update the Office Contact & Social Media settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'office_address'    => 'required|string|max:500',
            'office_email'      => 'required|email|max:255',
            'office_work_hours' => 'required|string|max:255',
            'whatsapp_number'   => 'required|string|max:50',
            'social_instagram'  => 'nullable|string|max:255',
            'social_facebook'   => 'nullable|string|max:255',
            'map_latitude'      => 'required|string|max:50',
            'map_longitude'     => 'required|string|max:50',
        ]);

        foreach ($data as $key => $val) {
            WebsiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) ($val ?? ''),
                    'group' => 'office_contact',
                ]
            );
        }

        WebsiteSetting::clearCache();

        ActivityLogger::log("Memperbarui Kontak Kantor, Media Sosial (IG/FB), & Peta Instansi", null, 'update_office_contact');

        return redirect()->back()->with('success', 'Informasi Kontak, Medsos (IG/FB), & Peta Kantor berhasil diperbarui.');
    }
}
