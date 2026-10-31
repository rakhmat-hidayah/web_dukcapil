<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            'manage_online_queue',
            'manage_queue_schedule',
            'manage_queue_verification',
            'view_queue_reports',
        ];

        foreach ($newPermissions as $perm) {
            \Spatie\Permission\Models\Permission::findOrCreate($perm);
        }

        // Role: Queue Officer
        $queueOfficer = \Spatie\Permission\Models\Role::findOrCreate('Queue Officer');
        $queueOfficer->givePermissionTo(['manage_online_queue', 'manage_queue_schedule', 'view_queue_reports']);

        // Role: Queue Verifier
        $queueVerifier = \Spatie\Permission\Models\Role::findOrCreate('Queue Verifier');
        $queueVerifier->givePermissionTo(['manage_queue_verification', 'view_queue_reports']);

        // Super Administrator + Website Administrator mendapatkan semua permission baru
        $superAdmin = \Spatie\Permission\Models\Role::findByName('Super Administrator');
        if ($superAdmin) {
            $superAdmin->givePermissionTo($newPermissions);
        }
        $webAdmin = \Spatie\Permission\Models\Role::findByName('Website Administrator');
        if ($webAdmin) {
            $webAdmin->givePermissionTo($newPermissions);
        }
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $perms = ['manage_online_queue', 'manage_queue_schedule', 'manage_queue_verification', 'view_queue_reports'];
        \Spatie\Permission\Models\Permission::whereIn('name', $perms)->delete();
        \Spatie\Permission\Models\Role::whereIn('name', ['Queue Officer', 'Queue Verifier'])->delete();
    }
};
