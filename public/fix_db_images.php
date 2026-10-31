<?php
/**
 * COMPREHENSIVE DATABASE REPAIR TOOL
 * Upload: httpdocs/public/fix_db_images.php
 * Run: https://dukcapil.dompukab.go.id/fix_db_images.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') die('403');
header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo '<pre style="font-family:monospace;font-size:14px;background:#09090b;color:#f4f4f5;padding:24px;line-height:1.6;border-radius:12px">';
echo "<h2 style='color:#38bdf8;margin:0 0 16px 0'>🛠️ COMPREHENSIVE DATABASE & CACHE REPAIR TOOL</h2>\n";

$publicDir = __DIR__;

// 1. Reset OPcache
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "⚡ PHP OPcache reset successfully!\n";
}

// 2. Clean 'news' table specifically
$newsList = DB::table('news')->get();
$newsCleaned = 0;

foreach ($newsList as $item) {
    $up = [];

    if ($item->thumbnail) {
        $thumb = $item->thumbnail;
        // Strip domain name if present
        $thumb = preg_replace('#^https?://[^/]+#i', '', $thumb);
        // Replace /storage/ with /uploads/
        $thumb = str_replace(['/storage/', 'storage/'], ['/uploads/', 'uploads/'], $thumb);
        
        $cleanRel = ltrim(str_replace(['/uploads/', 'uploads/'], '', $thumb), '/');
        $fullPath = $publicDir . '/uploads/' . $cleanRel;

        if (!file_exists($fullPath) || is_dir($fullPath)) {
            echo "❌ News ID {$item->id} ('{$item->title}'): File uploads/{$cleanRel} MISSING -> Resetting thumbnail to NULL (Vue fallback enabled!)\n";
            $up['thumbnail'] = null;
        } else {
            echo "✅ News ID {$item->id}: Valid file found -> uploads/{$cleanRel}\n";
            $up['thumbnail'] = 'uploads/' . $cleanRel;
        }
    }

    if ($item->content) {
        $newContent = preg_replace('#https?://[^/]+/(storage|uploads)/#i', '/uploads/', $item->content);
        $newContent = str_replace(['/storage/', 'storage/file_manager'], ['/uploads/', 'uploads/file_manager'], $newContent);
        if ($newContent !== $item->content) {
            $up['content'] = $newContent;
        }
    }

    if (!empty($up)) {
        DB::table('news')->where('id', $item->id)->update($up);
        $newsCleaned++;
    }
}
echo "\n✨ Total News database records updated: {$newsCleaned}\n";

// 3. Scan & clean ALL other database tables containing /storage/
$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tableKey = "Tables_in_" . $dbName;
$globalCleaned = 0;

foreach ($tables as $t) {
    $tableName = $t->$tableKey;
    $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
    
    foreach ($columns as $col) {
        try {
            $affected = DB::statement("
                UPDATE `{$tableName}` 
                SET `{$col}` = REPLACE(REPLACE(`{$col}`, 'https://dukcapil.dompukab.go.id/storage/', '/uploads/'), '/storage/', '/uploads/')
                WHERE `{$col}` LIKE '%/storage/%'
            ");
        } catch (\Throwable $e) {
            // ignore non-string columns
        }
    }
}
echo "✨ Global SQL sweep executed on all database tables!\n";

// 4. Clear all Laravel Caches
try {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "🧹 Laravel Caches (Cache, Config, View) Cleared!\n";
} catch (\Throwable $e) {
    echo "⚠️ Cache Clear Warning: " . $e->getMessage() . "\n";
}

echo "\n<span style='color:#4ade80;font-weight:bold;font-size:16px'>🎉 PERBAIKAN TOTAL SELESAI! Silakan buka & refresh website Dukcapil di Chrome!</span>\n";
echo '</pre>';
