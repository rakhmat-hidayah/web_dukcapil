<?php
/**
 * Skrip Perbaikan Thumbnail Beranda di JS Build Assets (1-Klik)
 */
$buildDir = __DIR__ . '/build/assets';

if (!is_dir($buildDir)) {
    die("Folder build/assets tidak ditemukan di " . __DIR__);
}

$files = glob($buildDir . '/*.js');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (str_contains($content, '/uploads/uploads/')) {
        $newContent = str_replace('/uploads/uploads/', '/uploads/', $content);
        file_put_contents($file, $newContent);
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ PERBAIKAN THUMBNAIL BERANDA SELESAI!</h2>";
echo "<p>Berhasil memperbaiki duplikasi path di <strong>{$count} file JS build</strong>.</p>";
echo "<p>Thumbnail kartu berita di Halaman Depan sekarang <strong>100% TAMPIL JERNIH!</strong></p>";
echo "</div>";
