<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleBasedDashboardRedirectTest extends TestCase
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

    public function test_super_admin_sees_overview_dashboard(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test_redirect@dukcapil.dompukab.go.id'],
            ['name' => 'Admin Utama', 'username' => 'admin_test_redirect', 'password' => bcrypt('password')]
        );
        $admin->syncRoles(['Administrator Utama']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }

    public function test_petugas_pengambilan_redirected_to_handover_dashboard(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'petugas_pengambilan_test@dukcapil.dompukab.go.id'],
            ['name' => 'Petugas Pengambilan Test', 'username' => 'petugas_pengambilan_test', 'password' => bcrypt('password')]
        );
        $user->syncRoles(['Petugas Pengambilan KTP-el']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.handover.dashboard'));
    }

    public function test_petugas_verifikasi_redirected_to_queue_dashboard(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'petugas_verif_test@dukcapil.dompukab.go.id'],
            ['name' => 'Petugas Verifikasi Test', 'username' => 'petugas_verif_test', 'password' => bcrypt('password')]
        );
        $user->syncRoles(['Petugas Verifikasi / Registrasi KTP-el']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.queue.dashboard'));
    }

    public function test_petugas_pengarsipan_redirected_to_archive_dashboard(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'petugas_arsip_test@dukcapil.dompukab.go.id'],
            ['name' => 'Petugas Pengarsipan Test', 'username' => 'petugas_arsip_test', 'password' => bcrypt('password')]
        );
        $user->syncRoles(['Petugas Pengarsipan']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.archive.dashboard'));
    }

    public function test_auto_role_sync_heals_legacy_roles(): void
    {
        // Simulate legacy role existing in database
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Complaint Officer', 'guard_name' => 'web']);

        \App\Services\AutoRoleSync::sync();

        $this->assertDatabaseMissing('roles', ['name' => 'Complaint Officer']);
        $this->assertDatabaseHas('roles', ['name' => 'Petugas Pengaduan']);
    }
}
