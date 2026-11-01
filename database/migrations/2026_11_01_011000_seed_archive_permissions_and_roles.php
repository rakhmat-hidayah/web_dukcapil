<?php

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

        $permissions = [
            'archive.dashboard.view'     => 'Melihat Dashboard Pengarsipan',
            'archive.applications.view'  => 'Melihat Permohonan Siap Diarsipkan',
            'archive.package.create'     => 'Membuat Paket Arsip ZIP',
            'archive.package.view'       => 'Melihat Detail dan Manifest Paket Arsip',
            'archive.package.download'   => 'Mengunduh Paket Arsip ZIP',
            'archive.package.confirm'    => 'Mencatat Konfirmasi Penyimpanan Arsip',
            'archive.history.view'       => 'Melihat Riwayat dan Audit Pengarsipan',
            'archive.storage.view'       => 'Melihat Status Penyimpanan Permanen',
            'archive.settings.manage'    => 'Mengelola Konfigurasi Pengarsipan (Admin)',
            'archive.transfer.execute'   => 'Mengeksekusi Transfer ke Media Permanen (Admin)',
        ];

        foreach ($permissions as $name => $desc) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Create or get 'operator_arsip' role
        $archiveOperatorRole = Role::firstOrCreate(['name' => 'operator_arsip', 'guard_name' => 'web']);
        $archiveOperatorRole->syncPermissions([
            'archive.dashboard.view',
            'archive.applications.view',
            'archive.package.create',
            'archive.package.view',
            'archive.package.download',
            'archive.package.confirm',
            'archive.history.view',
            'archive.storage.view',
        ]);

        // Sync with Super Administrator & Website Administrator
        $superAdmin = Role::where('name', 'Super Administrator')->where('guard_name', 'web')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(array_keys($permissions));
        }

        $webAdmin = Role::where('name', 'Website Administrator')->where('guard_name', 'web')->first();
        if ($webAdmin) {
            $webAdmin->givePermissionTo(array_keys($permissions));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
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

        foreach ($permissions as $perm) {
            Permission::where('name', $perm)->delete();
        }

        Role::where('name', 'operator_arsip')->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
