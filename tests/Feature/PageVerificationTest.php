<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\News;
use App\Models\Page;
use App\Models\ServiceRequirement;
use App\Models\Innovation;
use Illuminate\Support\Facades\Cache;

class PageVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'dukcapil_dompukab']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
        \Illuminate\Support\Facades\DB::purge();
        \Illuminate\Support\Facades\DB::reconnect();
        Cache::forget('nav_menus');
        Cache::forget('published_pages');
    }

    public function test_all_public_pages_render_successfully(): void
    {
        $newsSlug = News::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->value('slug') ?? 'koordinasi-dukcapil-dompu-terait-pelaksanaan-pemutakhiran-data-pemilih-untuk-pemilu-2024-di-lapas-kabupaten-dompu';
        $pageSlug = Page::where('status', 'published')->value('slug') ?? Page::value('slug') ?? 'profil-dinas-kependudukan-dan-pencatatan-sipil';
        $serviceSlug = ServiceRequirement::value('slug') ?? 'ktp-el';
        $innovationSlug = Innovation::value('slug') ?? 'sample-innovation';

        $publicUrls = [
            '/',
            '/profil',
            '/profil/struktur-organisasi',
            '/profil/pejabat',
            '/profil/pejabat/drs-abd-najib',
            '/news',
            "/news/$newsSlug",
            "/page/$pageSlug",
            '/layanan',
            '/layanan/survei',
            "/layanan/$serviceSlug",
            '/ppid/pengertian',
            '/ppid/profil',
            '/ppid/tugas-fungsi',
            '/ppid/kontak',
            '/ppid/sk-ppid',
            '/ppid/informasi-publik',
            '/ppid/prosedur',
            '/ppid/permohonan',
            '/inovasi',
            "/inovasi/$innovationSlug",
            '/downloads',
            '/faq',
            '/contact',
            '/search?q=dukcapil',
            '/statistik-kependudukan',
            '/pengaduan',
            '/pengaduan/lacak',
            '/sitemap.xml',
            '/robots.txt',
        ];

        foreach ($publicUrls as $url) {
            $response = $this->get($url);
            if ($response->getStatusCode() !== 200) {
                fwrite(STDERR, "PUBLIC URL FAILED [{$response->getStatusCode()}]: $url\n");
            }
            $response->assertStatus(200);
        }
    }

    public function test_all_admin_pages_render_successfully_for_admin(): void
    {
        $admin = User::first();
        $this->assertNotNull($admin, 'Admin user should exist');

        $adminUrls = [
            '/admin/dashboard',
            '/admin/profile/dashboard',
            '/admin/profile/builder',
            '/admin/profile/officials',
            '/admin/profile/organization-chart',
            '/admin/news',
            '/admin/pages',
            '/admin/service-requirements',
            '/admin/ppid/pages',
            '/admin/ppid/documents',
            '/admin/ppid/requests',
            '/admin/complaints',
            '/admin/innovations',
            '/admin/surveys',
            '/admin/downloads',
            '/admin/faqs',
            '/admin/menus',
            '/admin/users',
            '/admin/demographics/dashboard',
            '/admin/demographics/hierarchy',
            '/admin/files',
            '/admin/audit-logs',
            '/admin/notifications',
            '/admin/api-keys',
            '/admin/theme',
        ];

        foreach ($adminUrls as $url) {
            $response = $this->actingAs($admin)->get($url);
            if ($response->getStatusCode() !== 200) {
                fwrite(STDERR, "ADMIN URL FAILED [{$response->getStatusCode()}]: $url\n");
            }
            $response->assertStatus(200);
        }
    }
}
