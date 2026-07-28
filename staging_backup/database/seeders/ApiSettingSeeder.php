<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'api_rate_limit_public',
                'value' => '100',
            ],
            [
                'key' => 'api_rate_limit_partner',
                'value' => '1000',
            ],
            [
                'key' => 'api_terms_of_service',
                'value' => "# Ketentuan Layanan Integrasi REST API Dukcapil Dompu\n\nSelamat datang di Platform API Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Dengan menggunakan layanan API kami, Anda menyetujui ketentuan berikut:\n\n1. **Ketentuan Penggunaan Data Pemerintah**: Data yang diperoleh melalui API ini hanya boleh digunakan untuk tujuan pelayanan publik dan administrasi yang sah. Dilarang keras menyebarluaskan data kependudukan pribadi secara bebas atau memperjualbelikannya.\n2. **Hak Cipta**: Hak Cipta atas data kependudukan dan dokumentasi API tetap berada pada Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu.\n3. **Keamanan**: Anda berkewajiban menjaga kerahasiaan API Key Anda. Penyalahgunaan API Key akibat kelalaian Anda sepenuhnya menjadi tanggung jawab institusi Anda.\n4. **Batasan Penggunaan (Rate Limit)**: Pengguna berkewajiban mematuhi batasan kuota request yang ditetapkan demi kestabilan sistem.\n5. **Pelepasan Tanggung Jawab (Disclaimer)**: Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu tidak bertanggung jawab atas kerugian langsung maupun tidak langsung yang timbul akibat gangguan teknis layanan API.",
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('api_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Seed default API keys for testing & local development
        DB::table('api_keys')->updateOrInsert(
            ['client_name' => 'Internal System'],
            [
                'api_key' => 'dkp_internal_system_access_token_2026',
                'rate_limit_per_hour' => 999999, // practically unlimited
                'is_active' => true,
                'permissions' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('api_keys')->updateOrInsert(
            ['client_name' => 'Dinas Kominfo Kabupaten Dompu'],
            [
                'api_key' => 'dkp_dinas_kominfo_access_token_2026',
                'rate_limit_per_hour' => 1500,
                'is_active' => true,
                'permissions' => json_encode(['news', 'demographics']),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
