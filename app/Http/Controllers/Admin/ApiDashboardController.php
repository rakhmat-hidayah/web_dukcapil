<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ApiDashboardController extends Controller
{
    /**
     * Display the API usage and performance monitor dashboard.
     */
    public function index(): InertiaResponse
    {
        // 1. Core KPIs
        $totalRequests = DB::table('api_logs')->count();
        $avgDuration = DB::table('api_logs')->avg('duration_ms') ?? 0;
        $failedRequests = DB::table('api_logs')->where('response_code', '>=', 400)->count();
        $rateLimitViolations = DB::table('api_logs')->where('response_code', 429)->count();

        // 2. Top Clients (joined with api_keys)
        $topClients = DB::table('api_logs')
            ->leftJoin('api_keys', 'api_logs.api_key_id', '=', 'api_keys.id')
            ->select(DB::raw('COALESCE(api_keys.client_name, "Public User") as client_name'), DB::raw('count(*) as total'))
            ->groupBy('api_logs.api_key_id', 'api_keys.client_name')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 3. Top Endpoints
        $topEndpoints = DB::table('api_logs')
            ->select('endpoint', 'method', DB::raw('count(*) as total'))
            ->groupBy('endpoint', 'method')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 4. Failed Requests List
        $failures = DB::table('api_logs')
            ->leftJoin('api_keys', 'api_logs.api_key_id', '=', 'api_keys.id')
            ->select('api_logs.*', 'api_keys.client_name')
            ->where('api_logs.response_code', '>=', 400)
            ->orderBy('api_logs.created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($fail) {
                return [
                    'id' => $fail->id,
                    'client' => $fail->client_name ?? 'Public User',
                    'endpoint' => $fail->endpoint,
                    'method' => $fail->method,
                    'ip' => $fail->ip_address,
                    'code' => $fail->response_code,
                    'duration' => $fail->duration_ms,
                    'time' => date('d M Y, H:i', strtotime($fail->created_at)),
                ];
            });

        // 5. Hourly Request Volume Chart Data (last 24 hours)
        $last24Hours = [];
        for ($i = 23; $i >= 0; $i--) {
            $hourTime = date('Y-m-d H:00:00', strtotime("-{$i} hours"));
            $hourLabel = date('H:00', strtotime("-{$i} hours"));
            
            $count = DB::table('api_logs')
                ->where('created_at', '>=', $hourTime)
                ->where('created_at', '<', date('Y-m-d H:00:00', strtotime($hourTime . ' + 1 hour')))
                ->count();
                
            $last24Hours[] = [
                'label' => $hourLabel,
                'count' => $count,
            ];
        }

        return Inertia::render('Admin/ApiKeys/Dashboard', [
            'stats' => [
                'total_requests' => $totalRequests,
                'avg_duration' => round($avgDuration, 1),
                'failed_requests' => $failedRequests,
                'rate_limit_violations' => $rateLimitViolations,
            ],
            'topClients' => $topClients,
            'topEndpoints' => $topEndpoints,
            'failures' => $failures,
            'hourlyChart' => $last24Hours,
        ]);
    }
}
