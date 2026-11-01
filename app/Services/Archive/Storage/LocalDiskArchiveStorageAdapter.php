<?php

namespace App\Services\Archive\Storage;

use App\Models\Archive\ArchivePackage;
use App\Models\Archive\ArchiveSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LocalDiskArchiveStorageAdapter implements ArchiveStorageAdapterInterface
{
    protected ?string $basePath;

    public function __construct()
    {
        $config = ArchiveSetting::get('permanent_storage_config', []);
        $this->basePath = $config['target_path'] ?? env('PERMANENT_ARCHIVE_LOCAL_PATH');
    }

    public function getDriverName(): string
    {
        return 'local_path';
    }

    public function isConfigured(): bool
    {
        return !empty($this->basePath);
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Path direktori tujuan penyimpanan permanen belum diisi.',
                'details' => ['path' => null],
            ];
        }

        try {
            if (!File::isDirectory($this->basePath)) {
                return [
                    'success' => false,
                    'message' => "Direktori tujuan [{$this->basePath}] tidak ditemukan pada server.",
                    'details' => ['path' => $this->basePath, 'exists' => false],
                ];
            }

            if (!File::isWritable($this->basePath)) {
                return [
                    'success' => false,
                    'message' => "Direktori tujuan [{$this->basePath}] tidak memiliki izin tulis (write permission).",
                    'details' => ['path' => $this->basePath, 'writable' => false],
                ];
            }

            // Perform write test with temporary marker
            $testFile = rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . '.dukcapil_storage_probe_' . time();
            File::put($testFile, 'probe');
            File::delete($testFile);

            return [
                'success' => true,
                'message' => "Koneksi ke direktori penyimpanan permanen [{$this->basePath}] aktif dan dapat ditulis.",
                'details' => [
                    'path' => $this->basePath,
                    'free_space_mb' => round(@disk_free_space($this->basePath) / 1024 / 1024, 2),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Kesalahan saat menguji direktori penyimpanan: ' . $e->getMessage(),
                'details' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function transfer(ArchivePackage $package): array
    {
        $test = $this->testConnection();
        if (!$test['success']) {
            return [
                'success' => false,
                'destination' => '',
                'bytes_transferred' => 0,
                'message' => 'Gagal memulai transfer: ' . $test['message'],
            ];
        }

        $sourceZip = storage_path('app/private/' . $package->zip_path);
        if (!File::exists($sourceZip)) {
            return [
                'success' => false,
                'destination' => '',
                'bytes_transferred' => 0,
                'message' => 'Berkas paket ZIP sumber tidak ditemukan di hosting.',
            ];
        }

        $fileName = basename($sourceZip);
        $destinationPath = rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . $fileName;

        try {
            // Copy file to permanent storage
            File::copy($sourceZip, $destinationPath);

            $bytesTransferred = File::size($destinationPath);

            return [
                'success' => true,
                'destination' => $destinationPath,
                'bytes_transferred' => $bytesTransferred,
                'message' => "Paket arsip berhasil disalin ke media penyimpanan permanen [{$destinationPath}].",
            ];
        } catch (\Throwable $e) {
            Log::error("Archive transfer failed: " . $e->getMessage());
            return [
                'success' => false,
                'destination' => $destinationPath,
                'bytes_transferred' => 0,
                'message' => 'Gagal menyalin paket ke penyimpanan permanen: ' . $e->getMessage(),
            ];
        }
    }

    public function verify(ArchivePackage $package): array
    {
        if (empty($package->permanent_storage_destination)) {
            return [
                'verified' => false,
                'remote_checksum' => null,
                'message' => 'Tujuan penyimpanan permanen belum tercatat pada paket ini.',
            ];
        }

        $destinationPath = $package->permanent_storage_destination;

        if (!File::exists($destinationPath)) {
            return [
                'verified' => false,
                'remote_checksum' => null,
                'message' => "Berkas paket tidak ditemukan di lokasi penyimpanan permanen [{$destinationPath}].",
            ];
        }

        $remoteSize = File::size($destinationPath);
        if ($remoteSize !== $package->package_size_bytes) {
            return [
                'verified' => false,
                'remote_checksum' => null,
                'message' => "Ukuran berkas pada media permanen ({$remoteSize} bytes) tidak cocok dengan ukuran paket asli ({$package->package_size_bytes} bytes).",
            ];
        }

        $remoteHash = hash_file('sha256', $destinationPath);
        if ($remoteHash !== $package->sha256_checksum) {
            return [
                'verified' => false,
                'remote_checksum' => $remoteHash,
                'message' => "Checksum SHA-256 berkas di media permanen [{$remoteHash}] tidak sesuai dengan hash resmi paket [{$package->sha256_checksum}].",
            ];
        }

        return [
            'verified' => true,
            'remote_checksum' => $remoteHash,
            'message' => 'Integritas paket di penyimpanan permanen terverifikasi valid (SHA-256 dan ukuran 100% cocok).',
        ];
    }
}
