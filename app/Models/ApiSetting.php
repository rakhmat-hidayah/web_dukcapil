<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ApiSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get all API settings cached.
     */
    public static function getAllCached(): array
    {
        return Cache::rememberForever('api_settings_all', function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear caching.
     */
    public static function clearCache(): void
    {
        Cache::forget('api_settings_all');
    }

    protected static function booted()
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
