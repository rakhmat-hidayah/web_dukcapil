<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Str;

/**
 * Enterprise Single Source of Truth for News SEO, OpenGraph & Social Preview Metadata.
 * Replaces standalone script architectures (e.g. share.php) with native Laravel service architecture.
 */
class NewsMetaService
{
    /**
     * Generate complete SEO, OpenGraph, Twitter Card & Schema.org JSON-LD Metadata.
     */
    public static function generate(News $news): array
    {
        $appUrl = rtrim(config('app.url'), '/');
        if (str_starts_with($appUrl, 'http://') && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            $appUrl = str_replace('http://', 'https://', $appUrl);
        }

        $siteName = 'Disdukcapil Kabupaten Dompu';
        $canonicalUrl = route('public.news.show', $news->slug);

        $title = $news->meta_title ?: $news->title;
        $description = $news->meta_description_final;
        $ogImageUrl = $news->og_image_final_url;
        $imageAlt = $news->thumbnail_alt ?: ($news->image_alt ?: $news->title);
        $authorName = $news->author ? $news->author->name : 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu';
        $categoryName = $news->category ? $news->category->name : 'Berita';
        
        $publishedAtIso = $news->published_at 
            ? $news->published_at->toIso8601String() 
            : ($news->created_at ? $news->created_at->toIso8601String() : null);
            
        $modifiedAtIso = $news->updated_at 
            ? $news->updated_at->toIso8601String() 
            : null;

        $tagsArray = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('news_tags') && \Illuminate\Support\Facades\Schema::hasTable('news_news_tags')) {
                $tagsArray = $news->tags ? $news->tags->pluck('name')->toArray() : [];
            }
        } catch (\Throwable $e) {
            $tagsArray = [];
        }

        // 1. Breadcrumb Items
        $breadcrumbs = [
            ['label' => 'Beranda',  'url' => url('/'),                                       'active' => false],
            ['label' => 'Berita',   'url' => route('public.news.index'),                      'active' => false],
        ];
        if ($news->category) {
            $breadcrumbs[] = [
                'label'  => $news->category->name,
                'url'    => route('public.news.index', ['category' => $news->category->slug]),
                'active' => false,
            ];
        }
        $breadcrumbs[] = ['label' => $news->title, 'url' => $canonicalUrl, 'active' => true];

        // 2. Schema.org BreadcrumbList
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(function ($crumb, $idx) {
                return [
                    '@type' => 'ListItem',
                    'position' => $idx + 1,
                    'name' => $crumb['label'],
                    'item' => $crumb['url'],
                ];
            }, $breadcrumbs, array_keys($breadcrumbs)),
        ];

        // 3. Schema.org NewsArticle
        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl,
            ],
            'headline' => Str::limit($title, 110),
            'description' => $description,
            'image' => [$ogImageUrl],
            'datePublished' => $publishedAtIso,
            'dateModified' => $modifiedAtIso,
            'author' => [
                '@type' => 'Person',
                'name' => $authorName,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu',
                'url' => $appUrl,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $appUrl . '/android-chrome-512x512.png',
                    'width' => 512,
                    'height' => 512,
                ],
            ],
            'articleSection' => $categoryName,
            'keywords' => implode(', ', array_merge(['Dukcapil Dompu', $categoryName], $tagsArray)),
            'inLanguage' => 'id-ID',
        ];

        return [
            'title' => $title . ' - ' . $siteName,
            'description' => $description,
            'canonical' => $canonicalUrl,
            'breadcrumbs' => $breadcrumbs,
            'og' => [
                'type' => 'article',
                'title' => $title,
                'description' => $description,
                'image' => $ogImageUrl,
                'image_width' => '1200',
                'image_height' => '630',
                'url' => $canonicalUrl,
                'site_name' => $siteName,
                'locale' => 'id_ID',
                'published_time' => $publishedAtIso,
                'modified_time' => $modifiedAtIso,
                'author' => $authorName,
                'section' => $categoryName,
                'tags' => $tagsArray,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $title,
                'description' => $description,
                'image' => $ogImageUrl,
                'image_alt' => $imageAlt,
                'url' => $canonicalUrl,
                'site' => '@dukcapildompu',
            ],
            'schema' => $articleSchema,
            'breadcrumb_schema' => $breadcrumbSchema,
        ];
    }

    /**
     * Audit SEO Score & Readiness.
     */
    public static function auditSeo(array $data): array
    {
        return NewsSeoService::auditSeo($data);
    }
}
