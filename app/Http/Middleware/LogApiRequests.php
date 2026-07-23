<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        try {
            $duration = round((microtime(true) - $startTime) * 1000); // ms
            $apiKeyModel = $request->attributes->get('api_key_model');

            DB::table('api_logs')->insert([
                'api_key_id' => $apiKeyModel ? $apiKeyModel->id : null,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'response_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail silently to prevent crashing client responses
            \Log::error('API Request logging failed: ' . $e->getMessage());
        }

        return $response;
    }
}
