<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pages Seed
        $pages = [
            [
                'title' => 'Profil Dinas Kependudukan dan Pencatatan Sipil',
                'content' => '<h2>Tentang Kami</h2><p>Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu merupakan unsur pelaksana urusan pemerintahan bidang kependudukan dan pencatatan sipil di tingkat daerah.</p>',
                'template' => 'default',
                'status' => 'published',
            ],
            [
                'title' => 'Visi dan Misi',
                'content' => '<h2>Visi</h2><p>Terwujudnya Pelayanan Kependudukan dan Pencatatan Sipil yang Prima, Akurat, dan Berkelanjutan di Kabupaten Dompu.</p><h2>Misi</h2><ul><li>Meningkatkan kualitas pelayanan administrasi kependudukan.</li><li>Mewujudkan validitas database kependudukan nasional.</li></ul>',
                'template' => 'full-width',
                'status' => 'published',
            ],
            [
                'title' => 'Standar Pelayanan Publik',
                'content' => '<h2>Jenis Pelayanan</h2><p>Dukcapil Kabupaten Dompu melayani perekaman KTP-el, pembuatan Kartu Identitas Anak (KIA), Kartu Keluarga (KK), Akta Kelahiran, Akta Kematian, dan Surat Pindah.</p>',
                'template' => 'default',
                'status' => 'published',
            ],
        ];

        foreach ($pages as $p) {
            DB::table('pages')->updateOrInsert(
                ['slug' => Str::slug($p['title'])],
                [
                    'user_id' => 1,
                    'title' => $p['title'],
                    'content' => $p['content'],
                    'template' => $p['template'],
                    'status' => $p['status'],
                    'show_in_menu' => true,
                    'sort_order' => 1,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. News Categories & Articles Seed
        $catId = DB::table('news_categories')->updateOrInsert(
            ['slug' => 'info-kependudukan'],
            [
                'name' => 'Info Kependudukan',
                'color' => '#10b981',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $cat = DB::table('news_categories')->where('slug', 'info-kependudukan')->first();

        DB::table('news')->updateOrInsert(
            ['slug' => 'perekaman-ktp-el-keliling-desa'],
            [
                'user_id' => 1,
                'news_category_id' => $cat ? $cat->id : null,
                'title' => 'Jadwal Perekaman KTP-el Keliling Masuk Desa Tahun 2026',
                'excerpt' => 'Dukcapil Dompu menyelenggarakan layanan jemput bola perekaman KTP-el keliling ke seluruh desa.',
                'content' => '<p>Guna mempercepat cakupan kepemilikan KTP elektronik, Disdukcapil Kabupaten Dompu merilis jadwal perekaman keliling...</p>',
                'status' => 'published',
                'is_featured' => true,
                'is_breaking' => false,
                'view_count' => 124,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 3. Announcements Seed
        DB::table('announcements')->updateOrInsert(
            ['title' => 'Pemberitahuan Cuti Bersama Hari Raya'],
            [
                'user_id' => 1,
                'content' => 'Sehubungan dengan Hari Raya Nasional, loket pelayanan fisik Disdukcapil Kabupaten Dompu ditutup sementara dan dibuka kembali pada tanggal berikutnya. Pelayanan online tetap berjalan via WhatsApp.',
                'priority' => 'high',
                'is_pinned' => true,
                'is_popup' => false,
                'is_ticker' => true,
                'status' => 'published',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 4. Banners Seed
        DB::table('banners')->updateOrInsert(
            ['title' => 'Sistem Pendaftaran KIA Online Kabupaten Dompu'],
            [
                'subtitle' => 'Buat KIA anak Anda lebih cepat dan mudah dari rumah.',
                'image' => 'banners/banner-1.jpg',
                'type' => 'hero',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 5. Gallery Seed
        DB::table('gallery_albums')->updateOrInsert(
            ['slug' => 'kegiatan-sosialisasi-kia-2026'],
            [
                'title' => 'Kegiatan Sosialisasi KIA 2026',
                'description' => 'Dokumentasi acara sosialisasi Kartu Identitas Anak di sekolah dasar Kecamatan Dompu.',
                'type' => 'photo',
                'is_published' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 6. Downloads Seed
        DB::table('download_categories')->updateOrInsert(
            ['slug' => 'formulir-layanan'],
            [
                'name' => 'Formulir Layanan',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $dlCat = DB::table('download_categories')->where('slug', 'formulir-layanan')->first();

        DB::table('downloads')->updateOrInsert(
            ['title' => 'Formulir Surat Kuasa Pendaftaran Penduduk F-1.03'],
            [
                'user_id' => 1,
                'download_category_id' => $dlCat ? $dlCat->id : null,
                'description' => 'Unduh formulir resmi F-1.03 surat kuasa pengurusan administrasi kependudukan keluarga.',
                'file_path' => 'downloads/f-1.03.pdf',
                'file_name' => 'formulir_f1_03.pdf',
                'file_type' => 'pdf',
                'file_size' => 143520,
                'status' => 'published',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 7. FAQ Seed
        $faqs = [
            [
                'question' => 'Apakah mengurus KTP-el dipungut biaya?',
                'answer' => 'Seluruh pelayanan kependudukan dan pencatatan sipil di Dinas Dukcapil Kabupaten Dompu adalah GRATIS (Rp 0,-) tanpa biaya apapun.',
                'category' => 'Layanan KTP-el',
            ],
            [
                'question' => 'Berapa hari proses pembuatan Akta Kelahiran?',
                'answer' => 'Sesuai dengan standar operasional pelayanan (SOP), penyelesaian Akta Kelahiran memakan waktu maksimal 3 hari kerja sejak berkas dinyatakan lengkap.',
                'category' => 'Layanan Akta',
            ]
        ];

        foreach ($faqs as $f) {
            DB::table('faqs')->updateOrInsert(
                ['question' => $f['question']],
                [
                    'answer' => $f['answer'],
                    'category' => $f['category'],
                    'sort_order' => 1,
                    'is_published' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 8. Navigation Menus Seed
        DB::table('menus')->updateOrInsert(
            ['slug' => 'menu-utama'],
            [
                'name' => 'Menu Utama',
                'location' => 'header',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $menu = DB::table('menus')->where('slug', 'menu-utama')->first();

        if ($menu) {
            $menuItems = [
                ['label' => 'Beranda', 'url' => '/'],
                ['label' => 'Profil Instansi', 'url' => '/profil'],
                ['label' => 'Berita', 'url' => '/news'],
                ['label' => 'Pengumuman', 'url' => '/announcements'],
                ['label' => 'Unduhan', 'url' => '/downloads'],
            ];

            foreach ($menuItems as $idx => $item) {
                DB::table('menu_items')->updateOrInsert(
                    ['menu_id' => $menu->id, 'label' => $item['label']],
                    [
                        'url' => $item['url'],
                        'target' => '_self',
                        'sort_order' => $idx + 1,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
