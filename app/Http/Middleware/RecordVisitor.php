<?php

namespace App\Http\Middleware;

use App\Services\VisitorLoggerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordVisitor
{
    /**
     * Handle an incoming request and record visitor analytics.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldRecord($request, $response)) {
            VisitorLoggerService::log($request);
        }

        return $response;
    }

    /**
     * Determine if request should be recorded.
     */
    private function shouldRecord(Request $request, Response $response): bool
    {
        // 1. Only record GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }

        // 2. Only record successful or redirecting responses (< 400 status)
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        // 3. Skip static assets, internal endpoints, and administrative routes
        $path = $request->path();

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
            'admin/*',
            'admin',
        ];

        foreach ($skips as $skip) {
            if ($request->is($skip)) {
                return false;
            }
        }

        return true;
    }
}
