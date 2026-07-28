<?php

namespace Database\Seeders;

use App\Models\ServiceRequirement;
use Illuminate\Database\Seeder;

class ServiceRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'title' => 'Perekaman KTP Elektronik',
                'slug' => 'perekaman-ktp-elektronik',
                'icon' => '🪪',
                'color' => '#2563eb',
                'description' => 'Persyaratan pengajuan perekaman dan pencetakan Kartu Tanda Penduduk Elektronik (KTP-El) baru maupun ganti hilang/rusak.',
                'requirements' => "<ul>\n<li>Telah berusia 17 tahun atau sudah kawin/pernah kawin</li>\n<li>Fotokopi Kartu Keluarga (KK) terbaru</li>\n<li>Surat Keterangan Kehilangan dari Kepolisian (untuk KTP-El ganti karena hilang)</li>\n<li>KTP-El fisik yang rusak (untuk KTP-El ganti karena rusak)</li>\n<li>Surat Keterangan Pindah (SKPWNI) jika merupakan warga pindahan baru</li>\n</ul>",
                'processing_time' => '1 Hari Kerja',
                'cost' => 'Gratis / Rp 0',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Kartu Identitas Anak (KIA)',
                'slug' => 'kartu-identitas-anak',
                'icon' => '🧒',
                'color' => '#8b5cf6',
                'description' => 'Persyaratan penerbitan identitas kependudukan bagi anak berusia 0 - 17 tahun kurang satu hari.',
                'requirements' => "<ul>\n<li>Fotokopi Akta Kelahiran Anak</li>\n<li>Kartu Keluarga (KK) asli orang tua</li>\n<li>KTP asli kedua orang tua</li>\n<li>Pas foto anak ukuran 2x3 sebanyak 2 lembar (untuk anak usia 5-17 tahun, warna latar menyesuaikan tahun lahir ganjil/genap)</li>\n<li>KIA lama jika ganti rusak/hilang</li>\n</ul>",
                'processing_time' => '1 Hari Kerja',
                'cost' => 'Gratis / Rp 0',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Akta Kelahiran & Kematian',
                'slug' => 'akta-kelahiran-kematian',
                'icon' => '📜',
                'color' => '#10b981',
                'description' => 'Persyaratan pengurusan pencatatan sipil penerbitan Akta Kelahiran baru bagi bayi dan Akta Kematian bagi warga yang telah meninggal.',
                'requirements' => "<h3>Persyaratan Akta Kelahiran:</h3>\n<ul>\n<li>Surat Keterangan Kelahiran dari Penolong Kelahiran (Bidan/Rumah Sakit/Puskesmas)</li>\n<li>Fotokopi Buku Nikah / Akta Perkawinan Orang Tua</li>\n<li>Fotokopi KTP kedua orang tua dan 2 orang saksi</li>\n<li>Kartu Keluarga (KK) asli dimana anak akan didaftarkan</li>\n</ul>\n\n<h3>Persyaratan Akta Kematian:</h3>\n<ul>\n<li>Surat Keterangan Kematian dari Dokter/Rumah Sakit atau Kepala Desa/Lurah</li>\n<li>KK dan KTP asli yang meninggal</li>\n<li>Fotokopi KTP pelapor dan 2 orang saksi</li>\n</ul>",
                'processing_time' => '1 Hari Kerja',
                'cost' => 'Gratis / Rp 0',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Kartu Keluarga (KK)',
                'slug' => 'kartu-keluarga',
                'icon' => '🏠',
                'color' => '#f59e0b',
                'description' => 'Persyaratan penerbitan, penggantian, pemecahan, atau perubahan elemen data pada Kartu Keluarga kependudukan.',
                'requirements' => "<ul>\n<li>KK lama (asli)</li>\n<li>Surat Nikah / Akta Cerai (bagi yang baru menikah atau bercerai)</li>\n<li>Surat Keterangan Pindah (SKPWNI) bagi anggota keluarga yang pindah masuk</li>\n<li>Akta Kelahiran / Ijazah pendukung jika ada penambahan nama/perubahan elemen data pendidikan</li>\n<li>Surat Keterangan Kematian (jika ada pengurangan anggota keluarga meninggal)</li>\n</ul>",
                'processing_time' => '1 Hari Kerja',
                'cost' => 'Gratis / Rp 0',
                'sort_order' => 4,
                'is_active' => true,
            ]
        ];

        foreach ($services as $service) {
            ServiceRequirement::updateOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
