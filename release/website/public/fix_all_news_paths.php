<?php
/**
 * Fix ALL storage/ paths in News content (inline images) + thumbnails
 * Also clean old WordPress URLs
 */
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$news = DB::table('news')->get(['id', 'title', 'thumbnail', 'content']);
$thumbFixed = 0;
$contentFixed = 0;
$wpFixed = 0;

echo "<style>body{font-family:sans-serif;padding:20px;max-width:1200px;} .section{margin:20px 0;padding:16px;border-radius:8px;} .ok{background:#f0fdf4;border:1px solid #22c55e;color:#166534;} .warn{background:#fefce8;border:1px solid #eab308;color:#854d0e;} .info{background:#eff6ff;border:1px solid #3b82f6;color:#1d4ed8;}</style>";
echo "<h1>🔧 PERBAIKAN TOTAL THUMBNAIL & KONTEN BERITA</h1>";

$results = [];

foreach ($news as $n) {
    $changes = [];
    $newThumb = $n->thumbnail;
    $newContent = $n->content;

    // Fix thumbnail: remove storage/ prefix
    if ($n->thumbnail && (str_contains($n->thumbnail, 'storage/') || str_starts_with($n->thumbnail, '/storage'))) {
        $clean = ltrim(str_replace(['/storage/', 'storage/'], '', $n->thumbnail), '/');
        $newThumb = 'uploads/' . $clean;
        $changes[] = "Thumbnail: storage/ → uploads/";
        $thumbFixed++;
    }

    // Fix old WordPress URLs in thumbnail
    if ($n->thumbnail && str_contains($n->thumbnail, 'wp-content')) {
        $newThumb = null;
        $changes[] = "Thumbnail WordPress URL → dihapus";
        $wpFixed++;
    }

    // Fix inline content: replace /storage/ image URLs with /uploads/
    if ($n->content && str_contains($n->content, '/storage/')) {
        $newContent = preg_replace_callback(
            '/(src=["\'])\/storage\/([^"\']+)(["\'])/i',
            function($m) {
                return $m[1] . '/uploads/' . $m[2] . $m[3];
            },
            $n->content
        );
        if ($newContent !== $n->content) {
            $changes[] = "Konten artikel: /storage/ → /uploads/";
            $contentFixed++;
        }
    }

    if (!empty($changes)) {
        DB::table('news')->where('id', $n->id)->update([
            'thumbnail' => $newThumb,
            'content' => $newContent,
            'updated_at' => now(),
        ]);
        $results[] = ['id' => $n->id, 'title' => $n->title, 'changes' => $changes];
    }
}

if (empty($results)) {
    echo "<div class='warn section'><h3>ℹ️ Tidak ada data yang perlu diperbaiki di database.</h3></div>";
} else {
    echo "<div class='ok section'><h3>✅ BERHASIL MEMPERBAIKI " . count($results) . " BERITA!</h3>";
    echo "<ul><li>Thumbnail storage/: <strong>{$thumbFixed}</strong></li><li>Thumbnail WP URL: <strong>{$wpFixed}</strong></li><li>Konten inline: <strong>{$contentFixed}</strong></li></ul></div>";
    echo "<table border='1' style='width:100%;border-collapse:collapse;font-size:12px;'>";
    echo "<tr style='background:#f3f4f6'><th>ID</th><th>Judul</th><th>Perbaikan</th></tr>";
    foreach ($results as $r) {
        echo "<tr><td>{$r['id']}</td><td>" . htmlspecialchars(substr($r['title'], 0, 50)) . "</td><td>" . implode('<br>', $r['changes']) . "</td></tr>";
    }
    echo "</table>";
}

\Illuminate\Support\Facades\Cache::flush();
echo "<div class='info section'><h3>🧹 Laravel Cache dibersihkan!</h3></div>";
echo "<p><a href='/' style='padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>← Kembali ke Beranda</a></p>";
