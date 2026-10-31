<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Standard security headers (all environments)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(self), payment=()');

        // HSTS — only on production HTTPS (Plesk + SSL)
        // Instructs browsers to always use HTTPS for 1 year, including subdomains
        if (config('app.env') === 'production' || config('app.env') === 'staging') {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content-Security-Policy
        // Whitelist: self, trusted CDNs, Google Fonts, OpenStreetMap, YouTube
        $siteUrl = rtrim(config('app.url'), '/');
        $domain  = parse_url($siteUrl, PHP_URL_HOST) ?? 'dukcapil.dompukab.go.id';

        $cspParts = [
            "default-src 'self';",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com;",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com;",
            "img-src 'self' data: blob: https://images.unsplash.com"
                . " https://a.tile.openstreetmap.org https://b.tile.openstreetmap.org"
                . " https://c.tile.openstreetmap.org"
                . " https://i.ytimg.com https://img.youtube.com;",
            "font-src 'self' https://fonts.gstatic.com;",
            "connect-src 'self' https://cloudflareinsights.com"
                . " https://a.tile.openstreetmap.org https://b.tile.openstreetmap.org"
                . " https://c.tile.openstreetmap.org;",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com;",
            "media-src 'self' blob: data:;",
            "object-src 'none';",
            "base-uri 'self';",
            "form-action 'self';",
        ];

        if ($request->isSecure() || (config('app.env') === 'production' && !str_ends_with($request->getHost(), '.test'))) {
            $cspParts[] = "upgrade-insecure-requests;";
        }

        $response->headers->set('Content-Security-Policy', implode(' ', $cspParts));

        return $response;
    }
}
