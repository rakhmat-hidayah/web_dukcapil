<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaStorage
{
    /**
     * Allowed upload MIME types and extensions.
     */
    protected static array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'webp', 'svg', 'gif',
        'pdf', 'docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt',
        'zip', 'rar', 'txt', 'csv'
    ];

    /**
     * Strictly blocked executable extensions.
     */
    protected static array $blockedExtensions = [
        'php', 'exe', 'bat', 'cmd', 'js', 'sh', 'dll', 'cgi', 'pl', 'asp', 'aspx', 'jsp', 'phar'
    ];

    /**
     * Store raw binary content directly into public/uploads/$path.
     *
     * @param string $path E.g., 'banners/uuid.webp'
     * @param string $content Raw image/file binary stream
     * @return string Relative path starting with 'uploads/$path'
     */
    public static function put(string $path, string $content): string
    {
        $cleanPath = ltrim(str_replace(['storage/', '/storage/', 'uploads/', '/uploads/'], '', $path), '/');
        $fullPath = public_path('uploads/' . $cleanPath);

        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, $content);

        return 'uploads/' . $cleanPath;
    }

    /**
     * Store an uploaded file directly into public/uploads/$subfolder.
     *
     * @param UploadedFile $file
     * @param string $subfolder E.g., 'banners', 'news', 'officials'
     * @param string|null $prefix Optional filename prefix
     * @return string Relative path starting with 'uploads/$subfolder/filename.ext'
     */
    public static function store(UploadedFile $file, string $subfolder = 'attachments', ?string $prefix = null): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, self::$blockedExtensions) || !in_array($extension, self::$allowedExtensions)) {
            throw new \InvalidArgumentException("Format file (.{$extension}) tidak diizinkan untuk di-upload demi keamanan sistem.");
        }

        // Clean subfolder name
        $subfolder = trim(str_replace(['..', '\\'], ['', '/'], $subfolder), '/');
        $targetDir = public_path('uploads/' . $subfolder);

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generate sanitized, unique filename
        $prefix = $prefix ? Str::slug($prefix) : 'file';
        $timestamp = date('Ymd-His');
        $random = Str::random(6);
        $filename = "{$prefix}-{$timestamp}-{$random}.{$extension}";

        // Move file directly into public/uploads/$subfolder
        $file->move($targetDir, $filename);

        return "uploads/{$subfolder}/{$filename}";
    }

    /**
     * Delete a file from public/uploads.
     *
     * @param string|null $path Relative or full path
     * @return bool
     */
    public static function delete(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Normalize path
        $cleanPath = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $path), '/');
        $fullPath = public_path('uploads/' . $cleanPath);

        if (file_exists($fullPath) && is_file($fullPath)) {
            return @unlink($fullPath);
        }

        // Check if path is absolute public path
        if (file_exists($path) && is_file($path)) {
            return @unlink($path);
        }

        return false;
    }

    /**
     * Get full public URL for an uploaded file.
     *
     * @param string|null $path
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Normalize legacy /storage/ paths to /uploads/
        $cleanPath = ltrim(str_replace(['/storage/', 'storage/'], 'uploads/', $path), '/');

        if (!str_starts_with($cleanPath, 'uploads/')) {
            $cleanPath = 'uploads/' . $cleanPath;
        }

        return '/' . ltrim($cleanPath, '/');
    }

    /**
     * Alias for url()
     */
    public static function getUrl(?string $path): ?string
    {
        return static::url($path);
    }
}
