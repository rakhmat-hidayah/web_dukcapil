<?php

namespace App\Services;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
     * Set a setting with versioning and audit trail logging.
     * Respects APP_PRODUCTION_LOCK mode.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?int $userId = null, ?string $reason = null): WebsiteSetting
    {
        if (self::isProductionLocked()) {
            throw new \RuntimeException("Production Lock Mode (APP_PRODUCTION_LOCK) is active. Configuration changes are blocked.");
        }

        $val = is_null($value) ? '' : (string) $value;
        $existing = WebsiteSetting::where('key', $key)->first();

        $setting = WebsiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $val, 'group' => $group]
        );

        // Versioning: Log historic snapshot in website_setting_versions
        $latestVersion = DB::table('website_setting_versions')
            ->where('key', $key)
            ->max('version') ?? 0;

        DB::table('website_setting_versions')->insert([
            'key'           => $key,
            'value'         => $val,
            'group'         => $group,
            'version'       => $latestVersion + 1,
            'created_by'    => $userId ?? (auth()->check() ? auth()->id() : null),
            'ip_address'    => request()->ip() ?? '127.0.0.1',
            'change_reason' => $reason ?? 'CMS Update',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

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
     * Audit & Check configuration integrity against system schema rules.
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
        ];

        $missingKeys = [];
        $emptyKeys = [];
        $warnings = [];

        foreach ($requiredKeys as $key => $label) {
            if (!array_key_exists($key, $settings)) {
                $missingKeys[] = "[$key] $label (Kunci tidak ditemukan di DB)";
            } elseif (trim($settings[$key]) === '') {
                $emptyKeys[] = "[$key] $label (Nilai kosong)";
            }
        }

        // Validate email format if set
        if (!empty($settings['office_email']) && !filter_var($settings['office_email'], FILTER_VALIDATE_EMAIL)) {
            $warnings[] = "[office_email] Format email tidak valid: " . $settings['office_email'];
        }

        // Validate SANAI URL format if set
        if (!empty($settings['sanai_url']) && !filter_var($settings['sanai_url'], FILTER_VALIDATE_URL)) {
            $warnings[] = "[sanai_url] Format URL tidak valid: " . $settings['sanai_url'];
        }

        $isHealthy = empty($missingKeys) && empty($emptyKeys) && empty($warnings);

        return [
            'is_healthy'       => $isHealthy,
            'production_lock'  => self::isProductionLocked(),
            'total_keys_count' => count($settings),
            'missing_keys'     => $missingKeys,
            'empty_keys'       => $emptyKeys,
            'warnings'         => $warnings,
        ];
    }
}
