<?php

namespace App\Http\Middleware;

use App\Services\VisitorLoggerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            VisitorLoggerService::log($request);
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (!$request->isMethod('GET') || $response->getStatusCode() >= 400) {
            return false;
        }

        $skips = [
            'api/*', 'sanctum/*', 'livewire/*', '_debugbar/*', '_ignition/*', 'up',
            'build/*', 'vendor/*', 'storage/*', 'admin/*', 'admin',
        ];

        foreach ($skips as $skip) {
            if ($request->is($skip)) {
                return false;
            }
        }

        return true;
    }
}
