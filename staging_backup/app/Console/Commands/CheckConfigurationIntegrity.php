<?php

namespace App\Console\Commands;

use App\Services\ConfigurationService;
use Illuminate\Console\Command;

class CheckConfigurationIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:check-integrity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database website settings integrity against required schemas and validate format rules';

    /**
     * Execute the console command.
     */
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
            $this->warn("Missing Keys:");
            foreach ($report['missing_keys'] as $item) {
                $this->line("  ❌ $item");
            }
            $this->newLine();
        }

        if (!empty($report['empty_keys'])) {
            $this->warn("Empty Keys:");
            foreach ($report['empty_keys'] as $item) {
                $this->line("  ⚠️ $item");
            }
            $this->newLine();
        }

        if (!empty($report['warnings'])) {
            $this->warn("Format Warnings:");
            foreach ($report['warnings'] as $item) {
                $this->line("  ⚠️ $item");
            }
            $this->newLine();
        }

        if ($report['is_healthy']) {
            $this->info("✔ All configuration keys pass integrity check!");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
