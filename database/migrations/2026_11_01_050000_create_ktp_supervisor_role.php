<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create role: Supervisor Layanan KTP-el with all KTP-el module permissions active
     * (Antrian Online & Verifikasi, Serah Terima Fisik Kartu, dan Pengarsipan Permohonan Online).
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roleSupervisor = Role::firstOrCreate(['name' => 'Supervisor Layanan KTP-el', 'guard_name' => 'web']);

        $allKtpPerms = [
            // 1. Antrian Online & Verifikasi Dokumen (Queue)
            'manage_online_queue',
            'manage_queue_verification',
            'view_queue_reports',
            'manage_queue_schedule',
            'notifications.view',
            'notifications.logs',

            // 2. Serah Terima KTP-el (Handover)
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

            // 3. Pengarsipan Permohonan (Archive)
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

        foreach ($allKtpPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roleSupervisor->syncPermissions($allKtpPerms);

        // Also ensure Super Admin and Website Admin retain these permissions
        $superAdmin = Role::whereIn('name', ['Administrator Utama', 'Super Administrator'])->where('guard_name', 'web')->get();
        foreach ($superAdmin as $sa) {
            $sa->givePermissionTo($allKtpPerms);
        }

        $webAdmin = Role::whereIn('name', ['Administrator Website', 'Website Administrator'])->where('guard_name', 'web')->get();
        foreach ($webAdmin as $wa) {
            $wa->givePermissionTo($allKtpPerms);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
