<?php

namespace App\Services\Archive\Storage;

use App\Models\Archive\ArchivePackage;
use App\Models\Archive\ArchiveSetting;

class ArchiveStorageManager
{
    /**
     * Resolve the active storage adapter according to configuration.
     */
    public static function resolve(): ArchiveStorageAdapterInterface
    {
        $driver = ArchiveSetting::get('permanent_storage_driver', 'null');

        return match ($driver) {
            'local_path' => new LocalDiskArchiveStorageAdapter(),
            'sftp' => new SftpArchiveStorageAdapter(),
            default => new NullArchiveStorageAdapter(),
        };
    }

    /**
     * Test connection for the current active driver.
     */
    public static function testConnection(): array
    {
        return static::resolve()->testConnection();
    }

    /**
     * Transfer an archive package to permanent storage.
     */
    public static function transfer(ArchivePackage $package): array
    {
        return static::resolve()->transfer($package);
    }

    /**
     * Verify an archive package in permanent storage.
     */
    public static function verify(ArchivePackage $package): array
    {
        return static::resolve()->verify($package);
    }
}
