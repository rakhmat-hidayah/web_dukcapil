<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class News extends Model
{
    use SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'user_id', 'last_updated_user_id', 'news_category_id', 'title', 'slug', 'excerpt', 'content',
        'thumbnail', 'thumbnail_caption', 'thumbnail_alt', 'status', 'is_featured', 'is_breaking',
        'meta_title', 'meta_description', 'og_image', 'social_image', 'image_alt',
        'page_views', 'unique_readers', 'robot_hits',
        'beneficial_count', 'informative_count',
        'share_count', 'whatsapp_shares', 'facebook_shares', 'telegram_shares',
        'twitter_shares', 'linkedin_shares', 'copy_link_count', 'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_breaking' => 'boolean',
        'published_at' => 'datetime',
        'page_views' => 'integer',
        'unique_readers' => 'integer',
        'robot_hits' => 'integer',
        'beneficial_count' => 'integer',
        'informative_count' => 'integer',
        'share_count' => 'integer',
        'whatsapp_shares' => 'integer',
        'facebook_shares' => 'integer',
        'telegram_shares' => 'integer',
        'twitter_shares' => 'integer',
        'linkedin_shares' => 'integer',
        'copy_link_count' => 'integer',
    ];

    protected $appends = [];

    /**
     * Get computed final Open Graph Image URL following requirement priority:
     * 1. Social Preview Image (social_image / og_image)
     * 2. Featured Image / Thumbnail
     * 3. First Thumbnail in content HTML
     * 4. Generated Dynamic OG Banner route
     */
    public function getOgImageFinalUrlAttribute(): string
    {
        $appUrl = request() ? request()->schemeAndHttpHost() : rtrim(config('app.url'), '/');
        if (str_starts_with($appUrl, 'http://') && !str_contains($appUrl, 'localhost') && !str_contains($appUrl, '127.0.0.1')) {
            $appUrl = str_replace('http://', 'https://', $appUrl);
        }

        // 1. Check social_image or og_image
        $candidate = $this->social_image ?: $this->og_image;
        if ($candidate && !str_starts_with($candidate, 'data:')) {
            $clean = preg_replace('#^https?://[^/]+#i', '', $candidate);
            $clean = '/' . ltrim(str_replace(['/storage/', 'storage/'], '', $clean), '/');
            return $appUrl . $clean;
        }

        // 2. Check main thumbnail
        if ($this->thumbnail && !str_starts_with($this->thumbnail, 'data:')) {
            $clean = preg_replace('#^https?://[^/]+#i', '', $this->thumbnail);
            $clean = '/' . ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $clean), '/');
            return $appUrl . '/uploads' . $clean;
        }

        // 3. Check first img tag in content HTML
        if ($this->content && preg_match('#<img[^>]+src=["\']([^"\']+)["\']#i', $this->content, $matches)) {
            $src = $matches[1];
            if (!str_starts_with($src, 'data:')) {
                if (filter_var($src, FILTER_VALIDATE_URL)) {
                    return str_replace('http://', 'https://', $src);
                }
                if (str_starts_with($src, '/')) {
                    return $appUrl . $src;
                }
            }
        }

        // 4. Fallback to Dynamic Generated OG Banner URL
        if ($this->slug) {
            $bannerUrl = route('public.news.og-banner', $this->slug);
            return str_replace('http://', 'https://', $bannerUrl);
        }

        // 5. Default site logo
        return $appUrl . '/android-chrome-512x512.png';
    }

    /**
     * Get computed meta description following requirement priority:
     * 1. Meta Description (meta_description)
     * 2. Excerpt
     * 3. First 155 chars of stripped content HTML
     */
    public function getMetaDescriptionFinalAttribute(): string
    {
        if (!empty($this->meta_description)) {
            return \Illuminate\Support\Str::limit(trim($this->meta_description), 160);
        }

        if (!empty($this->excerpt)) {
            return \Illuminate\Support\Str::limit(trim($this->excerpt), 160);
        }

        if (!empty($this->content)) {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($this->content)));
            return \Illuminate\Support\Str::limit($plain, 155);
        }

        return 'Berita resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu';
    }

    /**
     * URL thumbnail publik yang bersih dan optimal (tanpa base64, ramah performa & cache browser).
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) return null;
        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }
        if (str_starts_with($this->thumbnail, 'data:')) {
            return $this->thumbnail;
        }

        $clean = preg_replace('#^https?://[^/]+#i', '', $this->thumbnail);
        $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $clean), '/');
        
        return '/uploads/' . $clean;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lastUpdatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_news_tags', 'news_id', 'news_tag_id');
    }

    public function reactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArticleReaction::class, 'news_id');
    }

    public function shares()
    {
        return $this->hasMany(NewsShare::class, 'news_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now()->addHours(24));
            });
    }

    /**
     * Relasi ke log pembaca individual.
     */
    public function readerLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArticleReaderLog::class, 'news_id');
    }

    /**
     * Increment page views & unique readers safely using NewsReaderTrackerService.
     */
    public function incrementViews(?\Illuminate\Http\Request $request = null): array
    {
        return \App\Services\NewsReaderTrackerService::recordRead($this, $request);
    }

    /**
     * Increment share counter & record share log
     */
    public function recordShare(string $platform, ?string $ip = null, ?string $userAgent = null): void
    {
        $platform = strtolower($platform);
        
        // Log individual share if table exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('news_shares')) {
                $this->shares()->create([
                    'platform'   => $platform,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('recordShare log error: ' . $e->getMessage());
        }

        // Direct atomic SQL UPDATE with COALESCE to prevent NULL + 1 = NULL in MySQL
        try {
            $columnMap = [
                'whatsapp'  => 'whatsapp_shares',
                'facebook'  => 'facebook_shares',
                'telegram'  => 'telegram_shares',
                'twitter'   => 'twitter_shares',
                'x'         => 'twitter_shares',
                'linkedin'  => 'linkedin_shares',
                'copy'      => 'copy_link_count',
                'copy_link' => 'copy_link_count',
            ];

            $specificColumn = $columnMap[$platform] ?? null;

            if ($specificColumn && \Illuminate\Support\Facades\Schema::hasColumn('news', $specificColumn)) {
                \Illuminate\Support\Facades\DB::table('news')
                    ->where('id', $this->id)
                    ->update([
                        'share_count' => \Illuminate\Support\Facades\DB::raw('COALESCE(share_count, 0) + 1'),
                        $specificColumn => \Illuminate\Support\Facades\DB::raw("COALESCE({$specificColumn}, 0) + 1"),
                    ]);
            } else if (\Illuminate\Support\Facades\Schema::hasColumn('news', 'share_count')) {
                \Illuminate\Support\Facades\DB::table('news')
                    ->where('id', $this->id)
                    ->update([
                        'share_count' => \Illuminate\Support\Facades\DB::raw('COALESCE(share_count, 0) + 1'),
                    ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('recordShare increment error: ' . $e->getMessage());
        }
    }
}