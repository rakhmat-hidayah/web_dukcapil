<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVisitorGeolocationJob;
use App\Models\VisitorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisitorAnalyticsController extends Controller
{
    /** Check if the visitor_geolocations table exists (safe guard). */
    private static function geoTableExists(): bool
    {
        static $exists = null;
        if ($exists === null) {
            $exists = Schema::hasTable('visitor_geolocations');
        }
        return $exists;
    }

    /** Safe date formatter to prevent 500 errors if date columns are null or strings */
    private static function formatDate($date): string
    {
        if (!$date) {
            return now()->format('d M Y, H:i:s');
        }
        if ($date instanceof \DateTimeInterface) {
            return $date->format('d M Y, H:i:s');
        }
        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d M Y, H:i:s');
        } catch (\Throwable) {
            return (string) $date;
        }
    }

    /**
     * Display Visitor Analytics Dashboard & Paginated Visitor Logs.
     */
    public function index(Request $request): Response
    {
        $today = today();
        $fiveMinsAgo = now()->subMinutes(5);

        $hasIntelCols = Schema::hasColumn('visitor_logs', 'visitor_category');

        // Auto-fix historical records that were set to is_human = 1 by default migration
        if ($hasIntelCols) {
            try {
                VisitorLog::where('is_robot', true)
                    ->where('is_human', true)
                    ->limit(1000)
                    ->update([
                        'is_human'         => false,
                        'visitor_category' => 'unknown_bot',
                        'bot_name'         => 'Automated Crawler / Robot',
                        'detection_reason' => 'Historical Robot Record',
                    ]);
            } catch (\Throwable) {}
        }

        // 1. Summary Cards Data (strictly isolated - Humans vs Bots vs Security Scanners)
        try {
            $humanQuery = VisitorLog::whereDate('created_at', $today);
            if ($hasIntelCols) {
                $humanQuery->where('is_human', true)
                           ->where('is_robot', false)
                           ->where(fn($q) => $q->whereNull('visitor_category')->orWhere('visitor_category', 'human'));
            } else {
                $humanQuery->where('is_robot', false);
            }

            $uniqueHumanQuery = VisitorLog::whereDate('created_at', $today);
            if ($hasIntelCols) {
                $uniqueHumanQuery->where('is_human', true)
                                 ->where('is_robot', false)
                                 ->where(fn($q) => $q->whereNull('visitor_category')->orWhere('visitor_category', 'human'));
            } else {
                $uniqueHumanQuery->where('is_robot', false);
            }

            $botQuery = VisitorLog::whereDate('created_at', $today);
            if ($hasIntelCols) {
                $botQuery->where(function ($q) {
                    $q->where('is_human', false)
                      ->orWhere('is_robot', true)
                      ->orWhere(fn($sq) => $sq->whereNotNull('visitor_category')->where('visitor_category', '!=', 'human'));
                })->where('visitor_category', '!=', 'security_scanner');
            } else {
                $botQuery->where('is_robot', true);
            }

            $securityQuery = VisitorLog::whereDate('created_at', $today);
            if ($hasIntelCols) {
                $securityQuery->where('visitor_category', 'security_scanner');
            } else {
                $securityQuery->whereRaw('1=0');
            }

            $topBotRow = $hasIntelCols
                ? VisitorLog::whereDate('created_at', $today)
                    ->where(fn ($q) => $q->where('is_human', false)->orWhere('is_robot', true))
                    ->whereNotNull('bot_name')
                    ->selectRaw('bot_name, COUNT(*) as cnt')
                    ->groupBy('bot_name')
                    ->orderByDesc('cnt')
                    ->first()
                : null;

            $summary = [
                'human_visitors'  => $humanQuery->count(),
                'unique_humans'   => $uniqueHumanQuery->distinct('visitor_uuid')->count('visitor_uuid'),
                'bot_visitors'    => $botQuery->count(),
                'security_scans'  => $securityQuery->count(),
                'online_visitors' => VisitorLog::where('created_at', '>=', $fiveMinsAgo)
                    ->when($hasIntelCols, fn ($q) => $q->where('is_human', true)->where('is_robot', false))
                    ->distinct('visitor_uuid')
                    ->count('visitor_uuid'),
                'top_bot'         => $topBotRow->bot_name ?? ($hasIntelCols ? 'Tidak ada bot' : 'N/A'),
            ];
        } catch (\Throwable) {
            $summary = [
                'human_visitors'  => 0,
                'unique_humans'   => 0,
                'bot_visitors'    => 0,
                'security_scans'  => 0,
                'online_visitors' => 0,
                'top_bot'         => 'N/A',
            ];
        }

        // 2. Build Paginated Visitor Logs Query
        try {
            $query = self::geoTableExists()
                ? VisitorLog::with('geolocation')->orderBy('id', 'desc')
                : VisitorLog::orderBy('id', 'desc');

            // Apply Traffic Type Filter (human | bots | security | all) - Default: human
            $trafficType = $request->get('traffic_type', 'human');
            if ($hasIntelCols) {
                if ($trafficType === 'human') {
                    $query->where('is_human', true)
                          ->where('is_robot', false)
                          ->where(fn ($q) => $q->whereNull('visitor_category')->orWhere('visitor_category', 'human'));
                } elseif ($trafficType === 'bots') {
                    $query->where(fn ($q) => $q->where('is_human', false)->orWhere('is_robot', true)->orWhere(fn($sq) => $sq->whereNotNull('visitor_category')->where('visitor_category', '!=', 'human')))
                          ->where('visitor_category', '!=', 'security_scanner');
                } elseif ($trafficType === 'security') {
                    $query->where('visitor_category', 'security_scanner');
                }
            } else {
                if ($trafficType === 'human') {
                    $query->where('is_robot', false);
                } elseif ($trafficType === 'bots' || $trafficType === 'security') {
                    $query->where('is_robot', true);
                }
            }

            // Apply Search Filter
            if ($search = trim($request->get('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('ip_address', 'like', "%{$search}%")
                      ->orWhere('url', 'like', "%{$search}%")
                      ->orWhere('visitor_uuid', 'like', "%{$search}%")
                      ->orWhere('user_agent', 'like', "%{$search}%")
                      ->orWhere('bot_name', 'like', "%{$search}%")
                      ->orWhere('detection_reason', 'like', "%{$search}%");
                });
            }

            // Apply Date Filter
            if ($date = $request->get('date')) {
                $query->whereDate('created_at', $date);
            }

            // Apply Browser Filter
            if ($browser = $request->get('browser')) {
                $query->where('browser', $browser);
            }

            // Apply Device Filter
            if ($device = $request->get('device')) {
                $query->where('device_type', $device);
            }

            // Apply Geolocation Status Filter (only if table exists)
            if ($status = $request->get('status')) {
                if (self::geoTableExists()) {
                    $query->whereHas('geolocation', function ($q) use ($status) {
                        $q->where('status', $status);
                    });
                }
            }

            $logs = $query->paginate(25)->withQueryString()->through(function ($log) {
                $geo = self::geoTableExists() ? ($log->geolocation ?? null) : null;
                return [
                    'id'                       => $log->id,
                    'visitor_uuid'             => $log->visitor_uuid ?? 'N/A',
                    'session_id'               => $log->session_id ?? 'N/A',
                    'full_ip'                  => $log->ip_address,
                    'masked_ip'                => $log->masked_ip,
                    'url'                      => $log->url,
                    'referrer'                 => $log->referrer,
                    'method'                   => $log->method,
                    'browser'                  => $log->browser ?? 'Unknown',
                    'browser_version'          => $log->browser_version,
                    'operating_system'         => $log->operating_system ?? $log->platform ?? 'Unknown',
                    'operating_system_version' => $log->operating_system_version,
                    'device_type'              => $log->device_type ?? $log->device ?? 'Desktop',
                    'is_robot'                 => (bool) $log->is_robot,
                    'is_human'                 => isset($log->is_human) ? (bool) $log->is_human : ! (bool) $log->is_robot,
                    'visitor_category'         => $log->visitor_category ?? ((bool) $log->is_robot ? 'unknown_bot' : 'human'),
                    'bot_name'                 => $log->bot_name ?? null,
                    'detection_reason'         => $log->detection_reason ?? ((bool) $log->is_robot ? 'Matched User Agent: Robot' : 'Legitimate Human Session'),
                    'visited_at'               => self::formatDate($log->visited_at ?? $log->created_at),
                    'visited_time_ago'         => $log->created_at 
                        ? ($log->created_at instanceof \DateTimeInterface ? $log->created_at->diffForHumans() : \Illuminate\Support\Carbon::parse($log->created_at)->diffForHumans()) 
                        : 'Baru saja',
                    'geolocation'              => $geo ? [
                        'status'        => $geo->status,
                        'country'       => $geo->country,
                        'country_code'  => $geo->country_code,
                        'province'      => $geo->province,
                        'city'          => $geo->city,
                        'district'      => $geo->district ?? '-',
                        'isp'           => $geo->isp ?? '-',
                        'asn'           => $geo->asn ?? '-',
                        'confidence'    => $geo->confidence ?? 'medium',
                        'ip_version'    => $geo->ip_version ?? (str_contains($log->ip_address, ':') ? 'IPv6' : 'IPv4'),
                        'timezone'      => $geo->timezone,
                        'latitude'      => $geo->latitude,
                        'longitude'     => $geo->longitude,
                        'error_message' => $geo->error_message,
                    ] : [
                        'status'        => 'pending',
                        'country'       => null,
                        'province'      => null,
                        'city'          => null,
                        'district'      => null,
                        'isp'           => null,
                        'asn'           => null,
                        'confidence'    => null,
                        'ip_version'    => str_contains($log->ip_address, ':') ? 'IPv6' : 'IPv4',
                        'timezone'      => null,
                        'latitude'      => null,
                        'longitude'     => null,
                        'error_message' => null,
                    ],
                ];
            });

            // 3. Unique Filter Options for UI Dropdowns
            $browserOptions = VisitorLog::whereNotNull('browser')->distinct()->pluck('browser');

        } catch (\Throwable $e) {
            // Fallback if visitor_logs table doesn't exist yet
            $logs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            $browserOptions = collect([]);
        }

        $deviceOptions = ['Desktop', 'Mobile', 'Tablet', 'Robot'];

        return Inertia::render('Admin/VisitorAnalytics/Index', [
            'summary'        => $summary,
            'logs'           => $logs,
            'filters'        => [
                'search'       => $request->get('search', ''),
                'traffic_type' => $request->get('traffic_type', 'human'),
                'date'         => $request->get('date', ''),
                'browser'      => $request->get('browser', ''),
                'device'       => $request->get('device', ''),
                'status'       => $request->get('status', ''),
            ],
            'browserOptions' => $browserOptions,
            'deviceOptions'  => $deviceOptions,
        ]);
    }

    /**
     * Get JSON detail for single visitor log modal.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $log = self::geoTableExists()
                ? VisitorLog::with('geolocation')->findOrFail($id)
                : VisitorLog::findOrFail($id);

            $geo = self::geoTableExists() ? ($log->geolocation ?? null) : null;

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                       => $log->id,
                    'visitor_uuid'             => $log->visitor_uuid,
                    'session_id'               => $log->session_id,
                    'ip_address'               => $log->ip_address,
                    'masked_ip'                => $log->masked_ip,
                    'url'                      => $log->url,
                    'referrer'                 => $log->referrer,
                    'method'                   => $log->method,
                    'user_agent'               => $log->user_agent,
                    'browser'                  => $log->browser,
                    'browser_version'          => $log->browser_version,
                    'operating_system'         => $log->operating_system ?? $log->platform,
                    'operating_system_version' => $log->operating_system_version,
                    'device_type'              => $log->device_type ?? $log->device,
                    'is_robot'                 => (bool) $log->is_robot,
                    'is_human'                 => isset($log->is_human) ? (bool) $log->is_human : ! (bool) $log->is_robot,
                    'visitor_category'         => $log->visitor_category ?? ((bool) $log->is_robot ? 'unknown_bot' : 'human'),
                    'bot_name'                 => $log->bot_name ?? null,
                    'detection_reason'         => $log->detection_reason ?? ((bool) $log->is_robot ? 'Matched User Agent: Robot' : 'Legitimate Human Session'),
                    'visited_at'               => $log->created_at ? $log->created_at->format('d M Y, H:i:s') : now()->format('d M Y, H:i:s'),
                    'geolocation'              => $geo ? [
                        'status'        => $geo->status,
                        'country'       => $geo->country,
                        'country_code'  => $geo->country_code,
                        'province'      => $geo->province,
                        'city'          => $geo->city,
                        'timezone'      => $geo->timezone,
                        'latitude'      => $geo->latitude,
                        'longitude'     => $geo->longitude,
                        'error_message' => $geo->error_message,
                    ] : ['status' => 'pending'],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retry Geolocation queue job for failed/pending log.
     */
    public function retryGeolocation(int $id)
    {
        $log = VisitorLog::findOrFail($id);

        if (class_exists(ProcessVisitorGeolocationJob::class)) {
            ProcessVisitorGeolocationJob::dispatch($log->id, $log->ip_address);
        }

        return back()->with('success', 'Queue Geolocation berhasil dikirim ulang untuk IP ' . $log->ip_address);
    }

    /**
     * Export Visitor Logs to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = self::geoTableExists()
            ? VisitorLog::with('geolocation')->orderBy('id', 'desc')
            : VisitorLog::orderBy('id', 'desc');

        if ($search = trim($request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('visitor_uuid', 'like', "%{$search}%");
            });
        }

        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        }

        $filename = 'visitor_analytics_' . date('Y-m-d_H-i-s') . '.csv';
        $geoExists = self::geoTableExists();

        return response()->streamDownload(function () use ($query, $geoExists) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Visitor UUID', 'Session ID', 'IP Address', 'Browser', 'OS', 'Device',
                'URL', 'Referrer', 'Visited At', 'Country', 'Province', 'City', 'Geo Status'
            ]);

            $query->chunk(500, function ($logs) use ($handle, $geoExists) {
                foreach ($logs as $log) {
                    $geo = $geoExists ? ($log->geolocation ?? null) : null;
                    fputcsv($handle, [
                        $log->id,
                        $log->visitor_uuid,
                        $log->session_id,
                        $log->ip_address,
                        $log->browser . ($log->browser_version ? ' ' . $log->browser_version : ''),
                        ($log->operating_system ?? $log->platform) . ($log->operating_system_version ? ' ' . $log->operating_system_version : ''),
                        $log->device_type ?? $log->device,
                        $log->url,
                        $log->referrer,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $geo ? $geo->country : '-',
                        $geo ? $geo->province : '-',
                        $geo ? $geo->city : '-',
                        $geo ? $geo->status : 'pending',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
