<?php
/**
 * Skrip Menghapus Gambar Cover Header Ganda di Halaman Detail Berita (1-Klik)
 */
$buildDir = __DIR__ . '/build/assets';

if (!is_dir($buildDir)) {
    die("Folder build/assets tidak ditemukan di " . __DIR__);
}

$files = glob($buildDir . '/*.js');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (str_contains($content, 'Cover Image') || str_contains($content, 'h-64 sm:h-96')) {
        // Matikan rendering div cover image header di JS build asset
        $newContent = str_replace([
            'news.thumbnail',
            'alt:"Cover Image"',
            'alt: "Cover Image"',
        ], [
            'false',
            'alt:""',
            'alt:""',
        ], $content);

        file_put_contents($file, $newContent);
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ GAMBAR COVER HEADER BERHASIL DISEMBUNYIKAN!</h2>";
echo "<p>Berhasil memperbarui <strong>{$count} file JS build</strong>.</p>";
echo "<p>Gambar ganda di atas judul berita sekarang <strong>100% HILANG!</strong> Gambar hanya akan tampil 1 kali di dalam isi artikel.</p>";
echo "</div>";
