<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Role: Petugas Verifikasi / Registrasi KTP-el
        $roleVerif = Role::firstOrCreate(['name' => 'Petugas Verifikasi / Registrasi KTP-el', 'guard_name' => 'web']);
        $verifPerms = [
            'manage_online_queue',
            'manage_queue_verification',
            'view_queue_reports',
            'manage_queue_schedule',
            'notifications.view',
            'notifications.logs',
        ];
        foreach ($verifPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $roleVerif->syncPermissions($verifPerms);

        // 2. Role: Petugas Pengambilan KTP-el
        $roleHandover = Role::firstOrCreate(['name' => 'Petugas Pengambilan KTP-el', 'guard_name' => 'web']);
        $handoverPerms = [
            'handover.dashboard.view',
            'handover.ready.view',
            'handover.ready.update',
            'handover.pending.view',
            'handover.history.view',
            'handover.exceptions.view',
            'handover.exception.update',
            'handover.process',
            'handover.photo.view',
        ];
        foreach ($handoverPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $roleHandover->syncPermissions($handoverPerms);

        // 3. Role: Petugas Pengarsipan
        $roleArchive = Role::firstOrCreate(['name' => 'Petugas Pengarsipan', 'guard_name' => 'web']);
        $archivePerms = [
            'archive.dashboard.view',
            'archive.applications.view',
            'archive.package.create',
            'archive.package.view',
            'archive.package.download',
            'archive.package.confirm',
            'archive.history.view',
            'archive.storage.view',
        ];
        foreach ($archivePerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $roleArchive->syncPermissions($archivePerms);

        // Migrate any existing users from legacy roles to the new official roles
        $usersToMigrate = User::with('roles')->get();
        foreach ($usersToMigrate as $u) {
            if ($u->hasRole('petugas_pengambilan_ktpel')) {
                $u->removeRole('petugas_pengambilan_ktpel');
                $u->assignRole($roleHandover);
            }
            if ($u->hasRole('operator_arsip')) {
                $u->removeRole('operator_arsip');
                $u->assignRole($roleArchive);
            }
            if ($u->hasRole('Queue Verifier') || $u->hasRole('Queue Officer')) {
                $u->removeRole('Queue Verifier');
                $u->removeRole('Queue Officer');
                $u->assignRole($roleVerif);
            }
        }

        // Clean up deprecated technical names so they don't clutter the UI modal
        Role::whereIn('name', ['petugas_pengambilan_ktpel', 'operator_arsip', 'Queue Verifier', 'Queue Officer'])->delete();

        // Also give all permissions to Super Administrator & Website Administrator
        $superAdmin = Role::where('name', 'Super Administrator')->where('guard_name', 'web')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(array_merge($verifPerms, $handoverPerms, $archivePerms));
        }

        $webAdmin = Role::where('name', 'Website Administrator')->where('guard_name', 'web')->first();
        if ($webAdmin) {
            $webAdmin->givePermissionTo(array_merge($verifPerms, $handoverPerms, $archivePerms));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::whereIn('name', [
            'Petugas Verifikasi / Registrasi KTP-el',
            'Petugas Pengambilan KTP-el',
            'Petugas Pengarsipan',
        ])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
