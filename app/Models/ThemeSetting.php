<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ThemeSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Get all theme settings cached as a key-value array.
     */
    public static function getAllCached(): array
    {
        return Cache::rememberForever('theme_settings_all', function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear the theme settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('theme_settings_all');
    }

    protected static function booted()
    {
        // Automatically clear cache on changes
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
