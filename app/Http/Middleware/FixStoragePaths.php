<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fix Storage Paths Middleware
 *
 * Replaces all /storage/ URL references with /uploads/ in HTML and JSON responses.
 * This handles:
 *  - Article body HTML with embedded images from TipTap file_manager
 *  - Any remaining /storage/ references in JSON (Inertia responses)
 *  - og:image, meta image tags, etc.
 */
class FixStoragePaths
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = $response->headers->get('Content-Type', '');

        // Only process HTML and JSON responses
        if (!str_contains($contentType, 'text/html') && !str_contains($contentType, 'application/json')) {
            return $response;
        }

        // Skip binary / streaming responses
        if (!method_exists($response, 'getContent')) {
            return $response;
        }

        $content = $response->getContent();

        if (!$content || strlen($content) < 10) {
            return $response;
        }

        // Replace all /storage/ references with /uploads/
        // Pattern covers: /storage/, storage/ (relative), "storage/, 'storage/, =storage/
        $original = $content;

        $content = str_replace(
            [
                '"/storage/',
                "'/storage/",
                '>/storage/',
                '=/storage/',
                '\\/storage\\/',
                '"storage/file_manager',
                "'storage/file_manager",
            ],
            [
                '"/uploads/',
                "'/uploads/",
                '>/uploads/',
                '=/uploads/',
                '\\/uploads\\/',
                '"uploads/file_manager',
                "'uploads/file_manager",
            ],
            $content
        );

        if ($content !== $original) {
            $response->setContent($content);
        }

        return $response;
    }
}
