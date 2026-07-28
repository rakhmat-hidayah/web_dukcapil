<?php

namespace Database\Seeders;

use App\Models\Innovation;
use Illuminate\Database\Seeder;

class InnovationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $innovations = [
            [
                'title' => 'MADA UDI (Masyarakat Sadar Adminduk Mandiri)',
                'slug' => 'mada-udi',
                'icon' => '🚐',
                'color' => '#4f46e5',
                'description' => 'Layanan jemput bola penerbitan dokumen administrasi kependudukan terpadu langsung ke desa-desa pelosok dan terpencil di Kabupaten Dompu.',
                'content' => "<p><strong>MADA UDI</strong> merupakan program inovasi unggulan Disdukcapil Kabupaten Dompu untuk menghadirkan pelayanan adminduk jemput bola langsung ke tengah-tengah pemukiman warga, khususnya wilayah yang sulit dijangkau transportasi publik.</p><p>Melalui armada mobil pelayanan keliling, petugas kami mendatangi desa-desa terjauh untuk melayani perekaman KTP-el secara langsung, pencetakan Kartu Keluarga (KK), pembuatan KIA, hingga pembuatan Akta Kelahiran/Kematian tanpa perlu warga datang dan mengantre di kantor dinas pusat.</p>",
                'youtube_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', // Placeholder video
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'SI-ALAN (Sistem Informasi Akta Online)',
                'slug' => 'si-alan',
                'icon' => '👶',
                'color' => '#10b981',
                'description' => 'Penerbitan akta kelahiran bayi yang baru lahir secara instan dan online melalui kerja sama langsung dengan Puskesmas dan Rumah Bersalin.',
                'content' => "<p>Inovasi <strong>SI-ALAN</strong> mempermudah orang tua mendapatkan Akta Kelahiran anak yang baru lahir secara instan. Disdukcapil Dompu telah mengintegrasikan sistem pelaporan kelahiran dengan fasilitas kesehatan seperti Rumah Sakit Umum Daerah (RSUD) Dompu, Puskesmas, dan klinik bersalin swasta.</p><p>Ketika bayi lahir, faskes langsung mengunggah surat keterangan kelahiran dan dokumen persyaratan orang tua ke sistem. Dokumen Akta Kelahiran dan KK baru yang sudah mencantumkan nama anak akan diterbitkan seketika sebelum ibu dan bayi diperbolehkan pulang.</p>",
                'youtube_url' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'PANDU CINTA (Pelayanan Terpadu Pengantin Baru)',
                'slug' => 'pandu-cinta',
                'icon' => '💍',
                'color' => '#ec4899',
                'description' => 'Layanan penerbitan 3 dokumen adminduk sekaligus (KK baru orang tua, KK baru mertua, dan KTP status kawin) bagi pasangan pengantin baru setelah akad nikah.',
                'content' => "<p>Inovasi <strong>PANDU CINTA</strong> dirancang khusus bekerja sama dengan Kantor Urusan Agama (KUA) di Kabupaten Dompu. Layanan ini memastikan pasangan pengantin baru langsung mendapatkan dokumen kependudukan dengan status terupdate sesaat setelah melangsungkan pernikahan.</p><p>Dokumen yang langsung didapatkan meliputi:\n<ol>\n<li>Kartu Keluarga (KK) baru untuk pasangan pengantin</li>\n<li>KK orang tua dan mertua yang telah disesuaikan (dikeluarkan anggotanya)</li>\n<li>KTP-el suami istri dengan status pernikahan yang baru (Kawin Tercatat)</li>\n</ol></p>",
                'youtube_url' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'KTP-EL GOES TO SCHOOL',
                'slug' => 'ktp-el-goes-to-school',
                'icon' => '🏫',
                'color' => '#f59e0b',
                'description' => 'Perekaman KTP elektronik jemput bola menyasar siswa/siswi sekolah menengah atas (SMA/SMK/MA) se-Kabupaten Dompu yang telah memasuki usia wajib KTP.',
                'content' => "<p>Program <strong>KTP-el Goes to School</strong> merupakan akselerasi kepemilikan identitas bagi pemula (usia 16-17 tahun). Petugas operator Disdukcapil Kabupaten Dompu menjadwalkan kunjungan berkala ke sekolah-sekolah menengah untuk melakukan perekaman retina mata, sidik jari, dan foto wajah para siswa di lingkungan sekolah mereka.</p><p>Hal ini meminimalisir dispensasi meninggalkan jam pelajaran sekolah bagi siswa hanya untuk pergi ke kantor Dukcapil untuk melakukan perekaman KTP-el.</p>",
                'youtube_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                'sort_order' => 4,
                'is_active' => true,
            ]
        ];

        foreach ($innovations as $inno) {
            Innovation::updateOrCreate(['slug' => $inno['slug']], $inno);
        }
    }
}
