<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;

class EnterpriseNavigationSeeder extends Seeder
{
    public function run(): void
    {
        Cache::forget('nav_menus');
        Cache::forget('nav_menus_v2');

        // Delete any old duplicate menus
        $otherHeader = Menu::where('location', 'header')->where('slug', '!=', 'navigasi-utama-header')->get();
        foreach ($otherHeader as $oldM) {
            MenuItem::where('menu_id', $oldM->id)->delete();
            $oldM->delete();
        }

        $otherFooter = Menu::where('location', 'footer')->where('slug', '!=', 'navigasi-footer-tautan-pintas')->get();
        foreach ($otherFooter as $oldM) {
            MenuItem::where('menu_id', $oldM->id)->delete();
            $oldM->delete();
        }

        // Header Menu (Clean 8-Category Architecture with Inovasi top-level)
        $headerMenu = Menu::updateOrCreate(
            ['location' => 'header', 'slug' => 'navigasi-utama-header'],
            ['name' => 'Navigasi Utama (Header)', 'is_active' => true]
        );

        // Clear existing header menu items
        MenuItem::where('menu_id', $headerMenu->id)->delete();

        // 1. Beranda
        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Beranda',
            'url' => '/',
            'target' => '_self',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 2. Profil Parent & Children
        $profilParent = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Profil',
            'url' => '/profil',
            'target' => '_self',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $profilChildren = [
            ['label' => 'Tentang Dinas', 'url' => '/profil#about'],
            ['label' => 'Sambutan Kepala Dinas', 'url' => '/profil#speech'],
            ['label' => 'Visi & Misi', 'url' => '/profil#vision_mission'],
            ['label' => 'Tugas dan Fungsi', 'url' => '/profil#duties'],
            ['label' => 'Struktur Organisasi', 'url' => '/profil/struktur-organisasi'],
            ['label' => 'Profil Pejabat', 'url' => '/profil/pejabat'],
            ['label' => 'Prestasi & Penghargaan', 'url' => '/profil#achievements'],
            ['label' => 'Maklumat Pelayanan', 'url' => '/profil#maklumat'],
        ];

        foreach ($profilChildren as $idx => $child) {
            MenuItem::create([
                'menu_id' => $headerMenu->id,
                'parent_id' => $profilParent->id,
                'label' => $child['label'],
                'url' => $child['url'],
                'target' => '_self',
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }

        // 3. Layanan Parent & Children
        $layananParent = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Layanan',
            'url' => '/layanan',
            'target' => '_self',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $layananChildren = [
            ['label' => 'Persyaratan Layanan', 'url' => '/layanan'],
            ['label' => 'Formulir Pendaftaran', 'url' => '/downloads'],
            ['label' => 'Survei Kepuasan Masyarakat (IKM)', 'url' => '/layanan/survei'],
            ['label' => 'Pengaduan Rakyat (Lapor)', 'url' => '/pengaduan'],
            ['label' => 'Tracking Permohonan', 'url' => '/pengaduan/lacak'],
        ];

        foreach ($layananChildren as $idx => $child) {
            MenuItem::create([
                'menu_id' => $headerMenu->id,
                'parent_id' => $layananParent->id,
                'label' => $child['label'],
                'url' => $child['url'],
                'target' => '_self',
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }

        // 4. PPID Parent & Children
        $ppidParent = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'PPID',
            'url' => '/ppid/pengertian',
            'target' => '_self',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $ppidChildren = [
            ['label' => 'Pengertian PPID', 'url' => '/ppid/pengertian'],
            ['label' => 'Profil PPID', 'url' => '/ppid/profil'],
            ['label' => 'Tugas & Fungsi PPID', 'url' => '/ppid/tugas-fungsi'],
            ['label' => 'SK PPID', 'url' => '/ppid/sk-ppid'],
            ['label' => 'Informasi Publik', 'url' => '/ppid/informasi-publik'],
            ['label' => 'Prosedur Layanan', 'url' => '/ppid/prosedur'],
            ['label' => 'Ajukan Permohonan Informasi', 'url' => '/ppid/permohonan'],
        ];

        foreach ($ppidChildren as $idx => $child) {
            MenuItem::create([
                'menu_id' => $headerMenu->id,
                'parent_id' => $ppidParent->id,
                'label' => $child['label'],
                'url' => $child['url'],
                'target' => '_self',
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }

        // 5. Demografi Parent & Children
        $demoParent = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Demografi',
            'url' => '/statistik-kependudukan',
            'target' => '_self',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $demoChildren = [
            ['label' => 'Dashboard Kependudukan', 'url' => '/statistik-kependudukan'],
            ['label' => 'Statistik Penduduk', 'url' => '/statistik-kependudukan#statistik'],
            ['label' => 'Dataset Kependudukan', 'url' => '/statistik-kependudukan#dataset'],
            ['label' => 'Buku Agregat', 'url' => '/downloads?category=agregat'],
        ];

        foreach ($demoChildren as $idx => $child) {
            MenuItem::create([
                'menu_id' => $headerMenu->id,
                'parent_id' => $demoParent->id,
                'label' => $child['label'],
                'url' => $child['url'],
                'target' => '_self',
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }

        // 6. Inovasi (Top Level)
        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Inovasi',
            'url' => '/inovasi',
            'target' => '_self',
            'sort_order' => 6,
            'is_active' => true,
        ]);

        // 7. Informasi Parent & Children
        $infoParent = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Informasi',
            'url' => '/news',
            'target' => '_self',
            'sort_order' => 7,
            'is_active' => true,
        ]);

        $infoChildren = [
            ['label' => 'Berita & Artikel', 'url' => '/news'],
            ['label' => 'Pusat Unduhan (Download)', 'url' => '/downloads'],
            ['label' => 'Galeri Foto & Video', 'url' => '/gallery'],
            ['label' => 'Tanya Jawab (FAQ)', 'url' => '/faq'],
        ];

        foreach ($infoChildren as $idx => $child) {
            MenuItem::create([
                'menu_id' => $headerMenu->id,
                'parent_id' => $infoParent->id,
                'label' => $child['label'],
                'url' => $child['url'],
                'target' => '_self',
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }

        // 8. Kontak
        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Kontak',
            'url' => '/contact',
            'target' => '_self',
            'sort_order' => 8,
            'is_active' => true,
        ]);

        // Footer Menu
        $footerMenu = Menu::updateOrCreate(
            ['location' => 'footer'],
            ['name' => 'Navigasi Footer (Tautan Pintas)', 'slug' => 'navigasi-footer-tautan-pintas', 'is_active' => true]
        );

        MenuItem::where('menu_id', $footerMenu->id)->delete();

        $footerItems = [
            ['label' => 'Beranda', 'url' => '/'],
            ['label' => 'Profil Dinas', 'url' => '/profil'],
            ['label' => 'Persyaratan Layanan', 'url' => '/layanan'],
            ['label' => 'Survei Kepuasan (IKM)', 'url' => '/layanan/survei'],
            ['label' => 'Layanan PPID', 'url' => '/ppid/pengertian'],
            ['label' => 'Statistik Demografi', 'url' => '/statistik-kependudukan'],
            ['label' => 'Inovasi Pelayanan', 'url' => '/inovasi'],
            ['label' => 'Berita Terbaru', 'url' => '/news'],
            ['label' => 'Download Formulir', 'url' => '/downloads'],
            ['label' => 'Kontak Kami', 'url' => '/contact'],
        ];

        foreach ($footerItems as $idx => $item) {
            MenuItem::create([
                'menu_id' => $footerMenu->id,
                'label' => $item['label'],
                'url' => $item['url'],
                'target' => '_self',
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }
    }
}
