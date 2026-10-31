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
     * Safe to re-run: uses updateOrCreate so existing production data is not overwritten.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------------
        // DEFINE PERMISSIONS
        // -------------------------------------------------------
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
            'manage_ppid',
            'notifications.view',
            'notifications.manage',
            'notifications.test',
            'notifications.logs',
            'view_audit_logs',
            'view_analytics',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName);
        }

        // -------------------------------------------------------
        // DEFINE ROLES
        // -------------------------------------------------------

        // 1. Super Administrator — full access
        $superAdminRole = Role::findOrCreate('Super Administrator');
        $superAdminRole->givePermissionTo(Permission::all());

        // 2. Website Administrator — full CMS, no user/role management
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
            'manage_ppid',
            'notifications.view',
            'notifications.manage',
            'notifications.test',
            'notifications.logs',
            'view_audit_logs',
            'view_analytics',
        ]);

        // 3. Content Editor — content only
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

        // 4. News Operator — news only
        $newsOperatorRole = Role::findOrCreate('News Operator');
        $newsOperatorRole->givePermissionTo([
            'manage_file_manager',
            'manage_news',
        ]);

        // 5. Publication Operator — downloads & publications
        $pubOperatorRole = Role::findOrCreate('Publication Operator');
        $pubOperatorRole->givePermissionTo([
            'manage_file_manager',
            'manage_downloads',
            'manage_publications',
        ]);

        // 6. Complaint Officer — complaints & surveys
        $complaintOfficerRole = Role::findOrCreate('Complaint Officer');
        $complaintOfficerRole->givePermissionTo([
            'manage_complaints',
        ]);

        // 7. Media Manager — file manager only
        $mediaManagerRole = Role::findOrCreate('Media Manager');
        $mediaManagerRole->givePermissionTo([
            'manage_file_manager',
        ]);

        // -------------------------------------------------------
        // SEED DEFAULT SYSTEM USERS
        // -------------------------------------------------------

        // Super Admin — username: superadmin
        $superAdminUser = User::updateOrCreate(
            ['email' => 'admin@dompukab.go.id'],
            [
                'username'           => 'superadmin',
                'name'               => 'Super Administrator',
                'password'           => Hash::make('password123'),
                'email_verified_at'  => now(),
                'is_active'          => true,
                'failed_login_count' => 0,
            ]
        );
        $superAdminUser->syncRoles([$superAdminRole]);

        // Complaint Officer — username: complaint_officer
        $complaintOfficerUser = User::updateOrCreate(
            ['email' => 'complaint@dompukab.go.id'],
            [
                'username'           => 'complaint_officer',
                'name'               => 'Petugas Pengaduan',
                'password'           => Hash::make('password123'),
                'email_verified_at'  => now(),
                'is_active'          => true,
                'failed_login_count' => 0,
            ]
        );
        $complaintOfficerUser->syncRoles([$complaintOfficerRole]);
    }
}
