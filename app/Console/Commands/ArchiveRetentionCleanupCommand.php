<?php

namespace App\Console\Commands;

use App\Services\Archive\ArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveRetentionCleanupCommand extends Command
{
    protected $signature = 'archive:cleanup-retention 
                            {--retention-days= : Override batas masa retensi arsip digital dalam hari}
                            {--grace-days= : Override masa tenggang pembersihan dalam hari}
                            {--triggered-by=scheduler : Pemicu eksekusi (scheduler|manual|web)}';

    protected $description = 'Bersihkan paket arsip dan berkas yang telah melewati masa retensi secara otomatis';

    public function handle(ArchiveService $archiveService): int
    {
        $retentionDays = $this->option('retention-days') !== null ? (int) $this->option('retention-days') : null;
        $graceDays = $this->option('grace-days') !== null ? (int) $this->option('grace-days') : null;
        $triggeredBy = $this->option('triggered-by') ?: 'scheduler';

        $this->info("[archive:cleanup-retention] Memulai pembersihan arsip (Trigger: {$triggeredBy})...");

        try {
            $result = $archiveService->cleanupExpiredArchives($retentionDays, $graceDays);

            $this->info("[archive:cleanup-retention] Selesai: {$result['purged_packages_count']} paket dibersihkan ({$result['freed_bytes_formatted']}). Batas Retensi: {$result['retention_days']} hari, Grace Period: {$result['grace_period_days']} hari.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("[archive:cleanup-retention] Error: " . $e->getMessage());
            Log::error("ArchiveRetentionCleanupCommand error: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return self::FAILURE;
        }
    }
}
