<?php

namespace App\Console\Commands;

use App\Services\ConfigurationService;
use Illuminate\Console\Command;

class CheckConfigurationIntegrity extends Command
{
    protected $signature = 'config:check-integrity';

    protected $description = 'Check database website settings, media assets, URLs, and SEO integrity';

    public function handle(): int
    {
        $this->info("====================================================");
        $this->info("   ENTERPRISE CONFIGURATION INTEGRITY CHECKER      ");
        $this->info("====================================================");

        $report = ConfigurationService::checkIntegrity();

        $this->line("Production Lock Mode : " . ($report['production_lock'] ? "<fg=red>ACTIVE (LOCKED)</>" : "<fg=green>DISABLED (UNLOCKED)</>"));
        $this->line("Total Settings Keys  : " . $report['total_keys_count']);
        $this->line("System Health Status : " . ($report['is_healthy'] ? "<fg=green>HEALTHY (PASSED)</>" : "<fg=yellow>ATTENTION REQUIRED</>"));

        $this->newLine();

        if (!empty($report['missing_keys'])) {
            $this->warn("Missing Required Keys:");
            foreach ($report['missing_keys'] as $item) {
                $this->line("  ❌ $item");
            }
            $this->newLine();
        }

        if (!empty($report['empty_keys'])) {
            $this->warn("Empty Required Values:");
            foreach ($report['empty_keys'] as $item) {
                $this->line("  ⚠️ $item");
            }
            $this->newLine();
        }

        if (!empty($report['media_issues'])) {
            $this->error("Broken Media Asset References:");
            foreach ($report['media_issues'] as $item) {
                $this->line("  🖼️ $item");
            }
            $this->newLine();
        }

        if (!empty($report['url_warnings'])) {
            $this->warn("URL & Format Warnings:");
            foreach ($report['url_warnings'] as $item) {
                $this->line("  🔗 $item");
            }
            $this->newLine();
        }

        if ($report['is_healthy']) {
            $this->info("✔ All configuration keys, media assets, URLs, and SEO rules pass integrity check!");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
