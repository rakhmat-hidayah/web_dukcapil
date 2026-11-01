<?php

namespace App\Services;

use App\Models\Download;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadPolicyService
{
    /**
     * Format bytes into human-readable string (KB, MB, GB).
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $floor = (int) floor($base);
        $idx = min($floor, count($units) - 1);

        $val = round($bytes / pow(1024, $idx), $precision);
        $valFormatted = ($val == (int) $val) ? (string) (int) $val : number_format($val, $precision, ',', '.');

        return $valFormatted . ' ' . $units[$idx];
    }

    /**
     * Resolve the physical absolute server path of a download record.
     */
    public static function resolvePhysicalPath(Download $doc): ?string
    {
        if (!$doc->file_path) {
            return null;
        }

        $relPath = ltrim(str_replace(['../', '..\\'], '', $doc->file_path), '/');

        // Candidate 1: Direct public_path
        $candidate1 = public_path($relPath);
        if (file_exists($candidate1) && is_file($candidate1)) {
            return $candidate1;
        }

        // Candidate 2: public_path('uploads/' . $relPath)
        $candidate2 = public_path('uploads/' . $relPath);
        if (file_exists($candidate2) && is_file($candidate2)) {
            return $candidate2;
        }

        // Candidate 3: storage_path('app/public/' . $relPath)
        $candidate3 = storage_path('app/public/' . $relPath);
        if (file_exists($candidate3) && is_file($candidate3)) {
            return $candidate3;
        }

        // Candidate 4: Storage disk public path
        try {
            $candidate4 = Storage::disk('public')->path($relPath);
            if (file_exists($candidate4) && is_file($candidate4)) {
                return $candidate4;
            }
        } catch (\Throwable $e) {}

        return null;
    }

    /**
     * Detect MIME Content-Type of a physical file safely.
     */
    public static function detectMimeType(string $fullPath, ?string $fallbackExtension = null): string
    {
        $mimes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            '7z'   => 'application/x-7z-compressed',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'txt'  => 'text/plain',
        ];

        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($fullPath);
            if ($detected && $detected !== 'application/octet-stream') {
                return $detected;
            }
        }

        $ext = strtolower($fallbackExtension ?: pathinfo($fullPath, PATHINFO_EXTENSION));
        return $mimes[$ext] ?? 'application/octet-stream';
    }

    /**
     * Comprehensive Pre-Flight & Policy Validation.
     * Evaluates Database record, User authorization, Physical File existence, Readability, and Size metadata.
     */
    public static function validateDownload(Download|int $download, ?User $user = null): array
    {
        $doc = $download instanceof Download ? $download : Download::find($download);

        // 1. Database Record Check
        if (!$doc) {
            return [
                'allowed'     => false,
                'reason'      => 'FILE_NOT_FOUND',
                'reason_text' => 'Dokumen tidak ditemukan di database sistem.',
                'size_bytes'  => 0,
                'size_human'  => '0 B',
                'file_name'   => null,
                'file_type'   => null,
                'full_path'   => null,
                'mime_type'   => 'application/octet-stream',
            ];
        }

        // 2. User Authorization & Availability Status Check
        $isAdmin = $user && (
            $user->hasAnyRole([
                'Super Administrator', 'Administrator Utama',
                'Website Administrator', 'Administrator Website',
                'Publication Operator', 'Operator Publikasi',
                'Content Editor', 'Editor Konten',
                'PPID Admin', 'Admin PPID',
            ])
        );

        if (!$isAdmin && $doc->status !== 'published') {
            return [
                'allowed'     => false,
                'reason'      => 'FILE_UNAVAILABLE',
                'reason_text' => 'Dokumen ini belum dipublikasikan untuk umum.',
                'size_bytes'  => (int) ($doc->file_size ?? 0),
                'size_human'  => self::formatBytes((int) ($doc->file_size ?? 0)),
                'file_name'   => $doc->file_name,
                'file_type'   => $doc->file_type,
                'full_path'   => null,
                'mime_type'   => 'application/octet-stream',
            ];
        }

        // 3. Physical Filesystem Check
        $fullPath = self::resolvePhysicalPath($doc);
        if (!$fullPath || !is_readable($fullPath)) {
            return [
                'allowed'     => false,
                'reason'      => 'FILE_NOT_FOUND',
                'reason_text' => 'Berkas fisik dokumen tidak ditemukan atau tidak dapat dibaca dari server.',
                'size_bytes'  => (int) ($doc->file_size ?? 0),
                'size_human'  => self::formatBytes((int) ($doc->file_size ?? 0)),
                'file_name'   => $doc->file_name,
                'file_type'   => $doc->file_type,
                'full_path'   => null,
                'mime_type'   => 'application/octet-stream',
            ];
        }

        // 4. Retrieve Filesystem Metadata without Loading File into Memory
        $actualSizeBytes = (int) filesize($fullPath);
        $actualSizeHuman = self::formatBytes($actualSizeBytes);
        $mimeType = self::detectMimeType($fullPath, $doc->file_type);

        // Keep database file_size metadata in sync
        if ($doc->file_size != $actualSizeBytes) {
            try {
                $doc->update(['file_size' => $actualSizeBytes]);
            } catch (\Throwable $e) {}
        }

        // 5. Success Decision
        return [
            'allowed'     => true,
            'reason'      => 'ALLOWED',
            'reason_text' => 'Berkas valid dan siap diunduh.',
            'size_bytes'  => $actualSizeBytes,
            'size_human'  => $actualSizeHuman,
            'file_name'   => $doc->file_name ?: basename($fullPath),
            'file_type'   => $doc->file_type ?: pathinfo($fullPath, PATHINFO_EXTENSION),
            'full_path'   => $fullPath,
            'mime_type'   => $mimeType,
            'download'    => $doc,
        ];
    }

    /**
     * Memory-Safe File Streaming Response.
     * Streams file in 1 MB chunks to prevent RAM exhaustion and buffers.
     */
    public static function createStreamResponse(array $validationResult)
    {
        if (!$validationResult['allowed'] || !file_exists($validationResult['full_path'])) {
            abort(404, $validationResult['reason_text'] ?? 'File tidak dapat diproses.');
        }

        $fullPath  = $validationResult['full_path'];
        $fileName  = $validationResult['file_name'];
        $mimeType  = $validationResult['mime_type'];
        $sizeBytes = $validationResult['size_bytes'];
        $lastMod   = filemtime($fullPath);

        $headers = [
            'Content-Type'        => $mimeType,
            'Content-Length'      => (string) $sizeBytes,
            'Content-Disposition' => 'attachment; filename="' . addslashes($fileName) . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
            'Accept-Ranges'       => 'bytes',
            'Last-Modified'       => gmdate('D, d M Y H:i:s T', $lastMod),
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma'              => 'public',
        ];

        // Increment download counter on successful stream initiation
        if (isset($validationResult['download']) && $validationResult['download'] instanceof Download) {
            try {
                $validationResult['download']->increment('download_count');
            } catch (\Throwable $e) {}
        }

        return response()->streamDownload(function () use ($fullPath) {
            // Flush any existing PHP output buffers to prevent memory buffering
            while (ob_get_level()) {
                @ob_end_clean();
            }

            $stream = fopen($fullPath, 'rb');
            if ($stream === false) {
                return;
            }

            $chunkSize = 1024 * 1024; // 1 MB chunk
            while (!feof($stream) && connection_status() == 0) {
                echo fread($stream, $chunkSize);
                @flush();
            }

            fclose($stream);
        }, $fileName, $headers);
    }

    /**
     * Log download attempt for diagnostics and audit logging without exposing absolute filesystem paths.
     */
    public static function logDownloadAttempt(array $validationResult, ?Request $request = null, ?User $user = null): void
    {
        $userId = $user ? $user->id : (auth()->check() ? auth()->id() : null);
        $userIp = $request ? $request->ip() : request()->ip();
        $userAgent = $request ? $request->userAgent() : request()->userAgent();

        if ($validationResult['allowed']) {
            Log::info('Download Guard: File Stream Initiated', [
                'user_id'   => $userId,
                'file_name' => $validationResult['file_name'] ?? 'Unknown',
                'file_size' => $validationResult['size_human'] ?? '0 B',
                'ip'        => $userIp,
            ]);
        } else {
            Log::warning('Download Guard: Attempt Denied', [
                'user_id'    => $userId,
                'reason'     => $validationResult['reason'],
                'file_name'  => $validationResult['file_name'] ?? 'Unknown',
                'file_size'  => $validationResult['size_human'] ?? '0 B',
                'ip'         => $userIp,
                'user_agent' => $userAgent,
            ]);

            if ($user) {
                ActivityLogger::log(
                    "Gagal mengunduh berkas [{$validationResult['file_name']}]: ({$validationResult['reason']})",
                    null,
                    'download_failed',
                    [
                        'reason' => $validationResult['reason'],
                        'size'   => $validationResult['size_human'] ?? '0 B',
                    ]
                );
            }
        }
    }
}
