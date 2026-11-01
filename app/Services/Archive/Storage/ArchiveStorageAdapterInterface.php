<?php

namespace App\Services\Archive\Storage;

use App\Models\Archive\ArchivePackage;

interface ArchiveStorageAdapterInterface
{
    /**
     * Get unique driver identifier.
     */
    public function getDriverName(): string;

    /**
     * Check if adapter is configured with required credentials/paths.
     */
    public function isConfigured(): bool;

    /**
     * Test connection to destination storage.
     *
     * @return array{success: bool, message: string, details?: array}
     */
    public function testConnection(): array;

    /**
     * Transfer an archive package to permanent storage.
     *
     * @return array{success: bool, destination: string, bytes_transferred: int, message: string}
     */
    public function transfer(ArchivePackage $package): array;

    /**
     * Verify package existence and SHA-256 checksum at destination storage.
     *
     * @return array{verified: bool, remote_checksum: ?string, message: string}
     */
    public function verify(ArchivePackage $package): array;
}
