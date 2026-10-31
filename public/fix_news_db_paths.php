<?php
/**
 * Skrip Pembersih Path Storage ke Uploads pada Database News (1-Klik)
 */
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\News;

$newsItems = News::whereNotNull('thumbnail')->get();
$count = 0;

foreach ($newsItems as $news) {
    if (str_contains($news->thumbnail, 'storage/')) {
        $clean = ltrim(str_replace(['/storage/', 'storage/'], '', $news->thumbnail), '/');
        $news->thumbnail = 'uploads/' . $clean;
        $news->save();
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ PERBAIKAN DATABASE THUMBNAIL BERITA SELESAI!</h2>";
echo "<p>Berhasil memperbarui path thumbnail pada <strong>{$count} berita</strong> di database.</p>";
echo "<p>Semua URL berita di database sekarang <strong>MURNI MENGARAHKAN KE /uploads/</strong> tanpa blokir Cloudflare WAF (403 Forbidden).</p>";
echo "</div>";
