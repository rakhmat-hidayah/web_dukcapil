<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminIdleTimeout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminIdleTimeoutTest extends TestCase
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

    private function getTestAdmin(): User
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_idle_test@dukcapil.dompukab.go.id'],
            ['name' => 'Admin Idle Test', 'username' => 'admin_idle_test', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['Administrator Utama']);
        return $admin;
    }

    public function test_authenticated_admin_with_fresh_activity_can_access_dashboard(): void
    {
        $admin = $this->getTestAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['admin_last_activity_time' => now()->timestamp - 60]) // 1 minute ago (active)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $this->assertTrue(Auth::check());
        $this->assertGreaterThanOrEqual(now()->timestamp - 10, session('admin_last_activity_time'));
    }

    public function test_authenticated_admin_idle_over_15_minutes_is_logged_out_and_redirected(): void
    {
        $admin = $this->getTestAdmin();
        $staleTime = now()->timestamp - (AdminIdleTimeout::TIMEOUT_SECONDS + 10); // 910 seconds ago

        $response = $this->actingAs($admin)
            ->withSession(['admin_last_activity_time' => $staleTime])
            ->get(route('admin.dashboard'));

        // Should redirect to login with timeout parameter
        $response->assertRedirect(route('admin.login', ['timeout' => 1]));
        $this->assertFalse(Auth::check(), 'User should be logged out after 15 minutes of inactivity');
    }

    public function test_inertia_request_idle_timeout_triggers_inertia_location(): void
    {
        $admin = $this->getTestAdmin();
        $staleTime = now()->timestamp - (AdminIdleTimeout::TIMEOUT_SECONDS + 30);
        $version = (new \App\Http\Middleware\HandleInertiaRequests(app()))->version(request());

        $response = $this->actingAs($admin)
            ->withSession(['admin_last_activity_time' => $staleTime])
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Inertia-Version' => $version,
            ])
            ->get(route('admin.dashboard'));

        // Inertia location redirect uses 409 conflict with X-Inertia-Location header
        $response->assertStatus(409);
        $this->assertTrue($response->headers->has('X-Inertia-Location'));
        $this->assertStringContainsString('timeout=1', $response->headers->get('X-Inertia-Location'));
        $this->assertFalse(Auth::check());
    }

    public function test_keep_alive_endpoint_refreshes_last_activity_timestamp(): void
    {
        $admin = $this->getTestAdmin();
        $initialTime = now()->timestamp - 300; // 5 minutes ago

        $response = $this->actingAs($admin)
            ->withSession(['admin_last_activity_time' => $initialTime])
            ->postJson(route('admin.keep-alive'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'timestamp']);
        $this->assertEquals('active', $response->json('status'));
        $this->assertGreaterThanOrEqual($initialTime + 290, session('admin_last_activity_time'));
    }
}
