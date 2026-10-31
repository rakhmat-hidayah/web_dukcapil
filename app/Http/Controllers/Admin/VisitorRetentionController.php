<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\VisitorCleanupCommand;
use App\Http\Controllers\Controller;
use App\Models\VisitorCleanupLog;
use App\Models\VisitorLog;
use App\Models\WebsiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class VisitorRetentionController extends Controller
{
    /**
     * Visitor Analytics Settings & Storage Dashboard.
     */
    public function index(): Response
    {
        $settings = WebsiteSetting::where('group', 'visitor_analytics')->pluck('value', 'key')->toArray();

        // Database storage metrics
        $storageInfo = $this->getStorageInfo();

        // Cleanup history (latest 15) with table safety guard
        try {
            $cleanupHistory = Schema::hasTable('visitor_cleanup_logs')
                ? VisitorCleanupLog::orderBy('id', 'desc')->limit(15)->get()->map(fn ($log) => [
                    'id'               => $log->id,
                    'started_at'       => $log->started_at ? ($log->started_at instanceof \DateTimeInterface ? $log->started_at->format('d M Y, H:i:s') : (string) $log->started_at) : '-',
                    'finished_at'      => $log->finished_at ? ($log->finished_at instanceof \DateTimeInterface ? $log->finished_at->format('d M Y, H:i:s') : (string) $log->finished_at) : '-',
                    'deleted_records'  => number_format($log->deleted_records ?? 0),
                    'archived_records' => number_format($log->archived_records ?? 0),
                    'deleted_size_mb'  => $log->deleted_size_mb ?? 0,
                    'status'           => $log->status,
                    'duration_seconds' => $log->duration_seconds,
                    'retention_days'   => $log->retention_days,
                    'triggered_by'     => $log->triggered_by,
                    'error_message'    => $log->error_message,
                ])
                : collect([]);

            $cleanupRunning = Schema::hasTable('visitor_cleanup_logs')
                ? VisitorCleanupLog::where('status', 'running')->exists()
                : false;
        } catch (\Throwable $e) {
            $cleanupHistory = collect([]);
            $cleanupRunning = false;
        }

        return Inertia::render('Admin/VisitorAnalytics/Retention', [
            'settings'        => $settings,
            'storageInfo'     => $storageInfo,
            'cleanupHistory'  => $cleanupHistory,
            'cleanupRunning'  => $cleanupRunning,
        ]);
    }

    /**
     * Update Visitor Analytics Settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'visitor_logging_enabled'      => 'required|in:0,1',
            'visitor_auto_cleanup_enabled' => 'required|in:0,1',
            'visitor_retention_days'       => 'required|in:30,60,90,180,365',
            'visitor_cleanup_schedule'     => 'required|in:daily,weekly,monthly',
            'visitor_cleanup_time'         => 'required|in:00:00,01:00,02:00,03:00,04:00,05:00',
            'visitor_archive_before_delete'=> 'required|in:0,1',
            'visitor_cleanup_chunk_size'   => 'required|in:500,1000,2000,5000',
        ]);

        $keys = [
            'visitor_logging_enabled', 'visitor_auto_cleanup_enabled',
            'visitor_retention_days', 'visitor_cleanup_schedule',
            'visitor_cleanup_time', 'visitor_archive_before_delete',
            'visitor_cleanup_chunk_size',
        ];

        foreach ($keys as $key) {
            WebsiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key), 'group' => 'visitor_analytics']
            );
        }

        WebsiteSetting::clearCache();

        return back()->with('success', 'Pengaturan Visitor Analytics berhasil diperbarui!');
    }

    /**
     * Trigger Manual Cleanup Now.
     */
    public function cleanupNow(Request $request): JsonResponse
    {
        // Auto-run retention migration if tables do not exist yet on server
        if (!Schema::hasTable('visitor_cleanup_logs')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable) {
                // Ignore migration errors if already up to date
            }
        }

        // Prevent double-running (safely)
        try {
            if (Schema::hasTable('visitor_cleanup_logs') && VisitorCleanupLog::where('status', 'running')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proses cleanup sedang berjalan. Tunggu hingga selesai sebelum menjalankan lagi.',
                ], 409);
            }
        } catch (\Throwable) {}

        try {
            // Run Artisan cleanup directly
            Artisan::call('visitor:cleanup', [
                '--force'        => true,
                '--triggered-by' => 'manual',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proses cleanup berhasil dijalankan!',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai cleanup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get storage info for the retention dashboard.
     */
    private function getStorageInfo(): array
    {
        try {
            $settings = WebsiteSetting::where('group', 'visitor_analytics')->pluck('value', 'key')->toArray();
            $retentionDays = (int) ($settings['visitor_retention_days'] ?? 90);

            $totalLogs = DB::table('visitor_logs')->count();
            $eligibleForCleanup = DB::table('visitor_logs')
                ->where(function ($q) use ($retentionDays) {
                    $cutoff = now()->subDays($retentionDays);
                    $q->where('visited_at', '<', $cutoff)
                      ->orWhere(fn ($inner) => $inner->whereNull('visited_at')->where('created_at', '<', $cutoff));
                })
                ->count();

            // Table size in MB (MySQL)
            $tableSize = DB::select("
                SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'visitor_logs'
            ");
            $sizeMb = $tableSize[0]->size_mb ?? 0;

            // Archive table size
            $archiveSize = DB::select("
                SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'visitor_logs_archive'
            ");
            $archiveSizeMb = $archiveSize[0]->size_mb ?? 0;

            // Last cleanup info (safely check if table exists)
            $lastLog = Schema::hasTable('visitor_cleanup_logs') 
                ? VisitorCleanupLog::where('status', 'completed')->orderBy('id', 'desc')->first() 
                : null;

            // Next cleanup (schedule based)
            $cleanupTime = $settings['visitor_cleanup_time'] ?? '02:00';
            $schedule = $settings['visitor_cleanup_schedule'] ?? 'daily';
            $nextCleanup = $this->calculateNextCleanup($schedule, $cleanupTime);

            return [
                'total_logs'           => number_format($totalLogs),
                'eligible_for_cleanup' => number_format($eligibleForCleanup),
                'table_size_mb'        => $sizeMb,
                'archive_size_mb'      => $archiveSizeMb,
                'last_cleanup_at'      => $lastLog?->finished_at?->format('d M Y, H:i'),
                'last_cleanup_deleted' => $lastLog ? number_format($lastLog->deleted_records) : '0',
                'next_cleanup'         => $nextCleanup,
                'archive_status'       => Schema::hasTable('visitor_logs_archive') ? 'Available' : 'Not Set',
                'retention_days'       => $retentionDays,
            ];
        } catch (\Throwable $e) {
            return [
                'total_logs'           => '0',
                'eligible_for_cleanup' => '0',
                'table_size_mb'        => 0,
                'archive_size_mb'      => 0,
                'last_cleanup_at'      => null,
                'last_cleanup_deleted' => '0',
                'next_cleanup'         => 'N/A',
                'archive_status'       => 'N/A',
                'retention_days'       => 90,
            ];
        }
    }

    private function calculateNextCleanup(string $schedule, string $time): string
    {
        [$hour, $minute] = explode(':', $time . ':00');
        $now = now()->timezone('Asia/Makassar');

        $next = match ($schedule) {
            'weekly'  => $now->copy()->next('Monday')->setTime((int) $hour, (int) $minute),
            'monthly' => $now->copy()->startOfNextMonth()->setTime((int) $hour, (int) $minute),
            default   => $now->copy()->addDay()->setTime((int) $hour, (int) $minute),
        };

        return $next->format('d M Y, H:i') . ' WITA';
    }

    /**
     * Poll cleanup status (AJAX polling from frontend).
     */
    public function cleanupStatus(): JsonResponse
    {
        try {
            $hasTable = Schema::hasTable('visitor_cleanup_logs');
            $running  = $hasTable ? VisitorCleanupLog::where('status', 'running')->first() : null;
            $last     = $hasTable ? VisitorCleanupLog::where('status', '!=', 'running')->orderBy('id', 'desc')->first() : null;

            return response()->json([
                'running'           => (bool) $running,
                'last_status'       => $last?->status,
                'last_deleted'      => $last ? number_format($last->deleted_records ?? 0) : '0',
                'last_finished_at'  => $last?->finished_at ? ($last->finished_at instanceof \DateTimeInterface ? $last->finished_at->format('d M Y, H:i:s') : (string) $last->finished_at) : null,
                'last_duration'     => $last?->duration_seconds,
                'last_error'        => $last?->error_message,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'running'     => false,
                'last_status' => null,
            ]);
        }
    }
}
