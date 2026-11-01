<?php

namespace App\Services\Archive\Storage;

use App\Models\Archive\ArchivePackage;

class NullArchiveStorageAdapter implements ArchiveStorageAdapterInterface
{
    public function getDriverName(): string
    {
        return 'null';
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function testConnection(): array
    {
        return [
            'success' => false,
            'message' => 'Media penyimpanan permanen instansi belum dikonfigurasi. Pengarsipan berjalan dalam mode ARSIP_SAJA (Manual Download & Local Archiving).',
            'details' => [
                'driver' => 'null',
                'status' => 'NOT_CONFIGURED',
            ],
        ];
    }

    public function transfer(ArchivePackage $package): array
    {
        return [
            'success' => false,
            'destination' => '',
            'bytes_transferred' => 0,
            'message' => 'Transfer ditolak: Media penyimpanan permanen instansi belum dikonfigurasi.',
        ];
    }

    public function verify(ArchivePackage $package): array
    {
        return [
            'verified' => false,
            'remote_checksum' => null,
            'message' => 'Verifikasi gagal: Media penyimpanan permanen instansi belum dikonfigurasi.',
        ];
    }
}
