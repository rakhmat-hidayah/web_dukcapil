<?php

namespace App\Services;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;

class WebsiteSettingsService
{
    /** Cache key for global website settings */
    const CACHE_KEY = 'website_settings_all';

    /**
     * Get all website settings as a key-value associative array.
     */
    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return WebsiteSetting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get a specific setting by key with an optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = self::all();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting key-value pair and clear cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): WebsiteSetting
    {
        $setting = WebsiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => (string) ($value ?? ''), 'group' => $group]
        );

        self::clearCache();
        return $setting;
    }

    /**
     * Clear global website settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
