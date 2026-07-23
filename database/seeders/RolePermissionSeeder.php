<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'manage_settings',
            'manage_themes',
            'manage_users',
            'manage_roles',
            'manage_file_manager',
            'manage_news',
            'manage_pages',
            'manage_announcements',
            'manage_banners',
            'manage_gallery',
            'manage_downloads',
            'manage_faq',
            'manage_demographic',
            'manage_publications',
            'manage_complaints',
            'view_audit_logs',
            'view_analytics',
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName);
        }

        // Create roles and assign existing permissions
        
        // 1. Super Administrator
        $superAdminRole = Role::findOrCreate('Super Administrator');
        $superAdminRole->givePermissionTo(Permission::all());

        // 2. Website Administrator
        $webAdminRole = Role::findOrCreate('Website Administrator');
        $webAdminRole->givePermissionTo([
            'manage_settings',
            'manage_themes',
            'manage_file_manager',
            'manage_news',
            'manage_pages',
            'manage_announcements',
            'manage_banners',
            'manage_gallery',
            'manage_downloads',
            'manage_faq',
            'manage_demographic',
            'manage_publications',
            'manage_complaints',
            'view_audit_logs',
            'view_analytics',
        ]);

        // 3. Content Editor
        $contentEditorRole = Role::findOrCreate('Content Editor');
        $contentEditorRole->givePermissionTo([
            'manage_file_manager',
            'manage_news',
            'manage_pages',
            'manage_announcements',
            'manage_banners',
            'manage_gallery',
            'manage_faq',
        ]);

        // 4. News Operator
        $newsOperatorRole = Role::findOrCreate('News Operator');
        $newsOperatorRole->givePermissionTo([
            'manage_file_manager',
            'manage_news',
        ]);

        // 5. Publication Operator
        $pubOperatorRole = Role::findOrCreate('Publication Operator');
        $pubOperatorRole->givePermissionTo([
            'manage_file_manager',
            'manage_downloads',
            'manage_publications',
        ]);

        // 6. Complaint Officer
        $complaintOfficerRole = Role::findOrCreate('Complaint Officer');
        $complaintOfficerRole->givePermissionTo([
            'manage_complaints',
        ]);

        // 7. Media Manager
        $mediaManagerRole = Role::findOrCreate('Media Manager');
        $mediaManagerRole->givePermissionTo([
            'manage_file_manager',
        ]);

        // Create default Super Admin user for local deployment
        $superAdminUser = User::updateOrCreate(
            ['email' => 'admin@dompukab.go.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $superAdminUser->assignRole($superAdminRole);

        // Create a few default users for testing
        $complaintOfficerUser = User::updateOrCreate(
            ['email' => 'complaint@dompukab.go.id'],
            [
                'name' => 'Petugas Pengaduan',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $complaintOfficerUser->assignRole($complaintOfficerRole);
    }
}
