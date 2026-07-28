<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    /**
     * Run master reference data seeders (Wilayah Dompu, Categories, Services).
     * Idempotent & safe to run without modifying user-generated CMS content.
     */
    public function run(): void
    {
        $this->call([
            DemographicSeeder::class,
            ComplaintCategorySeeder::class,
            ServiceRequirementSeeder::class,
            EnterpriseProfileSeeder::class,
        ]);
    }
}
