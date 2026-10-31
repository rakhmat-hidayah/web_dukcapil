<?php
/**
 * Diagnostic & Fix Script - News Thumbnail Paths
 * Shows all thumbnail paths in DB and fixes storage/ -> uploads/
 */
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$news = DB::table('news')->whereNotNull('thumbnail')->get(['id', 'title', 'thumbnail']);

echo "<style>body{font-family:sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;} td,th{padding:8px 12px;border:1px solid #ddd;text-align:left;font-size:12px;} .bad{background:#fef2f2;color:#dc2626;} .good{background:#f0fdf4;color:#166534;} .fixed{background:#fefce8;color:#854d0e;}</style>";
echo "<h2>📊 STATUS THUMBNAIL BERITA DI DATABASE</h2>";
echo "<table><tr><th>ID</th><th>Judul</th><th>Path Lama</th><th>Status</th><th>File Ada?</th></tr>";

$fixCount = 0;

foreach ($news as $n) {
    $thumb = $n->thumbnail;
    $isStorage = str_contains($thumb, 'storage/') || str_starts_with($thumb, '/storage');
    
    // Compute expected public file path
    $cleanPath = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $thumb), '/');
    $publicPath = public_path('uploads/' . $cleanPath);
    $fileExists = file_exists($publicPath);
    
    $statusClass = $isStorage ? 'bad' : ($fileExists ? 'good' : 'bad');
    $statusText = $isStorage ? '❌ storage/ - DIBLOKIR CLOUDFLARE' : ($fileExists ? '✅ uploads/ - OK' : '⚠️ File tidak ada di disk');
    
    echo "<tr class='{$statusClass}'>";
    echo "<td>{$n->id}</td>";
    echo "<td>" . htmlspecialchars(substr($n->title, 0, 50)) . "</td>";
    echo "<td style='font-size:10px;word-break:break-all'>{$thumb}</td>";
    echo "<td>{$statusText}</td>";
    echo "<td>" . ($fileExists ? '✅ Ada' : '❌ Tidak ada') . "</td>";
    echo "</tr>";
    
    // Auto-fix storage/ paths
    if ($isStorage) {
        DB::table('news')->where('id', $n->id)->update(['thumbnail' => 'uploads/' . $cleanPath]);
        $fixCount++;
    }
}

echo "</table>";

if ($fixCount > 0) {
    echo "<div style='margin-top:20px;padding:16px;background:#f0fdf4;border:1px solid #22c55e;border-radius:8px;color:#166534;'>";
    echo "<h3>✅ Auto-Fixed: {$fixCount} thumbnail berhasil diperbaiki dari storage/ ke uploads/</h3>";
    echo "</div>";
} else {
    echo "<div style='margin-top:20px;padding:16px;background:#fefce8;border:1px solid #eab308;border-radius:8px;color:#854d0e;'>";
    echo "<h3>⚠️ Tidak ada path storage/ yang perlu diperbaiki di database. Masalah ada di tempat lain.</h3>";
    echo "</div>";
}

// Also show what files exist in uploads/news/
echo "<h2 style='margin-top:30px;'>📁 File Fisik di public/uploads/news/</h2>";
$newsDir = public_path('uploads/news');
if (is_dir($newsDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($newsDir, RecursiveDirectoryIterator::SKIP_DOTS));
    echo "<ul style='font-size:11px;font-family:monospace;'>";
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $relPath = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            echo "<li>/{$relPath}</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>Folder uploads/news tidak ditemukan.</p>";
}

// Also show file_manager files
echo "<h2 style='margin-top:30px;'>📁 File Fisik di public/uploads/file_manager/</h2>";
$fmDir = public_path('uploads/file_manager');
if (is_dir($fmDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fmDir, RecursiveDirectoryIterator::SKIP_DOTS));
    echo "<ul style='font-size:11px;font-family:monospace;'>";
    $i = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && $i < 30) {
            $relPath = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
            echo "<li>/{$relPath}</li>";
            $i++;
        }
    }
    echo "</ul>";
} else {
    echo "<p>Folder uploads/file_manager tidak ditemukan.</p>";
}
