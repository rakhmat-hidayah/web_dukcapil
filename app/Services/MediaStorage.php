<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaStorage
{
    /**
     * Store an uploaded file in ALL possible storage locations simultaneously.
     * Guaranteed to work regardless of Plesk Document Root setting.
     */
    public static function store(UploadedFile $file, string $folder): string
    {
        $ext = $file->getClientOriginalExtension() ?: 'png';
        $filename = Str::uuid() . '.' . $ext;
        $relativePath = trim($folder, '/') . '/' . $filename;

        $destinations = array_unique([
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),
            base_path('storage/' . $relativePath),
            base_path('public/storage/' . $relativePath),
        ]);

        foreach ($destinations as $dest) {
            $dir = dirname($dest);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @copy($file->getRealPath(), $dest);
        }

        return $relativePath;
    }

    /**
     * Write raw content (e.g. Intervention Image output) to all storage locations.
     */
    public static function put(string $relativePath, string $content): void
    {
        $relativePath = ltrim($relativePath, '/');

        $destinations = array_unique([
            storage_path('app/public/' . $relativePath),
            public_path('storage/' . $relativePath),
            base_path('storage/' . $relativePath),
            base_path('public/storage/' . $relativePath),
        ]);

        foreach ($destinations as $dest) {
            $dir = dirname($dest);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @file_put_contents($dest, $content);
        }
    }
}
