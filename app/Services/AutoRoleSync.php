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
        'supervisor_layanan_ktpel' => 'Supervisor Layanan KTP-el',
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
    public static function sync(bool $force = false): void
    {
        // Avoid running 30+ DB queries and clearing Spatie cache on every web request
        if (!$force && Cache::has('auto_role_sync_v9_done')) {
            return;
        }

        Cache::put('auto_role_sync_v9_done', true, now()->addHours(24));

        // 1. Run pending migrations safely if not yet cached as completed
        try {
            if (!Cache::has('db_auto_migrations_v13')) {
                Artisan::call('migrate', ['--force' => true]);
                Cache::put('db_auto_migrations_v13', true, now()->addDays(7));
            }
        } catch (\Throwable $e) {
            Log::warning('AutoRoleSync migration warning: ' . $e->getMessage());
        }

        // 2. Ensure all mandatory requirements (Formulir F-1.02 and Selfie photo) exist across all services
        try {
            \App\Services\Queue\QueueApplicationService::ensureAllRequiredDocuments();
        } catch (\Throwable $e) {
            Log::warning('AutoRoleSync ensureAllRequiredDocuments warning: ' . $e->getMessage());
        }

        // 3. Ensure Indonesian roles and Supervisor Layanan KTP-el exist
        try {
            self::ensureIndonesianRoles();
        } catch (\Throwable $e) {
            Log::warning('AutoRoleSync ensureIndonesianRoles warning: ' . $e->getMessage());
        }
    }

    /**
     * Ensure all canonical Indonesian roles exist and Supervisor Layanan KTP-el has all permissions.
     */
    public static function ensureIndonesianRoles(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Explicitly guarantee Supervisor Layanan KTP-el is active
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor Layanan KTP-el', 'guard_name' => 'web']);
        $ktpPerms = [
            'manage_online_queue',
            'manage_queue_verification',
            'view_queue_reports',
            'manage_queue_schedule',
            'notifications.view',
            'notifications.logs',
            'handover.dashboard.view',
            'handover.ready.view',
            'handover.ready.update',
            'handover.pending.view',
            'handover.history.view',
            'handover.exceptions.view',
            'handover.exception.view',
            'handover.exception.update',
            'handover.process',
            'handover.photo.view',
            'archive.dashboard.view',
            'archive.applications.view',
            'archive.package.create',
            'archive.package.view',
            'archive.package.download',
            'archive.package.confirm',
            'archive.history.view',
            'archive.storage.view',
            'archive.settings.manage',
            'archive.transfer.execute',
        ];
        foreach ($ktpPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $supervisorRole->syncPermissions($ktpPerms);

        // 2. Check if legacy English roles still exist in DB or other Indonesian roles are missing
        $hasLegacyRoles = Role::whereIn('name', array_keys(self::$roleMap))->exists();
        $missingRoles = Role::whereIn('name', [
            'Administrator Utama',
            'Administrator Website',
            'Editor Konten',
            'Operator Berita',
            'Operator Publikasi',
            'Pengelola Media',
            'Petugas Pengaduan',
            'Admin PPID',
            'Petugas Verifikasi / Registrasi KTP-el',
            'Petugas Pengambilan KTP-el',
            'Petugas Pengarsipan',
            'Supervisor Layanan KTP-el',
        ])->count() < 12;

        if ($hasLegacyRoles || $missingRoles) {
            self::performSync();
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

            // Ensure Supervisor Layanan KTP-el has all KTP-el module permissions
            $supervisorKtp = Role::firstOrCreate(['name' => 'Supervisor Layanan KTP-el', 'guard_name' => 'web']);
            $ktpPerms = [
                'manage_online_queue',
                'manage_queue_verification',
                'view_queue_reports',
                'manage_queue_schedule',
                'notifications.view',
                'notifications.logs',
                'handover.dashboard.view',
                'handover.ready.view',
                'handover.ready.update',
                'handover.pending.view',
                'handover.history.view',
                'handover.exceptions.view',
                'handover.exception.view',
                'handover.exception.update',
                'handover.process',
                'handover.photo.view',
                'archive.dashboard.view',
                'archive.applications.view',
                'archive.package.create',
                'archive.package.view',
                'archive.package.download',
                'archive.package.confirm',
                'archive.history.view',
                'archive.storage.view',
                'archive.settings.manage',
                'archive.transfer.execute',
            ];
            foreach ($ktpPerms as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
            $supervisorKtp->syncPermissions($ktpPerms);

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
