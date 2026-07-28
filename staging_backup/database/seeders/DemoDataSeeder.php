<?php

namespace Database\Seeders;

use App\Services\ConfigurationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DemoDataSeeder extends Seeder
{
    /**
     * Run sample demo content seeders.
     * Automatically blocked if Production Lock Mode (APP_PRODUCTION_LOCK) is active.
     */
    public function run(): void
    {
        if (ConfigurationService::isProductionLocked()) {
            $msg = "DemoDataSeeder execution blocked because Production Lock Mode (APP_PRODUCTION_LOCK) is active.";
            Log::warning($msg);
            $this->command?->warn($msg);
            return;
        }

        $this->call([
            CmsSeeder::class,
            InnovationSeeder::class,
            DownloadCenterSeeder::class,
            SurveySeeder::class,
            EnterpriseNavigationSeeder::class,
            ApiLogSeeder::class,
            ApiSettingSeeder::class,
        ]);
    }
}
