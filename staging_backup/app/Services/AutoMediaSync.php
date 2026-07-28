<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoMediaSync
{
    public static function sync(): void
    {
        try {
            $basePath = base_path();
            $publicStorage = public_path('storage');
            $storageAppPublic = storage_path('app/public');

            $subDirs = [
                'officials', 'banners', 'branding', 'news', 'leader', 'gallery',
                'gallery/covers', 'gallery/1', 'downloads', 'downloads/forms',
                'file_manager', 'ppid-documents', 'ppid-responses', 'complaints', 'demographics'
            ];

            foreach ([$publicStorage, $storageAppPublic] as $targetDir) {
                if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
                foreach ($subDirs as $sd) {
                    $p = $targetDir . '/' . $sd;
                    if (!is_dir($p)) @mkdir($p, 0775, true);
                }
            }

            // 1. Copy from storage/app/public -> public/storage if missing
            self::copyMissing($storageAppPublic, $publicStorage);

            // 2. Scan misplaced root folders (e.g. httpdocs/gallery, httpdocs/officials)
            $mediaTypes = ['gallery', 'officials', 'banners', 'leader', 'news', 'downloads', 'branding', 'complaints'];
            foreach ($mediaTypes as $mt) {
                $misplaced = $basePath . '/' . $mt;
                if (is_dir($misplaced)) {
                    self::copyMissing($misplaced, $publicStorage . '/' . $mt);
                    self::copyMissing($misplaced, $storageAppPublic . '/' . $mt);
                }
            }

            // 3. Fix leader photo setting in DB if default or missing file
            $leaderRow = DB::table('website_settings')->where('key', 'leader_photo')->first();
            if (!$leaderRow || $leaderRow->value === '/images/kepala_dinas.jpg' || !file_exists(public_path(ltrim($leaderRow->value, '/')))) {
                $leaderFiles = glob($publicStorage . '/leader/*.*');
                if (!empty($leaderFiles)) {
                    $fn = basename($leaderFiles[0]);
                    DB::table('website_settings')->updateOrInsert(
                        ['key' => 'leader_photo'],
                        ['value' => '/storage/leader/' . $fn, 'group' => 'pimpinan', 'updated_at' => now()]
                    );
                    \App\Models\WebsiteSetting::clearCache();
                    \Illuminate\Support\Facades\Cache::forget('website_settings_all');
                }
            }

            // 4. Fix banner images in DB if missing file
            $banners = DB::table('banners')->whereNull('deleted_at')->get();
            $bannerFiles = array_values(array_filter(scandir($publicStorage . '/banners') ?: [], fn($f) => $f !== '.' && $f !== '..'));
            if (!empty($bannerFiles)) {
                $bi = 0;
                foreach ($banners as $b) {
                    if (!$b->image || !file_exists($publicStorage . '/' . $b->image)) {
                        $newImg = 'banners/' . $bannerFiles[$bi % count($bannerFiles)];
                        DB::table('banners')->where('id', $b->id)->update(['image' => $newImg, 'updated_at' => now()]);
                        $bi++;
                    }
                }
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
