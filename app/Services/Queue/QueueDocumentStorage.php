<?php

namespace App\Services\Queue;

use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QueueDocumentStorage
{
    /**
     * Private disk identifier from config/filesystems.php ('local' maps to storage/app/private).
     */
    const DISK = 'local';
    const BASE_PATH = 'queue-documents';

    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];
    const BLOCKED_EXTENSIONS = ['php', 'exe', 'bat', 'cmd', 'js', 'sh', 'dll', 'cgi', 'pl', 'asp', 'aspx', 'jsp', 'phar'];
    const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

    // Maximum image dimensions to ensure readability while optimizing disk usage
    const IMAGE_MAX_WIDTH = 2048;
    const IMAGE_MAX_HEIGHT = 2048;
    const IMAGE_QUALITY = 82;

    /**
     * Store an uploaded application document securely in private storage.
     * Performs server-side EXIF auto-orientation, proportional resize, and adaptive compression.
     */
    public static function store(UploadedFile $file, QueueApplication $application): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, self::BLOCKED_EXTENSIONS)) {
            throw new \InvalidArgumentException("Format file (.{$extension}) dilarang demi keamanan sistem.");
        }

        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new \InvalidArgumentException("Format file (.{$extension}) tidak didukung. Harap unggah format JPG, PNG, atau PDF.");
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIMES)) {
            throw new \InvalidArgumentException("Tipe konten file ({$mimeType}) tidak valid.");
        }

        $originalSize = $file->getSize();
        $originalFilename = $file->getClientOriginalName();
        $year = date('Y');
        $month = date('m');
        $uuid = Str::uuid()->toString();
        $storedFilename = "{$uuid}.{$extension}";
        $path = self::BASE_PATH . "/{$year}/{$month}/{$application->id}/{$storedFilename}";

        $width = null;
        $height = null;
        $isCompressed = false;
        $finalSize = $originalSize;

        // Process images with Intervention Image
        if (str_starts_with($mimeType, 'image/')) {
            try {
                $img = Image::read($file->getRealPath());

                // EXIF auto-orientation is handled automatically in Intervention v3/v4 read()
                $width = $img->width();
                $height = $img->height();

                // Proportional scale down if exceeding maximum bounds
                if ($width > self::IMAGE_MAX_WIDTH || $height > self::IMAGE_MAX_HEIGHT) {
                    $img->scaleDown(width: self::IMAGE_MAX_WIDTH, height: self::IMAGE_MAX_HEIGHT);
                    $width = $img->width();
                    $height = $img->height();
                }

                // Encode to high-quality JPEG for size efficiency
                $encoded = $img->toJpeg(quality: self::IMAGE_QUALITY);
                $storedFilename = "{$uuid}.jpg";
                $path = self::BASE_PATH . "/{$year}/{$month}/{$application->id}/{$storedFilename}";
                $mimeType = 'image/jpeg';

                $binary = (string) $encoded;
                $finalSize = strlen($binary);
                $isCompressed = true;

                Storage::disk(self::DISK)->put($path, $binary);
            } catch (\Throwable $e) {
                Log::warning("Queue document compression fallback: " . $e->getMessage());
                // Fallback: store raw uploaded file safely
                Storage::disk(self::DISK)->putFileAs(
                    dirname($path),
                    $file,
                    basename($path)
                );
                $finalSize = $originalSize;
                $isCompressed = false;
            }
        } else {
            // PDF: store directly in private directory
            Storage::disk(self::DISK)->putFileAs(
                dirname($path),
                $file,
                basename($path)
            );
        }

        return [
            'path' => $path,
            'stored_filename' => $storedFilename,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'file_size_bytes' => $finalSize,
            'original_size_bytes' => $originalSize,
            'is_compressed' => $isCompressed,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Delete document file from private storage.
     */
    public static function delete(ApplicationDocument $doc): bool
    {
        $path = $doc->getRawOriginal('path');
        if (empty($path)) return false;

        try {
            return Storage::disk(self::DISK)->delete($path);
        } catch (\Throwable $e) {
            Log::warning("Queue document deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Stream private document file to authorized response.
     */
    public static function serve(ApplicationDocument $doc, bool $inline = true): StreamedResponse
    {
        $path = $doc->getRawOriginal('path');

        if (!Storage::disk(self::DISK)->exists($path)) {
            abort(404, 'Berkas dokumen tidak ditemukan di server penyimpanan.');
        }

        $mimeType = $doc->mime_type ?: 'application/octet-stream';
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $doc->original_filename ?: 'dokumen');
        $disposition = $inline ? 'inline' : 'attachment';

        return Storage::disk(self::DISK)->response($path, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-cache, private, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
