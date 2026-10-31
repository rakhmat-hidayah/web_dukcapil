<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class QueueSetting extends Model
{
    protected $table = 'queue_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("queue_setting_{$key}", 3600, function () use ($key, $default) {
            $val = static::where('key', $key)->value('value');
            return $val !== null ? $val : $default;
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general', ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description,
            ]
        );
        Cache::forget("queue_setting_{$key}");
        Cache::forget("queue_settings_group_{$group}");
    }

    public static function getGroup(string $group): array
    {
        return Cache::remember("queue_settings_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }
}
