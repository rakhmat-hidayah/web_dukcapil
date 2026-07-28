<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\VisitorLog; // Let's make sure we create this Model
use App\Services\AgentParser;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Perform logging after response is sent to optimize load time (terminable middleware concept)
        // Check if we should track this request
        if ($this->shouldTrack($request, $response)) {
            try {
                $ua = $request->userAgent() ?? '';
                $parsed = AgentParser::parse($ua);

                // Insert log
                \DB::table('visitor_logs')->insert([
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_agent' => substr($ua, 0, 500),
                    'browser' => $parsed['browser'],
                    'device' => $parsed['device'],
                    'platform' => $parsed['platform'],
                    'referrer' => substr($request->headers->get('referer') ?? '', 0, 500),
                    'user_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Fail silently to prevent site crash due to log writing error
                \Log::error('Visitor tracking failed: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Determine if the request should be tracked.
     */
    private function shouldTrack(Request $request, Response $response): bool
    {
        // Only track GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }

        // Only track successful or redirecting HTML pages
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $path = $request->path();

        // Skip assets, API routes, health check, and hot reloads
        $skips = [
            'api/*',
            'sanctum/*',
            'livewire/*',
            '_debugbar/*',
            '_ignition/*',
            'up',
            'build/*',
            'vendor/*',
            'storage/*',
            'admin/media-library/*', // skip media library loads
        ];

        foreach ($skips as $skip) {
            if ($request->fullUrlIs($skip) || $request->is($skip)) {
                return false;
            }
        }

        // Check content type if available to ensure it's a page load, not a json/asset fetch
        $contentType = $response->headers->get('Content-Type');
        if ($contentType && !str_contains($contentType, 'text/html') && !str_contains($contentType, 'application/json')) {
            return false;
        }

        return true;
    }
}
