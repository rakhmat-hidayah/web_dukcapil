<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InformasiLayananController extends Controller
{
    /**
     * Display the Informasi Layanan (Mobil Pelayanan & Bantuan Cepat) configuration form.
     */
    public function index(): InertiaResponse
    {
        $settings = WebsiteSetting::where('group', 'informasi_layanan')->pluck('value', 'key')->toArray();

        // Default initial values
        $defaults = [
            'mobile_service_is_active'         => '1',
            'mobile_service_kecamatan'         => 'Kecamatan Woja',
            'mobile_service_desa'              => 'Desa Monta Baru',
            'mobile_service_date'              => 'Senin, 28 Juli 2026',
            'mobile_service_time'              => '08.00 - 14.00 WITA',
            'mobile_service_status'            => 'hari_ini',
            'mobile_service_button_text'       => 'Lihat Jadwal Lengkap',
            'mobile_service_button_url'        => '/layanan/mobil',
            'quick_help_whatsapp'              => '6282340001111',
            'quick_help_call_center'           => '(0373) 21123',
            'quick_help_tracking_url'          => '/layanan/tracking',
            'quick_help_google_maps_url'       => 'https://maps.google.com/?q=Dukcapil+Dompu',
            'quick_help_email'                 => 'dukcapilkabdompu@gmail.com',
            'quick_help_primary_button_text'   => 'Hubungi Kami',
            'quick_help_primary_button_url'    => '/contact',
        ];

        $mergedSettings = array_merge($defaults, $settings);

        return Inertia::render('Admin/InformasiLayanan/Index', [
            'settings' => $mergedSettings,
        ]);
    }

    /**
     * Update the Informasi Layanan settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Section 1: Mobil Pelayanan
            'mobile_service_is_active'       => 'required|boolean',
            'mobile_service_kecamatan'       => 'nullable|string|max:255',
            'mobile_service_desa'            => 'nullable|string|max:255',
            'mobile_service_date'            => 'nullable|string|max:255',
            'mobile_service_time'            => 'nullable|string|max:255',
            'mobile_service_status'          => 'required|string|in:hari_ini,besok,selesai,akan_datang',
            'mobile_service_button_text'     => 'nullable|string|max:100',
            'mobile_service_button_url'      => 'nullable|string|max:500',

            // Section 2: Bantuan Cepat
            'quick_help_whatsapp'            => 'nullable|string|max:255',
            'quick_help_google_maps_url'     => 'nullable|string|max:500',
        ]);

        foreach ($data as $key => $val) {
            WebsiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($val) ? ($val ? '1' : '0') : (string) ($val ?? ''),
                    'group' => 'informasi_layanan',
                ]
            );
        }

        WebsiteSetting::clearCache();

        ActivityLogger::log("Memperbarui konfigurasi Informasi Layanan (Mobil Pelayanan & Bantuan Cepat)", null, 'update_informasi_layanan');

        return redirect()->back()->with('success', 'Konfigurasi Informasi Layanan berhasil diperbarui.');
    }
}
