<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Models\ServiceRequirement;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InformasiLayananController extends Controller
{
    /**
     * Display the Informasi Layanan (Pelayanan Mobile & Bantuan Cepat) configuration form.
     */
    public function index(): InertiaResponse
    {
        $settings = WebsiteSetting::where('group', 'informasi_layanan')->pluck('value', 'key')->toArray();

        // Get all available active service requirements
        $allServices = ServiceRequirement::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'icon', 'color', 'description']);

        // Default initial values for all sections
        $defaults = [
            // Hero Banner Header (/layanan/mobil)
            'mobile_service_hero_tag'           => 'Jemput Bola Adminduk',
            'mobile_service_hero_title'         => 'Jadwal Pelayanan Mobile Keliling',
            'mobile_service_hero_description'   => 'Layanan jemput bola penerbitan dokumen kependudukan langsung ke desa, kelurahan, dan sekolah di seluruh wilayah Kabupaten Dompu secara gratis dan cepat.',

            'mobile_service_is_active'          => '1',
            'mobile_service_kecamatan'          => 'Kecamatan Woja',
            'mobile_service_desa'               => 'Desa Monta Baru',
            'mobile_service_date'               => 'Senin, 28 Juli 2026',
            'mobile_service_time'               => '08.00 - 14.00 WITA',
            'mobile_service_status'             => 'hari_ini',
            'mobile_service_button_text'        => 'Lihat Jadwal Lengkap',
            'mobile_service_button_url'         => '/layanan/mobil',
            'mobile_service_notes'              => 'Harap membawa berkas persyaratan fisik lengkap (ASLI & Fotokopi) untuk mempercepat proses penerbitan dokumen di lokasi pelayanan mobile keliling.',
            'mobile_service_selected_services'  => json_encode($allServices->pluck('id')->toArray()),
            
            // Persyaratan Umum Berkas (Multi-line text, 1 requirement per line)
            'mobile_service_requirements'       => "Fotokopi Kartu Keluarga (KK) terbaru.\nKTP-el lama atau Surat Keterangan Pengganti (jika ubah data/rusak).\nSurat Keterangan Kehilangan dari Kepolisian (jika KTP/KK hilang).\nBuku Nikah / Kutipan Akta Perkawinan (jika ubah status perkawinan).\nSurat Keterangan Lahir dari Bidan/Klinik/RS (untuk Akta Kelahiran).",
            
            // Card Permohonan Kunjungan (Kades/Sekolah)
            'mobile_service_request_badge'      => 'Khusus Kepala Desa / Sekolah',
            'mobile_service_request_title'      => 'Ajukan Kunjungan Pelayanan Mobile',
            'mobile_service_request_description'=> 'Pemerintah Desa, Kelurahan, atau Kepala Sekolah di Kabupaten Dompu dapat mengajukan surat permohonan kunjungan tim jemput bola Dukcapil ke wilayah masing-masing.',
            'mobile_service_request_button_text'=> 'Hubungi Tim Jemput Bola via WA',
            'mobile_service_request_button_url' => 'https://wa.me/6282340001111',
            
            // Card Informasi Kontak Lebih Lanjut
            'mobile_service_contact_title'      => 'Butuh Informasi Lebih Lanjut?',
            'mobile_service_contact_description'=> 'Hubungi petugas layanan Dukcapil Kabupaten Dompu melalui saluran kontak resmi.',
            'mobile_service_contact_button_text'=> 'Lihat Kontak Kantor',
            'mobile_service_contact_button_url' => '/contact',
            
            // Bantuan Cepat Widget Beranda
            'quick_help_whatsapp'               => 'https://wa.me/6282340001111',
            'quick_help_google_maps_url'        => 'https://maps.google.com/?q=Dukcapil+Dompu',
        ];

        $mergedSettings = array_merge($defaults, $settings);

        return Inertia::render('Admin/InformasiLayanan/Index', [
            'settings' => $mergedSettings,
            'allServices' => $allServices,
        ]);
    }

    /**
     * Update the Informasi Layanan settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Section 0: Hero Banner Header
            'mobile_service_hero_tag'           => 'nullable|string|max:100',
            'mobile_service_hero_title'         => 'nullable|string|max:255',
            'mobile_service_hero_description'   => 'nullable|string|max:1000',

            // Section 1: Pelayanan Mobile Utama
            'mobile_service_is_active'          => 'required|boolean',
            'mobile_service_kecamatan'          => 'nullable|string|max:255',
            'mobile_service_desa'               => 'nullable|string|max:255',
            'mobile_service_date'               => 'nullable|string|max:255',
            'mobile_service_time'               => 'nullable|string|max:255',
            'mobile_service_status'             => 'required|string|in:hari_ini,besok,selesai,akan_datang',
            'mobile_service_button_text'        => 'nullable|string|max:100',
            'mobile_service_button_url'         => 'nullable|string|max:500',
            'mobile_service_notes'              => 'nullable|string|max:1000',
            'mobile_service_selected_services'  => 'nullable|array',

            // Section 2: Persyaratan Umum Berkas
            'mobile_service_requirements'       => 'nullable|string|max:2000',

            // Section 3: Ajukan Kunjungan Mobil (Desa/Sekolah)
            'mobile_service_request_badge'      => 'nullable|string|max:100',
            'mobile_service_request_title'      => 'nullable|string|max:255',
            'mobile_service_request_description'=> 'nullable|string|max:1000',
            'mobile_service_request_button_text'=> 'nullable|string|max:100',
            'mobile_service_request_button_url' => 'nullable|string|max:500',

            // Section 4: Informasi Kontak Lebih Lanjut
            'mobile_service_contact_title'      => 'nullable|string|max:255',
            'mobile_service_contact_description'=> 'nullable|string|max:1000',
            'mobile_service_contact_button_text'=> 'nullable|string|max:100',
            'mobile_service_contact_button_url' => 'nullable|string|max:500',

            // Section 5: Bantuan Cepat Widget
            'quick_help_whatsapp'               => 'nullable|string|max:255',
            'quick_help_google_maps_url'        => 'nullable|string|max:500',
        ]);

        foreach ($data as $key => $val) {
            if ($key === 'mobile_service_selected_services') {
                $val = json_encode(array_map('intval', $val ?? []));
            } elseif (is_bool($val)) {
                $val = $val ? '1' : '0';
            } else {
                $val = (string) ($val ?? '');
            }

            WebsiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $val,
                    'group' => 'informasi_layanan',
                ]
            );
        }

        WebsiteSetting::clearCache();

        ActivityLogger::log("Memperbarui konfigurasi Informasi Layanan & Halaman Pelayanan Mobile", null, 'update_informasi_layanan');

        return redirect()->back()->with('success', 'Konfigurasi Informasi Layanan & Pelayanan Mobile berhasil diperbarui.');
    }
}
