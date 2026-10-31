<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class KtpHandoverPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'handover.dashboard.view',
            'handover.pending.view',
            'handover.ready.view',
            'handover.ready.update',
            'handover.process',
            'handover.history.view',
            'handover.exception.view',
            'handover.exception.update',
            'handover.photo.view',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Create or update role
        $role = Role::findOrCreate('petugas_pengambilan_ktpel', 'web');
        $role->syncPermissions($permissions);

        // Grant to Super Administrator and Website Administrator
        $superAdmin = Role::findOrCreate('Super Administrator', 'web');
        $superAdmin->givePermissionTo($permissions);

        $webAdmin = Role::findOrCreate('Website Administrator', 'web');
        $webAdmin->givePermissionTo($permissions);
    }
}
