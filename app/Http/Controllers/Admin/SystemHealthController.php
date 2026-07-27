<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Log;

class SystemHealthController extends Controller
{
    /**
     * Display the System Health Dashboard.
     */
    public function index(): InertiaResponse
    {
        $checks = [
            'php_version'    => PHP_VERSION,
            'laravel_version'=> app()->version(),
            'environment'    => config('app.env'),
            'debug_mode'     => config('app.debug'),
            'app_url'        => config('app.url'),
            'timezone'       => config('app.timezone'),
        ];

        // Database connectivity check
        $dbStatus = $this->checkDatabase();

        // Cache connectivity check
        $cacheStatus = $this->checkCache();

        // Storage check
        $storageStatus = $this->checkStorage();

        // Disk usage
        $diskTotal = disk_total_space(storage_path());
        $diskFree  = disk_free_space(storage_path());
        $diskUsed  = $diskTotal - $diskFree;
        $diskUsedPct = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        // Memory usage
        $memUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memPeak  = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        // Queue status (check pending jobs)
        $queueStatus = $this->checkQueue();

        // Today's visitors
        $visitorToday = \App\Models\VisitorLog::whereDate('created_at', today())->count();

        // Failed login count today
        $failedLoginToday = \Spatie\Activitylog\Models\Activity::where('event', 'login_failed')
            ->whereDate('created_at', today())
            ->count();

        // Recent errors from activity log
        $errorCount = \Spatie\Activitylog\Models\Activity::where('event', 'error')
            ->whereDate('created_at', today())
            ->count();

        // Pending complaints
        $pendingComplaints = \App\Models\Complaint::where('status', 'pending')->count();

        // Pending PPID requests
        $pendingPpid = \App\Models\PpidRequest::where('status', 'pending')->count();

        // Total users
        $totalUsers = \App\Models\User::count();

        // Unpublished news
        $draftNews = \App\Models\News::where('status', 'draft')->count();

        // Check if storage symlink exists
        $storageLink = file_exists(public_path('storage')) && is_link(public_path('storage'));

        // Uptime (approximate from app start — use process ID trick or just show server time)
        $serverTime = now()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s T');

        return Inertia::render('Admin/System/Health', [
            'system'    => $checks,
            'db'        => $dbStatus,
            'cache'     => $cacheStatus,
            'storage'   => $storageStatus,
            'storageLink' => $storageLink,
            'disk'      => [
                'total'   => $this->formatBytes($diskTotal),
                'free'    => $this->formatBytes($diskFree),
                'used'    => $this->formatBytes($diskUsed),
                'used_pct'=> $diskUsedPct,
                'status'  => $diskUsedPct > 90 ? 'critical' : ($diskUsedPct > 75 ? 'warning' : 'healthy'),
            ],
            'memory'    => [
                'usage'   => $memUsage . ' MB',
                'peak'    => $memPeak . ' MB',
                'status'  => $memUsage > 256 ? 'warning' : 'healthy',
            ],
            'queue'     => $queueStatus,
            'stats'     => [
                'visitor_today'      => $visitorToday,
                'failed_login_today' => $failedLoginToday,
                'error_count'        => $errorCount,
                'pending_complaints' => $pendingComplaints,
                'pending_ppid'       => $pendingPpid,
                'total_users'        => $totalUsers,
                'draft_news'         => $draftNews,
            ],
            'server_time'    => $serverTime,
            'overall_status' => $this->computeOverallStatus($dbStatus, $cacheStatus, $storageStatus, $diskUsedPct),
        ]);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $dbVersion = DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown';
            return ['status' => 'healthy', 'message' => 'Connected', 'version' => $dbVersion];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Connection failed: ' . $e->getMessage(), 'version' => 'N/A'];
        }
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'ok', 5);
            $val = Cache::get($testKey);
            Cache::forget($testKey);
            return $val === 'ok'
                ? ['status' => 'healthy', 'message' => 'Cache working', 'driver' => config('cache.default')]
                : ['status' => 'warning', 'message' => 'Cache read mismatch', 'driver' => config('cache.default')];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Cache error: ' . $e->getMessage(), 'driver' => config('cache.default')];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            Storage::put($testFile, 'ok');
            $content = Storage::get($testFile);
            Storage::delete($testFile);
            return $content === 'ok'
                ? ['status' => 'healthy', 'message' => 'Storage writable', 'disk' => config('filesystems.default')]
                : ['status' => 'warning', 'message' => 'Storage write mismatch', 'disk' => config('filesystems.default')];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => 'Storage error: ' . $e->getMessage(), 'disk' => config('filesystems.default')];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed  = DB::table('failed_jobs')->count();
            return [
                'status'  => $failed > 10 ? 'critical' : ($failed > 0 ? 'warning' : 'healthy'),
                'pending' => $pending,
                'failed'  => $failed,
                'driver'  => config('queue.default'),
            ];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'pending' => 0, 'failed' => 0, 'driver' => config('queue.default')];
        }
    }

    private function computeOverallStatus(array $db, array $cache, array $storage, float $diskPct): string
    {
        if ($db['status'] === 'critical') return 'critical';
        if ($storage['status'] === 'critical') return 'critical';
        if ($diskPct > 90) return 'critical';
        if ($cache['status'] === 'warning' || $db['status'] === 'warning') return 'warning';
        if ($diskPct > 75) return 'warning';
        return 'healthy';
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
