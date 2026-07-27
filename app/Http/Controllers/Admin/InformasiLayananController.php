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
        $settings = WebsiteSetting::whereIn('group', ['informasi_layanan', 'pimpinan', 'profil_instansi'])->pluck('value', 'key')->toArray();

        // Get all available active service requirements
        $allServices = ServiceRequirement::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'icon', 'color', 'description']);

        // Default initial values for all sections
        $defaults = [
            // Hero Banner Header (/layanan/mobile)
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
            'mobile_service_button_url'         => '/layanan/mobile',
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
            
            // Widget Bantuan Cepat
            'quick_help_whatsapp'               => 'https://wa.me/6282340001111',
            'quick_help_google_maps_url'        => 'https://maps.google.com/?q=Dukcapil+Dompu',

            // Sambutan Pimpinan / Foto Kepala Dinas
            'leader_name'                       => 'Drs. Abd. Najib',
            'leader_title'                      => 'Kepala Dinas Dukcapil Kabupaten Dompu',
            'leader_speech_title'               => '"Pelayanan Kependudukan Yang Transparan & Membahagiakan Masyarakat"',
            'leader_speech_content'             => "Assalamu’alaikum Warahmatullahi Wabarakatuh,\n\nSelamat datang di portal resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Kehadiran portal ini merupakan bagian dari komitmen kami dalam mewujudkan keterbukaan informasi publik dan kemudahan akses layanan administrasi kependudukan bagi seluruh lapisan masyarakat.\n\nSeluruh jajaran Dinas Dukcapil Kabupaten Dompu siap melayani Anda dengan semangat integritas, profesionalisme, dan tanpa pungutan biaya apapun (Rp 0,-).",
            'leader_photo'                      => '/images/kepala_dinas.jpg',

            // Visi Misi Instansi
            'profile_visi_title'                => 'Visi Instansi',
            'profile_visi_text'                 => '"Terwujudnya Pelayanan Kependudukan dan Pencatatan Sipil yang Prima, Akurat, Terintegrasi, dan Membahagiakan Masyarakat di Kabupaten Dompu."',
            'profile_misi_title'                => 'Misi Pelayanan',
            'profile_misi_items'                => "Meningkatkan kualitas dan kecepatan pelayanan administrasi kependudukan dan pencatatan sipil.\nMewujudkan validitas dan akurasi database kependudukan Kabupaten Dompu yang terintegrasi SIAK Terpusat.\nMendorong pemanfaatan data kependudukan secara luas bagi perencanaan pembangunan daerah.",

            // Tugas Pokok & Fungsi
            'profile_duties_title'              => 'Tugas Pokok & Fungsi',
            'profile_duties_item_1_title'       => 'Pendaftaran Penduduk',
            'profile_duties_item_1_desc'        => 'Pelayanan penerbitan KTP-El, KK, KIA, dan surat keterangan pindah penduduk.',
            'profile_duties_item_2_title'       => 'Pencatatan Sipil',
            'profile_duties_item_2_desc'        => 'Penerbitan akta kelahiran, akta perkawinan, akta perceraian, dan akta kematian.',
            'profile_duties_item_3_title'       => 'Pengelolaan PIAK & IKD',
            'profile_duties_item_3_desc'        => 'Pengelolaan jaringan data SIAK Terpusat dan aktivasi Identitas Kependudukan Digital.',

            // Maklumat Pelayanan Publik
            'profile_maklumat_tag'              => 'Maklumat Pelayanan Publik',
            'profile_maklumat_title'            => '"Dengan Ini, Kami Menyatakan Sanggup Menyenggarakan Pelayanan Sesuai Standar Pelayanan Yang Telah Ditetapkan"',
            'profile_maklumat_subtitle'         => 'Apabila tidak memenuhi janji ini, kami siap menerima sanksi sesuai dengan peraturan perundang-undangan yang berlaku.',
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

            // Section 6: Sambutan Pimpinan / Foto Kepala Dinas
            'leader_name'                       => 'nullable|string|max:255',
            'leader_title'                      => 'nullable|string|max:255',
            'leader_speech_title'               => 'nullable|string|max:255',
            'leader_speech_content'             => 'nullable|string|max:4000',
            'leader_photo'                      => 'nullable|string|max:500',

            // Section 7: Visi Misi Instansi
            'profile_visi_title'                => 'nullable|string|max:255',
            'profile_visi_text'                 => 'nullable|string|max:2000',
            'profile_misi_title'                => 'nullable|string|max:255',
            'profile_misi_items'                => 'nullable|string|max:4000',

            // Section 8: Tugas Pokok & Fungsi
            'profile_duties_title'              => 'nullable|string|max:255',
            'profile_duties_item_1_title'       => 'nullable|string|max:255',
            'profile_duties_item_1_desc'        => 'nullable|string|max:1000',
            'profile_duties_item_2_title'       => 'nullable|string|max:255',
            'profile_duties_item_2_desc'        => 'nullable|string|max:1000',
            'profile_duties_item_3_title'       => 'nullable|string|max:255',
            'profile_duties_item_3_desc'        => 'nullable|string|max:1000',

            // Section 9: Maklumat Pelayanan Publik
            'profile_maklumat_tag'              => 'nullable|string|max:255',
            'profile_maklumat_title'            => 'nullable|string|max:2000',
            'profile_maklumat_subtitle'         => 'nullable|string|max:2000',
        ]);

        if ($request->hasFile('leader_photo_file')) {
            $path = $request->file('leader_photo_file')->store('leader', 'public');
            $data['leader_photo'] = $path;
        }

        foreach ($data as $key => $val) {
            if ($key === 'mobile_service_selected_services') {
                $val = json_encode(array_map('intval', $val ?? []));
            } elseif (is_bool($val)) {
                $val = $val ? '1' : '0';
            } else {
                $val = (string) ($val ?? '');
            }

            $group = str_starts_with($key, 'profile_') ? 'profil_instansi' : (str_starts_with($key, 'leader_') ? 'pimpinan' : 'informasi_layanan');

            WebsiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $val,
                    'group' => $group,
                ]
            );
        }

        WebsiteSetting::clearCache();

        ActivityLogger::log("Memperbarui konfigurasi Informasi Layanan & Profil Instansi", null, 'update_informasi_layanan');

        return redirect()->back()->with('success', 'Konfigurasi Informasi Layanan & Profil Instansi berhasil diperbarui.');
    }
}
