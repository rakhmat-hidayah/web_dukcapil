<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile\ProfileSection;
use App\Models\Profile\ProfileSectionSetting;
use App\Models\Profile\Official;
use App\Models\Profile\OfficialEducation;
use App\Models\Profile\OfficialHistory;
use App\Models\Profile\OfficialAchievement;
use App\Models\Profile\OfficialSocialLink;
use App\Models\Profile\OrganizationPosition;
use App\Models\Profile\OrganizationNode;
use Illuminate\Support\Str;

class EnterpriseProfileSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Profile Sections Registry
        $sections = [
            ['key' => 'hero', 'name' => 'Hero Banner Profil', 'description' => 'Header banner utama profil instansi', 'sort' => 1],
            ['key' => 'about', 'name' => 'Tentang Dinas', 'description' => 'Profil umum dan sejarah singkat Dinas Dukcapil', 'sort' => 2],
            ['key' => 'speech', 'name' => 'Sambutan Kepala Dinas', 'description' => 'Pesan dan arahan dari Kepala Dinas', 'sort' => 3],
            ['key' => 'vision_mission', 'name' => 'Visi & Misi', 'description' => 'Visi dan Misi Pelayanan Dukcapil Dompu', 'sort' => 4],
            ['key' => 'duties', 'name' => 'Tugas & Fungsi', 'description' => 'Tugas pokok dan fungsi organisasi', 'sort' => 5],
            ['key' => 'org_chart', 'name' => 'Struktur Organisasi', 'description' => 'Bagan pohon organisasi interaktif', 'sort' => 6],
            ['key' => 'officials', 'name' => 'Profil Pejabat Utama', 'description' => 'Direktori jajaran pimpinan instansi', 'sort' => 7],
            ['key' => 'achievements', 'name' => 'Prestasi & Penghargaan', 'description' => 'Daftar penghargaan dan pencapaian instansi', 'sort' => 8],
            ['key' => 'timeline', 'name' => 'Timeline Jejak Langkah', 'description' => 'Lini masa perkembangan dinas dari masa ke masa', 'sort' => 9],
            ['key' => 'gallery', 'name' => 'Galeri Foto & Video', 'description' => 'Dokumentasi kegiatan dan fasilitas layanan', 'sort' => 10],
            ['key' => 'maklumat', 'name' => 'Maklumat Pelayanan', 'description' => 'Pernyataan janji pelayanan publik', 'sort' => 11],
            ['key' => 'contact', 'name' => 'Kontak & Lokasi Kantor', 'description' => 'Alamat kantor, nomor telepon, dan peta lokasi', 'sort' => 12],
        ];

        foreach ($sections as $s) {
            $sec = ProfileSection::updateOrCreate(
                ['key' => $s['key']],
                [
                    'name' => $s['name'],
                    'description' => $s['description'],
                    'is_enabled' => true,
                    'sort_order' => $s['sort'],
                ]
            );

            ProfileSectionSetting::updateOrCreate(
                ['section_id' => $sec->id],
                [
                    'layout_type' => 'glassmorphism',
                    'bg_color' => 'transparent',
                    'animation_type' => 'fade-up',
                    'visible_desktop' => true,
                    'visible_tablet' => true,
                    'visible_mobile' => true,
                    'content_data' => [
                        'title' => $s['name'],
                        'subtitle' => $s['description'],
                    ],
                ]
            );
        }

        // 2. Seed Master Official Directory
        $kadis = Official::updateOrCreate(
            ['slug' => 'drs-abd-najib'],
            [
                'name' => 'Drs. Abd. Najib',
                'nip' => '19680512 199303 1 005',
                'position_title' => 'Kepala Dinas Kependudukan dan Pencatatan Sipil',
                'rank_golongan' => 'Pembina Utama Muda / IV c',
                'department' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu',
                'biography' => 'Drs. Abd. Najib memimpin Dinas Dukcapil Kabupaten Dompu dengan komitmen mewujudkan tertib administrasi kependudukan digital yang inklusif, cepat, dan transparan bagi seluruh warga Kabupaten Dompu.',
                'main_duties' => 'Memimpin, merumuskan, membinan, dan mengevaluasi pelaksanaan kebijakan daerah di bidang pendaftaran penduduk, pencatatan sipil, serta pengelolaan informasi administrasi kependudukan.',
                'office_address' => 'Jl. Bhayangkara No. 01, Kabupaten Dompu, NTB',
                'phone' => '(0373) 21124',
                'email' => 'kadis@dukcapil.dompukab.go.id',
                'office_hours' => 'Senin - Jumat | 07:30 - 16:00 WITA',
                'status' => 'active',
                'sort_order' => 1,
            ]
        );

        OfficialEducation::updateOrCreate(
            ['official_id' => $kadis->id, 'degree' => 'S1 Ilmu Pemerintahan'],
            ['institution' => 'Universitas Gadjah Mada', 'start_year' => 1987, 'end_year' => 1992]
        );

        OfficialHistory::updateOrCreate(
            ['official_id' => $kadis->id, 'position_title' => 'Kepala Dinas Dukcapil'],
            ['organization' => 'Pemerintah Kabupaten Dompu', 'start_year' => 2020, 'end_year' => 2026, 'description' => 'Memimpin transformasi digital layanan adminduk di Kabupaten Dompu.']
        );

        OfficialAchievement::updateOrCreate(
            ['official_id' => $kadis->id, 'title' => 'Penghargaan Pelayanan Publik Kategori A- (Sangat Baik)'],
            ['issuer' => 'Kementerian PAN-RB', 'year' => 2023, 'description' => 'Penghargaan atas komitmen efisiensi dan transparansi pelayanan adminduk.']
        );

        OfficialSocialLink::updateOrCreate(
            ['official_id' => $kadis->id, 'platform' => 'facebook'],
            ['url' => 'https://facebook.com/dukcapildompu', 'handle' => '@dukcapildompu']
        );

        $sekdis = Official::updateOrCreate(
            ['slug' => 'samsul-bahri-se'],
            [
                'name' => 'Samsul Bahri, S.E.',
                'nip' => '19740915 199903 1 002',
                'position_title' => 'Sekretaris Dinas',
                'rank_golongan' => 'Pembina / IV a',
                'department' => 'Sekretariat Dinas Dukcapil',
                'biography' => 'Mengordinasikan perencanaan program, pengelolaan keuangan, dan pembinaan kepegawaian internal dinas.',
                'main_duties' => 'Membantu Kepala Dinas dalam mengoordinasikan perencanaan, pembinaan administrasi, umum, kepegawaian, dan keuangan.',
                'office_address' => 'Jl. Bhayangkara No. 01, Dompu',
                'phone' => '(0373) 21124',
                'email' => 'sekretaris@dukcapil.dompukab.go.id',
                'status' => 'active',
                'sort_order' => 2,
            ]
        );

        $kabidDafduk = Official::updateOrCreate(
            ['slug' => 'h-ahmad-s-sos'],
            [
                'name' => 'H. Ahmad, S.Sos.',
                'nip' => '19780310 200501 1 008',
                'position_title' => 'Kabid Pelayanan Pendaftaran Penduduk',
                'rank_golongan' => 'Penata Tingkat I / III d',
                'department' => 'Bidang Pendaftaran Penduduk',
                'biography' => 'Mengelola pelayanan perekaman KTP-El, penerbitan Kartu Keluarga, dan Kartu Identitas Anak (KIA).',
                'status' => 'active',
                'sort_order' => 3,
            ]
        );

        $kabidCapil = Official::updateOrCreate(
            ['slug' => 'mariam-s-ip'],
            [
                'name' => 'Mariam, S.IP.',
                'nip' => '19810412 200604 2 011',
                'position_title' => 'Kabid Pelayanan Pencatatan Sipil',
                'rank_golongan' => 'Penata Tingkat I / III d',
                'department' => 'Bidang Pencatatan Sipil',
                'biography' => 'Mengelola penerbitan Akta Kelahiran, Akta Kematian, Akta Perkawinan, dan Akta Perceraian.',
                'status' => 'active',
                'sort_order' => 4,
            ]
        );

        $kabidPiak = Official::updateOrCreate(
            ['slug' => 'irwan-s-kom'],
            [
                'name' => 'Irwan, S.Kom.',
                'nip' => '19850620 200902 1 003',
                'position_title' => 'Kabid Pengelolaan Informasi Administrasi Kependudukan (PIAK)',
                'rank_golongan' => 'Penata / III c',
                'department' => 'Bidang PIAK',
                'biography' => 'Mengelola database SIAK Terpusat, keamanan jaringan data, dan layanan Identitas Kependudukan Digital (IKD).',
                'status' => 'active',
                'sort_order' => 5,
            ]
        );

        // 3. Seed Positions Catalog
        $posKadis = OrganizationPosition::updateOrCreate(
            ['code' => 'KADIS'],
            ['title' => 'Kepala Dinas', 'rank_level' => 'Eselon II.b', 'department' => 'Pimpinan']
        );
        $posSekdis = OrganizationPosition::updateOrCreate(
            ['code' => 'SEKDIS'],
            ['title' => 'Sekretaris Dinas', 'rank_level' => 'Eselon III.a', 'department' => 'Sekretariat']
        );
        $posDafduk = OrganizationPosition::updateOrCreate(
            ['code' => 'KABID_DAFDUK'],
            ['title' => 'Kabid Pendaftaran Penduduk', 'rank_level' => 'Eselon III.b', 'department' => 'Bidang Pendaftaran Penduduk']
        );
        $posCapil = OrganizationPosition::updateOrCreate(
            ['code' => 'KABID_CAPIL'],
            ['title' => 'Kabid Pencatatan Sipil', 'rank_level' => 'Eselon III.b', 'department' => 'Bidang Pencatatan Sipil']
        );
        $posPiak = OrganizationPosition::updateOrCreate(
            ['code' => 'KABID_PIAK'],
            ['title' => 'Kabid PIAK', 'rank_level' => 'Eselon III.b', 'department' => 'Bidang PIAK']
        );

        // 4. Seed Organization Tree Nodes
        $nodeRoot = OrganizationNode::updateOrCreate(
            ['node_title' => 'Kepala Dinas'],
            [
                'position_id' => $posKadis->id,
                'official_id' => $kadis->id,
                'parent_id' => null,
                'color_code' => '#1e3a8a',
                'icon' => 'Award',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $nodeSekdis = OrganizationNode::updateOrCreate(
            ['node_title' => 'Sekretaris Dinas'],
            [
                'position_id' => $posSekdis->id,
                'official_id' => $sekdis->id,
                'parent_id' => $nodeRoot->id,
                'color_code' => '#0284c7',
                'icon' => 'Briefcase',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        OrganizationNode::updateOrCreate(
            ['node_title' => 'Kabid Pendaftaran Penduduk'],
            [
                'position_id' => $posDafduk->id,
                'official_id' => $kabidDafduk->id,
                'parent_id' => $nodeSekdis->id,
                'color_code' => '#059669',
                'icon' => 'UserCheck',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        OrganizationNode::updateOrCreate(
            ['node_title' => 'Kabid Pencatatan Sipil'],
            [
                'position_id' => $posCapil->id,
                'official_id' => $kabidCapil->id,
                'parent_id' => $nodeSekdis->id,
                'color_code' => '#d97706',
                'icon' => 'FileText',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        OrganizationNode::updateOrCreate(
            ['node_title' => 'Kabid PIAK'],
            [
                'position_id' => $posPiak->id,
                'official_id' => $kabidPiak->id,
                'parent_id' => $nodeSekdis->id,
                'color_code' => '#7c3aed',
                'icon' => 'Cpu',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );
    }
}
