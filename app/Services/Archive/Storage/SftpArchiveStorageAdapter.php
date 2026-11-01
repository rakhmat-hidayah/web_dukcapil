<?php

namespace App\Services\Archive\Storage;

use App\Models\Archive\ArchivePackage;
use App\Models\Archive\ArchiveSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SftpArchiveStorageAdapter implements ArchiveStorageAdapterInterface
{
    protected ?string $host;
    protected int $port;
    protected ?string $username;
    protected ?string $password;
    protected ?string $targetPath;

    public function __construct()
    {
        $config = ArchiveSetting::get('permanent_storage_config', []);
        $this->host = $config['host'] ?? env('PERMANENT_ARCHIVE_SFTP_HOST');
        $this->port = (int) ($config['port'] ?? env('PERMANENT_ARCHIVE_SFTP_PORT', 22));
        $this->username = $config['username'] ?? env('PERMANENT_ARCHIVE_SFTP_USERNAME');
        $this->password = $config['password'] ?? env('PERMANENT_ARCHIVE_SFTP_PASSWORD');
        $this->targetPath = $config['target_path'] ?? env('PERMANENT_ARCHIVE_SFTP_PATH');
    }

    public function getDriverName(): string
    {
        return 'sftp';
    }

    public function isConfigured(): bool
    {
        return !empty($this->host) && !empty($this->username) && !empty($this->targetPath);
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Parameter host, username, atau target path SFTP server instansi belum lengkap.',
                'details' => ['configured' => false],
            ];
        }

        // Check if PHP SSH2 extension is loaded
        if (!function_exists('ssh2_connect')) {
            return [
                'success' => false,
                'message' => 'Ekstensi PHP SSH2 (ssh2_connect) belum diaktifkan pada server hosting ini. Hubungi administrator server untuk mengaktifkan php-ssh2.',
                'details' => ['extension_loaded' => false],
            ];
        }

        try {
            $connection = @ssh2_connect($this->host, $this->port);
            if (!$connection) {
                return [
                    'success' => false,
                    'message' => "Gagal menghubungi SFTP server [{$this->host}:{$this->port}]. Periksa network/firewall.",
                ];
            }

            if (!empty($this->password) && !@ssh2_auth_password($connection, $this->username, $this->password)) {
                return [
                    'success' => false,
                    'message' => "Autentikasi SFTP gagal untuk pengguna [{$this->username}].",
                ];
            }

            $sftp = @ssh2_sftp($connection);
            if (!$sftp) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuka subsistem SFTP pada server tujuan.',
                ];
            }

            return [
                'success' => true,
                'message' => "Koneksi SFTP ke server instansi [{$this->host}] berhasil dan subsistem SFTP aktif.",
                'details' => [
                    'host' => $this->host,
                    'port' => $this->port,
                    'username' => $this->username,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Kesalahan koneksi SFTP: ' . $e->getMessage(),
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
                'message' => $test['message'],
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
        $remoteDestination = rtrim($this->targetPath, '/') . '/' . $fileName;

        try {
            $connection = ssh2_connect($this->host, $this->port);
            ssh2_auth_password($connection, $this->username, $this->password);
            $sftp = ssh2_sftp($connection);

            $sftpStream = @fopen('ssh2.sftp://' . intval($sftp) . $remoteDestination, 'w');
            if (!$sftpStream) {
                return [
                    'success' => false,
                    'destination' => $remoteDestination,
                    'bytes_transferred' => 0,
                    'message' => "Gagal membuka remote stream SFTP pada [{$remoteDestination}].",
                ];
            }

            $localStream = fopen($sourceZip, 'r');
            $bytes = stream_copy_to_stream($localStream, $sftpStream);
            fclose($localStream);
            fclose($sftpStream);

            return [
                'success' => true,
                'destination' => "sftp://{$this->host}:{$this->port}{$remoteDestination}",
                'bytes_transferred' => $bytes,
                'message' => "Paket arsip berhasil ditransfer via SFTP ke server instansi [{$remoteDestination}].",
            ];
        } catch (\Throwable $e) {
            Log::error("SFTP archive transfer error: " . $e->getMessage());
            return [
                'success' => false,
                'destination' => $remoteDestination,
                'bytes_transferred' => 0,
                'message' => 'Gagal mentransfer paket via SFTP: ' . $e->getMessage(),
            ];
        }
    }

    public function verify(ArchivePackage $package): array
    {
        return [
            'verified' => false,
            'remote_checksum' => null,
            'message' => 'Verifikasi remote SFTP memerlukan integrasi agent SHA-256 pada server instansi.',
        ];
    }
}
