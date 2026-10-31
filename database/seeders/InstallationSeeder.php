<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InstallationSeeder extends Seeder
{
    /**
     * Run initial core system installation seeders.
     * Safe to run on fresh deployments. Never overwrites existing production records.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            KtpHandoverPermissionSeeder::class,
            WebsiteSettingSeeder::class,
            ThemeSettingSeeder::class,
            PpidSeeder::class,
        ]);
    }
}
