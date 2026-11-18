<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard permissions and roles
        $perm1 = Permission::firstOrCreate(['name' => 'ktp_blank_stocks.view', 'guard_name' => 'web']);
        $perm2 = Permission::firstOrCreate(['name' => 'ktp_blank_stocks.create_receipt', 'guard_name' => 'web']);
        $perm3 = Permission::firstOrCreate(['name' => 'cms.posts.manage', 'guard_name' => 'web']);

        $superAdminRole = Role::firstOrCreate(['name' => 'Administrator Utama', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo([$perm1, $perm2, $perm3]);

        $this->admin = User::factory()->create([
            'name' => 'Super Administrator',
            'username' => 'superadmin_test',
            'email' => 'admin@dompukab.go.id',
            'is_active' => true,
        ]);
        $this->admin->assignRole($superAdminRole);

        $this->operator = User::factory()->create([
            'name' => 'Operator Test',
            'username' => 'operator_test',
            'email' => 'operator@dompukab.go.id',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_user_and_role_management_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Index')
            ->has('users')
            ->has('roles')
            ->has('modules')
        );
    }

    public function test_admin_can_create_custom_role_with_permissions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'name' => 'Petugas Khusus Blangko',
            'permissions' => ['ktp_blank_stocks.view', 'ktp_blank_stocks.create_receipt'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', [
            'name' => 'Petugas Khusus Blangko',
            'guard_name' => 'web',
        ]);

        $role = Role::findByName('Petugas Khusus Blangko', 'web');
        $this->assertTrue($role->hasPermissionTo('ktp_blank_stocks.view'));
        $this->assertTrue($role->hasPermissionTo('ktp_blank_stocks.create_receipt'));
        $this->assertFalse($role->hasPermissionTo('cms.posts.manage'));
    }

    public function test_admin_can_sync_roles_for_user(): void
    {
        $customRole = Role::firstOrCreate(['name' => 'Staff Humas', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin)->put(route('admin.users.sync-roles', $this->operator), [
            'roles' => ['Staff Humas'],
        ]);

        $response->assertRedirect();
        $this->operator->refresh();
        $this->assertTrue($this->operator->hasRole('Staff Humas'));
    }

    public function test_admin_can_update_custom_role_permissions(): void
    {
        $customRole = Role::firstOrCreate(['name' => 'Staff Khusus', 'guard_name' => 'web']);
        $customRole->syncPermissions(['ktp_blank_stocks.view']);

        $response = $this->actingAs($this->admin)->put(route('admin.roles.update', $customRole), [
            'name' => 'Staff Khusus Updated',
            'permissions' => ['ktp_blank_stocks.view', 'cms.posts.manage'],
        ]);

        $response->assertRedirect();
        $customRole->refresh();
        $this->assertEquals('Staff Khusus Updated', $customRole->name);
        $this->assertTrue($customRole->hasPermissionTo('cms.posts.manage'));
    }

    public function test_protected_system_role_cannot_be_deleted(): void
    {
        $mainRole = Role::findByName('Administrator Utama', 'web');

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $mainRole));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Administrator Utama']);
    }

    public function test_admin_can_delete_unused_custom_role(): void
    {
        $customRole = Role::firstOrCreate(['name' => 'Peran Uji Coba Hapus', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $customRole));

        $response->assertRedirect();
        $this->assertDatabaseMissing('roles', ['name' => 'Peran Uji Coba Hapus']);
    }
}
