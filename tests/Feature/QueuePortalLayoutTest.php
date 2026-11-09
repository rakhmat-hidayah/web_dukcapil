<?php

namespace Tests\Feature;

use App\Models\Queue\QueueSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueuePortalLayoutTest extends TestCase
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
        DB::purge();
        DB::reconnect();
        Cache::forget('nav_menus');
    }

    public function test_default_portal_renders_modern_app_portal(): void
    {
        QueueSetting::set('queue.portal_layout', 'app_portal', 'layout');

        $response = $this->get(route('queue.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Queue/Index')
            ->where('portalLayout', 'app_portal')
            ->has('services')
            ->has('todayStats')
        );
    }

    public function test_query_mode_classic_provides_instant_rollback(): void
    {
        QueueSetting::set('queue.portal_layout', 'app_portal', 'layout');

        $response = $this->get(route('queue.index', ['mode' => 'classic']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Queue/IndexClassic')
            ->where('portalLayout', 'classic')
        );
    }

    public function test_admin_settings_can_switch_portal_layout(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_layout_test@dukcapil.dompukab.go.id'],
            ['name' => 'Admin Layout Test', 'username' => 'admin_layout_test', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['Administrator Utama']);

        // Admin switches to classic mode
        $response = $this->actingAs($admin)->post(route('admin.queue.settings.update'), [
            'settings' => [
                'queue.portal_layout' => 'classic',
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('classic', QueueSetting::get('queue.portal_layout'));

        // Public page now serves classic view by default
        $publicResponse = $this->get(route('queue.index'));
        $publicResponse->assertStatus(200);
        $publicResponse->assertInertia(fn ($page) => $page->component('Public/Queue/IndexClassic'));

        // Admin switches back to modern app portal
        $this->actingAs($admin)->post(route('admin.queue.settings.update'), [
            'settings' => [
                'queue.portal_layout' => 'app_portal',
            ],
        ]);
        $this->assertEquals('app_portal', QueueSetting::get('queue.portal_layout'));
    }
}
