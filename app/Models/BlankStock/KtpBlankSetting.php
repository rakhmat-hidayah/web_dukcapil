<?php

namespace App\Models\BlankStock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KtpBlankSetting extends Model
{
    protected $table = 'ktp_blank_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("ktp_blank_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, mixed $value, ?string $description = null): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value'       => (string) $value,
                'description' => $description,
            ]
        );

        Cache::forget("ktp_blank_setting_{$key}");
        Cache::forget('public_blank_stock_summary');

        return $setting;
    }

    public static function getAll(): array
    {
        return Cache::remember('ktp_blank_settings_all', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('ktp_blank_settings_all');
        Cache::forget('public_blank_stock_summary');
    }
}
