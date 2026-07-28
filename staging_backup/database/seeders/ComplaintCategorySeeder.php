<?php

namespace Database\Seeders;

use App\Models\ComplaintCategory;
use Illuminate\Database\Seeder;

class ComplaintCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'KTP & Kartu Keluarga',
                'slug' => 'ktp-kk',
                'icon' => '🪪',
                'color' => '#3b82f6', // Blue
                'description' => 'Aduan terkait keterlambatan pencetakan, kesalahan data KTP, atau masalah KK.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Akta Catatan Sipil',
                'slug' => 'akta-catatan-sipil',
                'icon' => '📜',
                'color' => '#10b981', // Emerald
                'description' => 'Aduan pengurusan Akta Kelahiran, Akta Kematian, Akta Kawin, atau Akta Cerai.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'KIA & Pindah Datang',
                'slug' => 'kia-pindah-datang',
                'icon' => '🧒',
                'color' => '#8b5cf6', // Violet
                'description' => 'Pelaporan permohonan KIA, surat pindah daerah, atau pendatang baru.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Sikap & Pelayanan Petugas',
                'slug' => 'pelayanan-petugas',
                'icon' => '👥',
                'color' => '#f59e0b', // Amber
                'description' => 'Aduan terhadap perlakuan tidak ramah, pungutan liar, atau loket kosong.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Masalah Website & Sistem',
                'slug' => 'website-sistem',
                'icon' => '💻',
                'color' => '#ef4444', // Red
                'description' => 'Kendala akses aplikasi online, eror saat mendaftar, atau link rusak.',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            ComplaintCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
