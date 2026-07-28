<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database using structured modular seeders:
     * 1. InstallationSeeder: Core setup (Roles, Permissions, Setting Keys)
     * 2. ReferenceSeeder: Master reference data (Wilayah Dompu, Categories)
     * 3. DemoDataSeeder: Demo content (Blocked if APP_PRODUCTION_LOCK=true)
     */
    public function run(): void
    {
        $this->call([
            InstallationSeeder::class,
            ReferenceSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
