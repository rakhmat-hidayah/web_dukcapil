<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebsiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // --- SEO ---
            ['key' => 'seo_title', 'value' => 'Dukcapil Kabupaten Dompu | Official Website', 'group' => 'seo'],
            ['key' => 'seo_description', 'value' => 'Website Resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Layanan Informasi Kependudukan, Dokumen Kependudukan dan Pengaduan Masyarakat.', 'group' => 'seo'],
            ['key' => 'seo_keywords', 'value' => 'dukcapil, dompu, kependudukan, pencatatan sipil, ektp, kia, kartu keluarga, akta kelahiran', 'group' => 'seo'],
            ['key' => 'seo_canonical', 'value' => 'https://dukcapil.dompukab.go.id', 'group' => 'seo'],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\nSitemap: https://dukcapil.dompukab.go.id/sitemap.xml", 'group' => 'seo'],

            // --- SMTP ---
            ['key' => 'mail_host', 'value' => '127.0.0.1', 'group' => 'smtp'],
            ['key' => 'mail_port', 'value' => '2525', 'group' => 'smtp'],
            ['key' => 'mail_username', 'value' => null, 'group' => 'smtp'],
            ['key' => 'mail_password', 'value' => null, 'group' => 'smtp'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'group' => 'smtp'],
            ['key' => 'mail_from_address', 'value' => 'noreply@dompukab.go.id', 'group' => 'smtp'],
            ['key' => 'mail_from_name', 'value' => 'Dukcapil Dompu', 'group' => 'smtp'],

            // --- System Settings ---
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system'],
            ['key' => 'google_analytics_code', 'value' => null, 'group' => 'system'],
            ['key' => 'enable_comments', 'value' => '0', 'group' => 'system'],

            // --- External Services (SANAI Online CTA) ---
            ['key' => 'sanai_name', 'value' => 'SANAI Online', 'group' => 'external_services'],
            ['key' => 'sanai_url', 'value' => 'https://sanai-dukcapil.dompukab.go.id', 'group' => 'external_services'],
            ['key' => 'sanai_description', 'value' => 'Portal Layanan Administrasi Kependudukan Kabupaten Dompu', 'group' => 'external_services'],
            ['key' => 'sanai_button_label', 'value' => 'SANAI Online', 'group' => 'external_services'],
            ['key' => 'sanai_is_active', 'value' => '1', 'group' => 'external_services'],
            ['key' => 'sanai_open_new_tab', 'value' => '1', 'group' => 'external_services'],
            ['key' => 'sanai_display_navbar', 'value' => '1', 'group' => 'external_services'],
            ['key' => 'sanai_display_homepage', 'value' => '1', 'group' => 'external_services'],
            ['key' => 'sanai_display_footer', 'value' => '1', 'group' => 'external_services'],

            // --- Office Contact & Map Location ---
            ['key' => 'office_address', 'value' => 'Jl. Bhayangkara No. 01, Kel. Bada, Dompu, NTB 84211', 'group' => 'office_contact'],
            ['key' => 'office_phone', 'value' => '(0373) 21124', 'group' => 'office_contact'],
            ['key' => 'office_email', 'value' => 'dukcapil@dompukab.go.id', 'group' => 'office_contact'],
            ['key' => 'office_work_hours', 'value' => 'Senin - Kamis: 08:00 - 16:00 WITA | Jumat: 08:00 - 11:30 WITA', 'group' => 'office_contact'],
            ['key' => 'whatsapp_number', 'value' => '628111222333', 'group' => 'office_contact'],
            ['key' => 'map_latitude', 'value' => '-8.536780', 'group' => 'office_contact'],
            ['key' => 'map_longitude', 'value' => '118.461295', 'group' => 'office_contact'],
        ];

        foreach ($settings as $setting) {
            DB::table('website_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
