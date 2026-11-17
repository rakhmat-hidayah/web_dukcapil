<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class KtpBlankStockPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all permissions for Blank Stock Module
        $permissions = [
            'blank_stocks.view',
            'blank_stocks.dashboard',
            'blank_stocks.receipts.view',
            'blank_stocks.receipts.create',
            'blank_stocks.usage.view_own',
            'blank_stocks.usage.view_all',
            'blank_stocks.usage.create',
            'blank_stocks.usage.revise_own',
            'blank_stocks.usage.approve',
            'blank_stocks.opname.view',
            'blank_stocks.opname.create',
            'blank_stocks.opname.approve',
            'blank_stocks.adjustment.create',
            'blank_stocks.adjustment.approve',
            'blank_stocks.ledger.view',
            'blank_stocks.reports.export',
            'blank_stocks.settings.manage',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // 2. Roles Definition

        // A. Bendahara Barang
        $bendaharaRole = Role::findOrCreate('Bendahara Barang', 'web');
        $bendaharaRole->syncPermissions([
            'blank_stocks.view',
            'blank_stocks.dashboard',
            'blank_stocks.receipts.view',
            'blank_stocks.receipts.create',
            'blank_stocks.usage.view_all',
            'blank_stocks.opname.view',
            'blank_stocks.opname.create',
            'blank_stocks.adjustment.create',
            'blank_stocks.ledger.view',
            'blank_stocks.reports.export',
        ]);

        // B. Operator Cetak KTP-el
        $operatorCetakRole = Role::findOrCreate('Operator Cetak KTP-el', 'web');
        $operatorCetakRole->syncPermissions([
            'blank_stocks.view',
            'blank_stocks.usage.view_own',
            'blank_stocks.usage.create',
            'blank_stocks.usage.revise_own',
        ]);

        // C. Supervisor Layanan KTP-el (Pimpinan / Supervisor)
        $supervisorRole = Role::findOrCreate('Supervisor Layanan KTP-el', 'web');
        $supervisorRole->givePermissionTo([
            'blank_stocks.view',
            'blank_stocks.dashboard',
            'blank_stocks.receipts.view',
            'blank_stocks.usage.view_all',
            'blank_stocks.usage.approve',
            'blank_stocks.opname.view',
            'blank_stocks.opname.approve',
            'blank_stocks.adjustment.approve',
            'blank_stocks.ledger.view',
            'blank_stocks.reports.export',
        ]);

        // D. Administrators
        $superAdmin = Role::findOrCreate('Super Administrator', 'web');
        $superAdmin->givePermissionTo($permissions);

        $adminUtama = Role::findOrCreate('Administrator Utama', 'web');
        $adminUtama->givePermissionTo($permissions);

        $adminWeb = Role::findOrCreate('Administrator Website', 'web');
        $adminWeb->givePermissionTo($permissions);

        // 3. Seed Default User Accounts for testing and demonstration if not exist
        // Default Bendahara Barang
        User::firstOrCreate(
            ['email' => 'bendahara.barang@dukcapil.dompukab.go.id'],
            [
                'name'               => 'Bendahara Barang Disdukcapil',
                'username'           => 'bendahara_barang',
                'password'           => Hash::make('password123'),
                'email_verified_at'  => now(),
                'is_active'          => true,
                'failed_login_count' => 0,
            ]
        )->syncRoles([$bendaharaRole]);

        // Default Operator Cetak A
        User::firstOrCreate(
            ['email' => 'operator.cetak.a@dukcapil.dompukab.go.id'],
            [
                'name'               => 'Operator Cetak A',
                'username'           => 'operator_cetak_a',
                'password'           => Hash::make('password123'),
                'email_verified_at'  => now(),
                'is_active'          => true,
                'failed_login_count' => 0,
            ]
        )->syncRoles([$operatorCetakRole]);

        // Default Operator Cetak B
        User::firstOrCreate(
            ['email' => 'operator.cetak.b@dukcapil.dompukab.go.id'],
            [
                'name'               => 'Operator Cetak B',
                'username'           => 'operator_cetak_b',
                'password'           => Hash::make('password123'),
                'email_verified_at'  => now(),
                'is_active'          => true,
                'failed_login_count' => 0,
            ]
        )->syncRoles([$operatorCetakRole]);
    }
}
