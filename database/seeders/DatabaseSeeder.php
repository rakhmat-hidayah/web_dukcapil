<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ThemeSettingSeeder::class,
            WebsiteSettingSeeder::class,
            ApiSettingSeeder::class,
            CmsSeeder::class,
            DemographicSeeder::class,
            ComplaintCategorySeeder::class,
            ServiceRequirementSeeder::class,
            InnovationSeeder::class,
            PpidSeeder::class,
            EnterpriseProfileSeeder::class,
            EnterpriseNavigationSeeder::class,
        ]);
    }
}
