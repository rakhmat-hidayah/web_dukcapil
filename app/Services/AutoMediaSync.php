<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoMediaSync
{
    public static function sync(): void
    {
        // Avoid heavy recursive disk scan on every web request
        if (Cache::has('auto_media_sync_v2_done')) {
            return;
        }

        try {
            Cache::put('auto_media_sync_v2_done', true, now()->addHours(24));

            $publicUploads = public_path('uploads');
            $storageAppPublic = storage_path('app/public');

            $subDirs = [
                'officials', 'banners', 'branding', 'news', 'leader', 'gallery',
                'gallery/covers', 'downloads', 'file_manager', 'ppid-documents',
                'ppid-responses', 'complaints', 'demographics', 'logos', 'favicon'
            ];

            foreach ([$publicUploads, $storageAppPublic] as $targetDir) {
                if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
                foreach ($subDirs as $sd) {
                    $p = $targetDir . '/' . $sd;
                    if (!is_dir($p)) @mkdir($p, 0775, true);
                }
            }

            // 1. Copy missing from storage/app/public -> public/uploads
            self::copyMissing($storageAppPublic, $publicUploads);

            // 2. Fix leader photo setting in DB if pointing to old /storage/
            $leaderRow = DB::table('website_settings')->where('key', 'leader_photo')->first();
            if ($leaderRow && str_contains($leaderRow->value, '/storage/')) {
                $newValue = str_replace('/storage/', 'uploads/', $leaderRow->value);
                DB::table('website_settings')->where('key', 'leader_photo')->update([
                    'value' => $newValue,
                    'updated_at' => now()
                ]);
                \App\Models\WebsiteSetting::clearCache();
            }

        } catch (\Throwable $e) {
            Log::error('AutoMediaSync error: ' . $e->getMessage());
        }
    }

    private static function copyMissing(string $src, string $dst): void
    {
        if (!is_dir($src) || !is_dir($dst)) return;
        $realSrc = realpath($src);
        $realDst = realpath($dst);
        if ($realSrc && $realDst && str_starts_with($realDst, $realSrc)) return;

        try {
            $items = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($items as $item) {
                $itemPath = $item->getRealPath();
                if ($realDst && str_starts_with($realDst, $itemPath)) continue;

                $target = $dst . str_replace($src, '', $itemPath);
                if ($item->isDir()) {
                    if (!is_dir($target)) @mkdir($target, 0775, true);
                } else {
                    if (!file_exists($target)) {
                        @copy($itemPath, $target);
                    }
                }
            }
        } catch (\Throwable $t) {
            // Ignore unreadable directory errors
        }
    }
}
