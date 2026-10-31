<?php
/**
 * Debug: Tampilkan data JSON persis yang dikirim ke browser (Inertia props)
 * untuk memastikan thumbnail path sudah /uploads/ bukan /storage/
 */
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Ambil data persis seperti HomeController
$recentNews = DB::table('news')
    ->join('news_categories', 'news.category_id', '=', 'news_categories.id', 'left')
    ->where('news.status', 'published')
    ->whereNotNull('news.published_at')
    ->where('news.published_at', '<=', now())
    ->orderBy('news.published_at', 'desc')
    ->limit(6)
    ->get(['news.id', 'news.title', 'news.thumbnail', 'news.slug', 'news_categories.name as category_name']);

echo "<style>body{font-family:sans-serif;padding:20px;max-width:1200px;} table{width:100%;border-collapse:collapse;} td,th{padding:8px;border:1px solid #ddd;font-size:12px;} .bad{background:#fef2f2;} .good{background:#f0fdf4;}</style>";
echo "<h1>🔍 DEBUG DATA NEWS YANG DIKIRIM KE BROWSER</h1>";
echo "<p>Data persis dari database sebelum proses HomeController:</p>";

echo "<table><tr><th>ID</th><th>Judul</th><th>Thumbnail di DB (RAW)</th><th>Thumbnail setelah process</th><th>URL Final di Browser</th></tr>";

foreach ($recentNews as $item) {
    $rawThumb = $item->thumbnail;
    
    // Simulasi proses HomeController
    $processed = $rawThumb ? ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $rawThumb), '/') : null;
    
    // URL final yang akan di-render di browser
    $finalUrl = $processed ? '/uploads/' . $processed : null;
    
    // Cek apakah file fisik ada
    $fileExists = $finalUrl ? file_exists(public_path(ltrim($finalUrl, '/'))) : false;
    
    $class = ($finalUrl && str_contains($finalUrl, 'storage')) ? 'bad' : ($fileExists ? 'good' : '');
    
    echo "<tr class='{$class}'>";
    echo "<td>{$item->id}</td>";
    echo "<td>" . htmlspecialchars(substr($item->title, 0, 40)) . "</td>";
    echo "<td style='word-break:break-all;font-family:monospace;font-size:10px'>" . htmlspecialchars($rawThumb ?? 'NULL') . "</td>";
    echo "<td style='word-break:break-all;font-family:monospace;font-size:10px'>" . htmlspecialchars($processed ?? 'NULL') . "</td>";
    echo "<td style='word-break:break-all;font-family:monospace;font-size:10px'>" . ($finalUrl ? "<a href='{$finalUrl}' target='_blank'>{$finalUrl}</a>" : 'NULL') . " " . ($fileExists ? '✅' : '❌ File tidak ada') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2 style='margin-top:30px'>🔗 Test Langsung URL Gambar</h2>";
echo "<p>Klik link di bawah untuk test apakah URL bisa diakses:</p>";
echo "<ul>";
foreach ($recentNews as $item) {
    if ($item->thumbnail) {
        $processed = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $item->thumbnail), '/');
        $finalUrl = '/uploads/' . $processed;
        echo "<li><a href='{$finalUrl}' target='_blank'>{$finalUrl}</a></li>";
    }
}
echo "</ul>";

// Cek juga apakah ada tabel 'news' langsung
echo "<h2>📊 Raw DB news.thumbnail values:</h2>";
$raw = DB::select("SELECT id, title, thumbnail FROM news WHERE status='published' AND published_at <= NOW() ORDER BY published_at DESC LIMIT 6");
echo "<pre style='background:#f5f5f5;padding:10px;font-size:11px'>";
foreach ($raw as $r) {
    echo "ID {$r->id}: " . json_encode($r->thumbnail) . "\n";
}
echo "</pre>";
