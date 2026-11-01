<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Role translation mapping: English => Indonesian
     */
    protected array $roleMap = [
        'Super Administrator'   => 'Administrator Utama',
        'Website Administrator' => 'Administrator Website',
        'Content Editor'        => 'Editor Konten',
        'News Operator'         => 'Operator Berita',
        'Publication Operator'  => 'Operator Publikasi',
        'Complaint Officer'     => 'Petugas Pengaduan',
        'Media Manager'         => 'Pengelola Media',
        'PPID Admin'            => 'Admin PPID',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create each Indonesian role and copy permissions from the existing role
        foreach ($this->roleMap as $oldName => $newName) {
            $newRole = Role::firstOrCreate(['name' => $newName, 'guard_name' => 'web']);
            $oldRole = Role::where('name', $oldName)->where('guard_name', 'web')->first();

            if ($oldRole) {
                $permissions = $oldRole->permissions->pluck('name')->toArray();
                if (!empty($permissions)) {
                    $newRole->syncPermissions($permissions);
                }
            }
        }

        // Ensure Administrator Utama has ALL permissions
        $adminUtama = Role::where('name', 'Administrator Utama')->where('guard_name', 'web')->first();
        if ($adminUtama) {
            $adminUtama->syncPermissions(Permission::all());
        }

        // Ensure Admin PPID has PPID permissions
        $adminPpid = Role::where('name', 'Admin PPID')->where('guard_name', 'web')->first();
        if ($adminPpid) {
            $adminPpid->givePermissionTo(['manage_ppid', 'manage_downloads']);
        }

        // 2. Migrate all users with old role names to the new Indonesian role names
        $users = User::with('roles')->get();
        foreach ($users as $user) {
            foreach ($this->roleMap as $oldName => $newName) {
                if ($user->hasRole($oldName)) {
                    $newRole = Role::where('name', $newName)->where('guard_name', 'web')->first();
                    if ($newRole) {
                        $user->assignRole($newRole);
                        $user->removeRole($oldName);
                    }
                }
            }
        }

        // 3. Remove deprecated English roles so they do not show in the UI modal
        Role::whereIn('name', array_keys($this->roleMap))->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->roleMap as $oldName => $newName) {
            $oldRole = Role::firstOrCreate(['name' => $oldName, 'guard_name' => 'web']);
            $newRole = Role::where('name', $newName)->where('guard_name', 'web')->first();

            if ($newRole) {
                $permissions = $newRole->permissions->pluck('name')->toArray();
                if (!empty($permissions)) {
                    $oldRole->syncPermissions($permissions);
                }
            }
        }

        $users = User::with('roles')->get();
        foreach ($users as $user) {
            foreach ($this->roleMap as $oldName => $newName) {
                if ($user->hasRole($newName)) {
                    $oldRole = Role::where('name', $oldName)->where('guard_name', 'web')->first();
                    if ($oldRole) {
                        $user->assignRole($oldRole);
                        $user->removeRole($newName);
                    }
                }
            }
        }

        Role::whereIn('name', array_values($this->roleMap))->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
