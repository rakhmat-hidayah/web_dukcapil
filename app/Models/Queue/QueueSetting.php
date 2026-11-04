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
        try {
            return Cache::remember("queue_setting_{$key}", 3600, function () use ($key, $default) {
                $val = static::where('key', $key)->value('value');
                return $val !== null ? $val : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value, ?string $group = null, ?string $description = null): void
    {
        $existing = static::where('key', $key)->first();

        $updateData = ['value' => $value];
        if ($group !== null) {
            $updateData['group'] = $group;
        } elseif (!$existing) {
            $updateData['group'] = 'general';
        }

        if ($description !== null) {
            $updateData['description'] = $description;
        }

        static::updateOrCreate(
            ['key' => $key],
            $updateData
        );
        Cache::forget("queue_setting_{$key}");
        if ($existing?->group) {
            Cache::forget("queue_settings_group_{$existing->group}");
        }
        if ($group && $group !== $existing?->group) {
            Cache::forget("queue_settings_group_{$group}");
        }
    }

    public static function getGroup(string $group): array
    {
        return Cache::remember("queue_settings_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }
}
