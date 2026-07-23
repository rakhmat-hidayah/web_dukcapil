<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Get all website settings cached as a key-value array.
     */
    public static function getAllCached(): array
    {
        return Cache::rememberForever('website_settings_all', function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear the website settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('website_settings_all');
    }

    protected static function booted()
    {
        // Automatically clear cache on changes
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
