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
    ->get(['news.id', 'news.title', 'news.thumbnail', 'news.slug']);

echo "<style>body{font-family:sans-serif;padding:20px;max-width:1200px;} table{width:100%;border-collapse:collapse;} td,th{padding:8px;border:1px solid #ddd;font-size:12px;} .bad{background:#fef2f2;} .good{background:#f0fdf4;}</style>";
echo "<h1>🔍 DEBUG DATA NEWS YANG DIKIRIM KE BROWSER</h1>";

echo "<table><tr><th>ID</th><th>Judul</th><th>Thumbnail di DB (RAW)</th><th>Thumbnail setelah process</th><th>URL Final di Browser</th></tr>";

foreach ($recentNews as $item) {
    $rawThumb = $item->thumbnail;
    $processed = $rawThumb ? ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $rawThumb), '/') : null;
    $finalUrl = $processed ? '/uploads/' . $processed : null;
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

echo "<h2 style='margin-top:30px'>📊 Raw DB Values:</h2>";
$raw = DB::select("SELECT id, title, thumbnail FROM news WHERE status='published' AND published_at <= NOW() ORDER BY published_at DESC LIMIT 6");
echo "<pre style='background:#f5f5f5;padding:10px;font-size:11px'>";
foreach ($raw as $r) {
    echo "ID {$r->id}: " . json_encode($r->thumbnail) . "\n";
}
echo "</pre>";

echo "<h2>🧪 Test akses gambar langsung:</h2>";
echo "<div style='display:grid;grid-template-columns:repeat(3,1fr);gap:10px;'>";
foreach ($recentNews as $item) {
    if ($item->thumbnail) {
        $processed = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $item->thumbnail), '/');
        $finalUrl = '/uploads/' . $processed;
        echo "<div style='border:1px solid #ddd;padding:8px;border-radius:4px;'>";
        echo "<p style='font-size:10px;word-break:break-all;margin:0 0 4px'>{$finalUrl}</p>";
        echo "<img src='{$finalUrl}' style='max-width:100%;height:80px;object-fit:cover;' onerror=\"this.src='';this.parentNode.style.background='#fef2f2';this.insertAdjacentHTML('afterend','<span style=\\'color:red;font-size:10px\\'>❌ 403/404</span>')\">";
        echo "</div>";
    }
}
echo "</div>";
