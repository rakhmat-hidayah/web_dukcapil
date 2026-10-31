<?php

namespace App\Console\Commands;

use App\Models\VisitorCleanupLog;
use App\Models\WebsiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class VisitorCleanupCommand extends Command
{
    protected $signature   = 'visitor:cleanup {--force : Force cleanup even if auto-cleanup is disabled} {--triggered-by=scheduler : Who triggered this cleanup (scheduler|manual)}';
    protected $description = 'Cleanup expired visitor logs based on retention policy (chunked, non-blocking).';

    public function handle(): int
    {
        // 1. Safety check — Auto Cleanup enabled?
        $isEnabled = WebsiteSetting::where('key', 'visitor_auto_cleanup_enabled')->value('value');
        if ($isEnabled !== '1' && !$this->option('force')) {
            $this->warn('[visitor:cleanup] Auto Cleanup is disabled. Use --force to override.');
            return self::SUCCESS;
        }

        // 2. Load settings
        $retentionDays = (int) (WebsiteSetting::where('key', 'visitor_retention_days')->value('value') ?: 90);
        $chunkSize     = (int) (WebsiteSetting::where('key', 'visitor_cleanup_chunk_size')->value('value') ?: 1000);
        $archiveFirst  = WebsiteSetting::where('key', 'visitor_archive_before_delete')->value('value') === '1';
        $triggeredBy   = $this->option('triggered-by') ?: 'scheduler';

        $cutoffDate = Carbon::now()->subDays($retentionDays);

        // 3. Safety checks
        if (!Schema::hasTable('visitor_logs')) {
            $this->error('[visitor:cleanup] Table visitor_logs does not exist.');
            return self::FAILURE;
        }

        $totalEligible = DB::table('visitor_logs')
            ->where('visited_at', '<', $cutoffDate)
            ->orWhere(function ($q) use ($cutoffDate) {
                $q->whereNull('visited_at')->where('created_at', '<', $cutoffDate);
            })
            ->count();

        if ($totalEligible === 0) {
            $this->info("[visitor:cleanup] No records older than {$retentionDays} days found. Nothing to cleanup.");
            return self::SUCCESS;
        }

        $this->info("[visitor:cleanup] Starting cleanup. Records eligible: {$totalEligible} | Retention: {$retentionDays} days | Chunk: {$chunkSize} | Archive: " . ($archiveFirst ? 'YES' : 'NO'));

        // 4. Start audit log (if table exists)
        $cleanupLog = Schema::hasTable('visitor_cleanup_logs') ? VisitorCleanupLog::create([
            'started_at'       => now(),
            'status'           => 'running',
            'retention_days'   => $retentionDays,
            'triggered_by'     => $triggeredBy,
            'deleted_records'  => 0,
            'archived_records' => 0,
        ]) : null;

        $startTime       = microtime(true);
        $totalDeleted    = 0;
        $totalArchived   = 0;

        try {
            // 5. Summarise daily stats BEFORE deleting (preserve aggregate data)
            $this->aggregateDailySummaries($cutoffDate);

            // 6. Batch-delete in chunks
            do {
                // Get IDs for this batch
                $ids = DB::table('visitor_logs')
                    ->where(function ($q) use ($cutoffDate) {
                        $q->where('visited_at', '<', $cutoffDate)
                          ->orWhere(function ($inner) use ($cutoffDate) {
                              $inner->whereNull('visited_at')->where('created_at', '<', $cutoffDate);
                          });
                    })
                    ->limit($chunkSize)
                    ->pluck('id')
                    ->toArray();

                if (empty($ids)) {
                    break;
                }

                // Optional: Archive before deleting
                if ($archiveFirst && Schema::hasTable('visitor_logs_archive')) {
                    $rows = DB::table('visitor_logs')->whereIn('id', $ids)->get();
                    foreach ($rows->chunk(500) as $chunk) {
                        DB::table('visitor_logs_archive')->insert(
                            $chunk->map(fn ($r) => [
                                'original_id'              => $r->id,
                                'visitor_uuid'             => $r->visitor_uuid ?? null,
                                'session_id'               => $r->session_id ?? null,
                                'ip_address'               => $r->ip_address,
                                'url'                      => $r->url,
                                'method'                   => $r->method,
                                'user_agent'               => $r->user_agent,
                                'browser'                  => $r->browser,
                                'browser_version'          => $r->browser_version ?? null,
                                'operating_system'         => $r->operating_system ?? $r->platform ?? null,
                                'operating_system_version' => $r->operating_system_version ?? null,
                                'device_type'              => $r->device_type ?? $r->device ?? null,
                                'is_robot'                 => $r->is_robot ?? 0,
                                'referrer'                 => $r->referrer ?? $r->referrer ?? null,
                                'visited_at'               => $r->visited_at ?? $r->created_at,
                                'archived_at'              => now(),
                                'created_at'               => $r->created_at,
                                'updated_at'               => $r->updated_at,
                            ])->toArray()
                        );
                        $totalArchived += $chunk->count();
                    }
                }

                // Delete this batch
                $deleted = DB::table('visitor_logs')->whereIn('id', $ids)->delete();

                // Also delete orphaned geolocations (cascade should handle, but just in case)
                if (Schema::hasTable('visitor_geolocations')) {
                    DB::table('visitor_geolocations')->whereIn('visitor_log_id', $ids)->delete();
                }

                $totalDeleted += $deleted;

                $this->line("  → Deleted chunk: {$deleted} records (Total: {$totalDeleted})");

                // Small sleep to yield CPU and avoid I/O spikes
                usleep(50000); // 50ms

            } while (!empty($ids) && count($ids) === $chunkSize);

            // 7. Finalize audit log
            $duration = (int) (microtime(true) - $startTime);

            $cleanupLog?->update([
                'finished_at'      => now(),
                'deleted_records'  => $totalDeleted,
                'archived_records' => $totalArchived,
                'duration_seconds' => $duration,
                'status'           => 'completed',
                'error_message'    => null,
            ]);

            // 8. Update WebsiteSetting last cleanup metadata
            WebsiteSetting::updateOrCreate(
                ['key' => 'visitor_last_cleanup_at'],
                ['value' => now()->toDateTimeString(), 'group' => 'visitor_analytics']
            );
            WebsiteSetting::updateOrCreate(
                ['key' => 'visitor_last_cleanup_deleted'],
                ['value' => $totalDeleted, 'group' => 'visitor_analytics']
            );
            WebsiteSetting::clearCache();

            $this->info("[visitor:cleanup] ✅ Completed. Deleted: {$totalDeleted} records | Archived: {$totalArchived} | Duration: {$duration}s");

        } catch (\Throwable $e) {
            $duration = (int) (microtime(true) - $startTime);
            $cleanupLog?->update([
                'finished_at'      => now(),
                'deleted_records'  => $totalDeleted,
                'duration_seconds' => $duration,
                'status'           => 'failed',
                'error_message'    => $e->getMessage(),
            ]);

            Log::error('[visitor:cleanup] Cleanup failed: ' . $e->getMessage());
            $this->error('[visitor:cleanup] ❌ Cleanup FAILED: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Aggregate daily visit statistics from visitor_logs before they are deleted.
     * This preserves summary data even after logs are cleaned up.
     */
    private function aggregateDailySummaries(Carbon $cutoffDate): void
    {
        if (!Schema::hasTable('visitor_daily_summary')) {
            return;
        }

        $this->line('  → Aggregating daily summary statistics before cleanup...');

        // Get distinct dates that will be affected
        $dates = DB::table('visitor_logs')
            ->selectRaw('DATE(COALESCE(visited_at, created_at)) as log_date')
            ->where(function ($q) use ($cutoffDate) {
                $q->where('visited_at', '<', $cutoffDate)
                  ->orWhere(fn ($inner) => $inner->whereNull('visited_at')->where('created_at', '<', $cutoffDate));
            })
            ->groupBy('log_date')
            ->pluck('log_date');

        foreach ($dates as $date) {
            // Check if already summarised for this date
            $exists = DB::table('visitor_daily_summary')->where('summary_date', $date)->exists();
            if ($exists) {
                continue;
            }

            $stats = DB::table('visitor_logs')
                ->whereDate(DB::raw('COALESCE(visited_at, created_at)'), $date)
                ->selectRaw('
                    COUNT(*) as total_visits,
                    COUNT(DISTINCT COALESCE(visitor_uuid, ip_address)) as unique_visitors,
                    SUM(CASE WHEN LOWER(COALESCE(device_type, device)) = "desktop" THEN 1 ELSE 0 END) as desktop_count,
                    SUM(CASE WHEN LOWER(COALESCE(device_type, device)) = "mobile" THEN 1 ELSE 0 END) as mobile_count,
                    SUM(CASE WHEN LOWER(COALESCE(device_type, device)) = "tablet" THEN 1 ELSE 0 END) as tablet_count,
                    SUM(CASE WHEN LOWER(COALESCE(device_type, device)) = "robot" THEN 1 ELSE 0 END) as robot_count
                ')
                ->first();

            // Find top browser for that day
            $topBrowser = DB::table('visitor_logs')
                ->whereDate(DB::raw('COALESCE(visited_at, created_at)'), $date)
                ->whereNotNull('browser')
                ->selectRaw('browser, COUNT(*) as cnt')
                ->groupBy('browser')
                ->orderByDesc('cnt')
                ->first();

            DB::table('visitor_daily_summary')->updateOrInsert(
                ['summary_date' => $date],
                [
                    'total_visits'    => $stats->total_visits ?? 0,
                    'unique_visitors' => $stats->unique_visitors ?? 0,
                    'desktop_count'   => $stats->desktop_count ?? 0,
                    'mobile_count'    => $stats->mobile_count ?? 0,
                    'tablet_count'    => $stats->tablet_count ?? 0,
                    'robot_count'     => $stats->robot_count ?? 0,
                    'top_browser'     => $topBrowser->browser ?? null,
                    'top_browser_count' => $topBrowser->cnt ?? 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }

        $this->line('  → Daily summary aggregation complete.');
    }
}
