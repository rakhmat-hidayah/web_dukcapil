<?php
/**
 * Skrip Perbaikan Placeholder Kartu Berita di JS Build Assets (1-Klik)
 */
$buildDir = __DIR__ . '/build/assets';

if (!is_dir($buildDir)) {
    die("Folder build/assets tidak ditemukan di " . __DIR__);
}

$files = glob($buildDir . '/*.js');
$count = 0;

$oldPattern = "https://images.unsplash.com/photo-1495020689067-958852a6565d?auto=format&fit=crop&w=400&q=80";

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (str_contains($content, 'unsplash') || str_contains($content, 'Thumbnail')) {
        // Matikan fallback unsplash yang diblokir CORS dan ganti dengan placeholder lokal
        $newContent = str_replace([
            $oldPattern,
            'alt:"Thumbnail"',
            'alt: "Thumbnail"',
        ], [
            '/images/logo-dukcapil.png',
            'alt:""',
            'alt:""',
        ], $content);

        file_put_contents($file, $newContent);
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ PERBAIKAN PLACEHOLDER KARTU BERITA SELESAI!</h2>";
echo "<p>Berhasil memperbarui placeholder pada <strong>{$count} file JS build</strong>.</p>";
echo "<p>Berita yang belum memiliki foto sekarang <strong>MENAMPILKAN CARD PLACEHOLDER ELEGANT DUKCAPIL NEWS!</strong></p>";
echo "</div>";
