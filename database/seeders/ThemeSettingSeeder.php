<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // --- Colors: Primary ---
            ['key' => 'primary_50', 'value' => '#f0f7ff', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_100', 'value' => '#e0effe', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_200', 'value' => '#bae0fd', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_300', 'value' => '#7cc7fc', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_400', 'value' => '#38abfa', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_500', 'value' => '#0e91eb', 'type' => 'color', 'group' => 'colors'], // primary color (standard)
            ['key' => 'primary_600', 'value' => '#0274cb', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_700', 'value' => '#035ca3', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_800', 'value' => '#074f87', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_900', 'value' => '#0c4270', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'primary_950', 'value' => '#082a4a', 'type' => 'color', 'group' => 'colors'],

            // --- Colors: Secondary (Dark Navy/Zinc style) ---
            ['key' => 'secondary_50', 'value' => '#fafaf9', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_100', 'value' => '#f5f5f4', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_200', 'value' => '#e7e5e4', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_300', 'value' => '#d6d3d1', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_400', 'value' => '#a8a29e', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_500', 'value' => '#78716c', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_600', 'value' => '#57534e', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_700', 'value' => '#44403c', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_800', 'value' => '#292524', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_900', 'value' => '#1c1917', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'secondary_950', 'value' => '#0c0a09', 'type' => 'color', 'group' => 'colors'],

            // --- Colors: Accent (Gold/Amber style) ---
            ['key' => 'accent_50', 'value' => '#fffbeb', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_100', 'value' => '#fef3c7', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_200', 'value' => '#fde047', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_300', 'value' => '#facc15', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_400', 'value' => '#eab308', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_500', 'value' => '#ca8a04', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_600', 'value' => '#a16207', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_700', 'value' => '#854d0e', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_800', 'value' => '#713f12', 'type' => 'color', 'group' => 'colors'],
            ['key' => 'accent_900', 'value' => '#451a03', 'type' => 'color', 'group' => 'colors'],

            // --- Typography ---
            ['key' => 'font_family', 'value' => 'Inter', 'type' => 'font', 'group' => 'typography'],

            // --- Branding Images (CMS Uploadable) ---
            ['key' => 'site_logo', 'value' => null, 'type' => 'image', 'group' => 'brand'],
            ['key' => 'site_logo_dark', 'value' => null, 'type' => 'image', 'group' => 'brand'],
            ['key' => 'site_favicon', 'value' => null, 'type' => 'image', 'group' => 'brand'],
            ['key' => 'site_hero_bg', 'value' => null, 'type' => 'image', 'group' => 'brand'],

            // --- Brand text / metadata ---
            ['key' => 'site_title', 'value' => 'Dukcapil Dompu', 'type' => 'text', 'group' => 'brand'],
            ['key' => 'site_tagline', 'value' => 'Pelayanan Cepat, Tepat, dan Gratis', 'type' => 'text', 'group' => 'brand'],
            
            // --- Footer Info ---
            ['key' => 'footer_about', 'value' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu menyediakan pelayanan pendaftaran penduduk dan pencatatan sipil yang terintegrasi, transparan, dan akuntabel.', 'type' => 'textarea', 'group' => 'footer'],
            ['key' => 'footer_address', 'value' => 'Jl. Bhayangkara No. 05, Dompu, NTB', 'type' => 'text', 'group' => 'footer'],
            ['key' => 'footer_copyright', 'value' => '© 2026 Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. All Rights Reserved.', 'type' => 'text', 'group' => 'footer'],

            // --- Social Links ---
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/dukcapil.dompu', 'type' => 'text', 'group' => 'footer'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/dukcapil.dompu', 'type' => 'text', 'group' => 'footer'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com', 'type' => 'text', 'group' => 'footer'],
            ['key' => 'contact_whatsapp', 'value' => '+628111222333', 'type' => 'text', 'group' => 'footer'],
            ['key' => 'contact_email', 'value' => 'dukcapil@dompukab.go.id', 'type' => 'text', 'group' => 'footer'],
        ];

        // Ensure primary_500 is inserted as a fix (the list has 505 as typo, let's fix it here)
        foreach ($settings as &$item) {
            if ($item['key'] === 'primary_505') {
                $item['key'] = 'primary_500';
            }
        }

        foreach ($settings as $setting) {
            DB::table('theme_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
