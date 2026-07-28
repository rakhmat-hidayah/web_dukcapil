<?php

namespace App\Services;

use App\Models\WebsiteSetting;
use App\Models\Banner;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class ConfigurationService
{
    const CACHE_KEY = 'website_settings_all';

    /**
     * Check if Production Lock Mode is active.
     */
    public static function isProductionLocked(): bool
    {
        return filter_var(env('APP_PRODUCTION_LOCK', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get all cached website settings as key-value pairs.
     */
    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return WebsiteSetting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get a specific setting by key. Returns default if empty/missing.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = self::all();
        return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $default;
    }

    /**
     * Set a setting with ActivityLog audit trail (old and new values in JSON properties).
     * Respects APP_PRODUCTION_LOCK mode.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?int $userId = null, ?string $reason = null): WebsiteSetting
    {
        if (self::isProductionLocked()) {
            throw new \RuntimeException("Production Lock Mode (APP_PRODUCTION_LOCK) is active. Configuration changes are blocked.");
        }

        $newVal = is_null($value) ? '' : (string) $value;
        $existing = WebsiteSetting::where('key', $key)->first();
        $oldVal = $existing ? $existing->value : null;

        $setting = WebsiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $newVal, 'group' => $group]
        );

        // Audit Trail via Spatie ActivityLog (Single Source of Truth Audit Trail)
        activity('configuration')
            ->performedOn($setting)
            ->causedBy($userId ?? (auth()->check() ? auth()->id() : null))
            ->withTrashed()
            ->withProperties([
                'old'        => ['value' => $oldVal],
                'attributes' => ['value' => $newVal],
                'key'        => $key,
                'group'      => $group,
                'reason'     => $reason ?? 'CMS Configuration Update',
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ])
            ->log("Updated website setting [$key]");

        self::clearCache();

        return $setting;
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Audit & Check configuration integrity against system schema, media assets, URLs, and SEO.
     */
    public static function checkIntegrity(): array
    {
        $settings = self::all();

        $requiredKeys = [
            'office_address'     => 'Alamat Kantor Dinas',
            'office_email'       => 'Email Resmi Kantor',
            'office_phone'       => 'Nomor Telepon Kantor',
            'office_work_hours'  => 'Jam Operasional Layanan',
            'sanai_name'         => 'Nama Aplikasi SANAI Online',
            'sanai_url'          => 'URL Link SANAI Online',
            'seo_title'          => 'Judul SEO Website',
            'seo_description'    => 'Deskripsi Meta SEO',
            'seo_keywords'       => 'Kata Kunci (Keywords) SEO',
        ];

        $missingKeys = [];
        $emptyKeys = [];
        $mediaIssues = [];
        $urlWarnings = [];

        // 1. Check Required Settings Keys
        foreach ($requiredKeys as $key => $label) {
            if (!array_key_exists($key, $settings)) {
                $missingKeys[] = "[$key] $label (Kunci tidak ada di database)";
            } elseif (trim($settings[$key]) === '') {
                $emptyKeys[] = "[$key] $label (Nilai kosong)";
            }
        }

        // 2. Validate Email Format
        if (!empty($settings['office_email']) && !filter_var($settings['office_email'], FILTER_VALIDATE_EMAIL)) {
            $urlWarnings[] = "[office_email] Format email tidak valid: " . $settings['office_email'];
        }

        // 3. Validate External URLs & Social Links
        $urlFields = ['sanai_url', 'social_instagram', 'social_facebook', 'seo_canonical'];
        foreach ($urlFields as $field) {
            if (!empty($settings[$field]) && !filter_var($settings[$field], FILTER_VALIDATE_URL)) {
                $urlWarnings[] = "[$field] Format URL tidak valid: " . $settings[$field];
            }
        }

        // 4. Validate Branding Media Assets (Logo, Dark Logo, Favicon)
        $brandLogo = ThemeSetting::where('key', 'site_logo')->value('value');
        $brandLogoDark = ThemeSetting::where('key', 'site_logo_dark')->value('value');
        $brandFavicon = ThemeSetting::where('key', 'site_favicon')->value('value');

        if ($brandLogo && !file_exists(public_path($brandLogo)) && !file_exists(public_path('uploads/' . ltrim($brandLogo, '/')))) {
            $mediaIssues[] = "[site_logo] File logo situs tidak ditemukan pada disk: " . $brandLogo;
        }

        if ($brandLogoDark && !file_exists(public_path($brandLogoDark)) && !file_exists(public_path('uploads/' . ltrim($brandLogoDark, '/')))) {
            $mediaIssues[] = "[site_logo_dark] File logo dark situs tidak ditemukan pada disk: " . $brandLogoDark;
        }

        if ($brandFavicon && !file_exists(public_path($brandFavicon)) && !file_exists(public_path('uploads/' . ltrim($brandFavicon, '/')))) {
            $mediaIssues[] = "[site_favicon] File favicon situs tidak ditemukan pada disk: " . $brandFavicon;
        }

        // 5. Validate Banner Media References
        $activeBanners = Banner::where('is_active', true)->get();
        foreach ($activeBanners as $banner) {
            $bPath = $banner->image ?? $banner->image_path ?? null;
            if ($bPath && !file_exists(public_path($bPath)) && !file_exists(public_path('uploads/' . ltrim($bPath, '/')))) {
                $mediaIssues[] = "[Banner ID: {$banner->id}] File gambar banner tidak ditemukan: " . $bPath;
            }
        }

        $isHealthy = empty($missingKeys) && empty($emptyKeys) && empty($mediaIssues) && empty($urlWarnings);

        return [
            'is_healthy'       => $isHealthy,
            'production_lock'  => self::isProductionLocked(),
            'total_keys_count' => count($settings),
            'missing_keys'     => $missingKeys,
            'empty_keys'       => $emptyKeys,
            'media_issues'     => $mediaIssues,
            'url_warnings'     => $urlWarnings,
        ];
    }
}
