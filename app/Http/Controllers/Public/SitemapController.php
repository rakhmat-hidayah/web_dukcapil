<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Page;
use App\Models\GalleryAlbum;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate a dynamic XML Sitemap for all published public content.
     */
    public function sitemap(): Response
    {
        $xml = Cache::remember('public_sitemap_xml', 3600, function () {
            $news   = News::published()->select('slug', 'updated_at')->orderBy('published_at', 'desc')->get();
            $pages  = Page::where('status', 'published')->select('slug', 'updated_at')->get();
            $albums = GalleryAlbum::where('is_published', true)->select('slug', 'updated_at')->get();

            $baseUrl = config('app.url');

            $items = [];

            // Static routes
            $staticRoutes = [
                ['url' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
                ['url' => $baseUrl . '/news', 'priority' => '0.9', 'changefreq' => 'daily'],
                ['url' => $baseUrl . '/gallery', 'priority' => '0.8', 'changefreq' => 'weekly'],
                ['url' => $baseUrl . '/downloads', 'priority' => '0.7', 'changefreq' => 'weekly'],
                ['url' => $baseUrl . '/contact', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ];

            foreach ($staticRoutes as $r) {
                $items[] = "<url><loc>{$r['url']}</loc><changefreq>{$r['changefreq']}</changefreq><priority>{$r['priority']}</priority></url>";
            }

            // Dynamic news pages
            foreach ($news as $item) {
                $loc = $baseUrl . '/news/' . $item->slug;
                $lastmod = $item->updated_at?->toAtomString();
                $items[] = "<url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>";
            }

            // Dynamic profile pages
            foreach ($pages as $page) {
                $loc = $baseUrl . '/page/' . $page->slug;
                $lastmod = $page->updated_at?->toAtomString();
                $items[] = "<url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>";
            }

            // Dynamic gallery albums
            foreach ($albums as $album) {
                $loc = $baseUrl . '/gallery/' . $album->slug;
                $lastmod = $album->updated_at?->toAtomString();
                $items[] = "<url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>";
            }

            $urlset = implode("\n    ", $items);

            return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {$urlset}
</urlset>
XML;
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Serve robots.txt allowing public pages, blocking admin and API internals.
     */
    public function robots(): Response
    {
        $baseUrl = config('app.url');

        $content = <<<ROBOTS
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /api/docs
Allow: /api/v1/

Sitemap: {$baseUrl}/sitemap.xml
ROBOTS;

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
