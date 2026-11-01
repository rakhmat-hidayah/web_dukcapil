<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AutoRoleSync
{
    public static array $roleMap = [
        'Super Administrator'   => 'Administrator Utama',
        'Website Administrator' => 'Administrator Website',
        'Content Editor'        => 'Editor Konten',
        'News Operator'         => 'Operator Berita',
        'Publication Operator'  => 'Operator Publikasi',
        'Complaint Officer'     => 'Petugas Pengaduan',
        'Media Manager'         => 'Pengelola Media',
        'PPID Admin'            => 'Admin PPID',
        'Queue Officer'         => 'Petugas Verifikasi / Registrasi KTP-el',
        'Queue Verifier'        => 'Petugas Verifikasi / Registrasi KTP-el',
        'petugas_pengambilan_ktpel' => 'Petugas Pengambilan KTP-el',
        'operator_arsip'        => 'Petugas Pengarsipan',
    ];

    /**
     * Resolve any role name (English or Indonesian) to the canonical Indonesian role name.
     */
    public static function resolveRoleName(?string $name): string
    {
        if (!$name) {
            return 'Editor Konten';
        }

        return self::$roleMap[$name] ?? $name;
    }

    /**
     * Automatically ensure database migrations and official Indonesian roles are active.
     * Safe to run on every request or boot: checks cache and DB state.
     */
    public static function sync(): void
    {
        try {
            // 1. Run pending migrations safely if not yet cached as completed
            if (!Cache::has('db_auto_migrations_v4')) {
                Artisan::call('migrate', ['--force' => true]);
                Cache::put('db_auto_migrations_v4', true, now()->addDays(3));
            }

            // 2. Check if legacy English roles still exist in DB or Indonesian roles are missing
            $hasLegacyRoles = Role::whereIn('name', array_keys(self::$roleMap))->exists();
            $missingIndonesianRoles = Role::whereIn('name', [
                'Administrator Utama',
                'Administrator Website',
                'Editor Konten',
                'Petugas Verifikasi / Registrasi KTP-el',
                'Petugas Pengambilan KTP-el',
                'Petugas Pengarsipan',
            ])->count() < 6;

            if ($hasLegacyRoles || $missingIndonesianRoles) {
                self::performSync();
            }
        } catch (\Throwable $e) {
            Log::warning('AutoRoleSync warning: ' . $e->getMessage());
        }
    }

    /**
     * Perform the role migration and user role reassignment.
     */
    public static function performSync(): void
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // Create target Indonesian roles if they don't exist
            foreach (self::$roleMap as $oldName => $newName) {
                $newRole = Role::firstOrCreate(['name' => $newName, 'guard_name' => 'web']);
                $oldRole = Role::where('name', $oldName)->where('guard_name', 'web')->first();

                if ($oldRole) {
                    $permissions = $oldRole->permissions->pluck('name')->toArray();
                    if (!empty($permissions)) {
                        $newRole->syncPermissions($permissions);
                    }
                }
            }

            // Ensure Administrator Utama has all permissions
            $adminUtama = Role::where('name', 'Administrator Utama')->where('guard_name', 'web')->first();
            if ($adminUtama) {
                $adminUtama->syncPermissions(Permission::all());
            }

            // Ensure Admin PPID has PPID permissions
            $adminPpid = Role::where('name', 'Admin PPID')->where('guard_name', 'web')->first();
            if ($adminPpid) {
                $adminPpid->givePermissionTo(['manage_ppid', 'manage_downloads']);
            }

            // Migrate all users having legacy roles
            $users = User::with('roles')->get();
            foreach ($users as $user) {
                foreach (self::$roleMap as $oldName => $newName) {
                    if ($user->hasRole($oldName)) {
                        $newRole = Role::where('name', $newName)->where('guard_name', 'web')->first();
                        if ($newRole) {
                            $user->assignRole($newRole);
                            $user->removeRole($oldName);
                        }
                    }
                }
            }

            // Delete deprecated legacy roles so they never appear in UI
            Role::whereIn('name', array_keys(self::$roleMap))->delete();

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            Cache::forget('nav_menus');
        } catch (\Throwable $e) {
            Log::warning('AutoRoleSync performSync error: ' . $e->getMessage());
        }
    }
}
