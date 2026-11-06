<?php

namespace App\Models\Archive;

use Illuminate\Database\Eloquent\Model;

class ArchiveSetting extends Model
{
    protected $table = 'archive_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a setting by key with a default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting || $setting->value === null) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $setting->value;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        $encoded = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encoded,
                'description' => $description ?? static::where('key', $key)->value('description'),
            ]
        );
    }

    /**
     * Seed initial default settings if not exists.
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            'operation_mode' => [
                'value' => 'ARSIP_SAJA',
                'description' => 'Mode operasional pengarsipan (ARSIP_SAJA, ARSIP_DAN_TRANSFER, ARSIP_TRANSFER_DAN_PEMBERSIHAN)',
            ],
            'eligible_statuses' => [
                'value' => ['READY_FOR_PICKUP', 'COMPLETED'],
                'description' => 'Daftar status permohonan yang dapat dimasukkan ke paket arsip (Siap Diambil & Selesai)',
            ],
            'retention_policy_days' => [
                'value' => 365,
                'description' => 'Masa retensi arsip digital sebelum pertimbangan pemindahan (hari)',
            ],
            'cleaning_grace_period_days' => [
                'value' => 90,
                'description' => 'Masa tenggang aman sebelum pembersihan file sumber di hosting (hari)',
            ],
            'permanent_storage_driver' => [
                'value' => 'null',
                'description' => 'Adapter media penyimpanan permanen instansi (null, local_path, sftp)',
            ],
            'permanent_storage_config' => [
                'value' => [
                    'driver' => 'null',
                    'name' => 'Penyimpanan Permanen Instansi',
                    'host' => '',
                    'port' => 22,
                    'username' => '',
                    'target_path' => '',
                    'configured' => false,
                    'last_tested_at' => null,
                    'last_test_result' => null,
                ],
                'description' => 'Konfigurasi teknis koneksi media penyimpanan permanen',
            ],
        ];

        foreach ($defaults as $key => $item) {
            if (!static::where('key', $key)->exists()) {
                static::set($key, $item['value'], $item['description']);
            }
        }
    }
}
