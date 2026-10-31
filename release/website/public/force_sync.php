<?php
/**
 * Aggressive Force Sync All Build Assets & Flushes OPcache (1-Klik)
 */
$buildDir = __DIR__ . '/build';

$old1 = 'Aspirasi & Pengaduan Rakyat (Lapor!)';
$old2 = 'Aspirasi &amp; Pengaduan Rakyat (Lapor!)';
$old3 = 'Pengaduan Rakyat';

$new = 'Aspirasi & Pengaduan Masyarakat (Lapor!)';

if (!is_dir($buildDir)) {
    die("Folder build tidak ditemukan.");
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($buildDir));
$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['js', 'json', 'html'])) {
        $content = file_get_contents($file->getPathname());
        if (str_contains($content, 'Rakyat') || str_contains($content, 'Aspirasi')) {
            $updated = str_replace([$old1, $old2, $old3], [$new, $new, 'Pengaduan Masyarakat'], $content);
            file_put_contents($file->getPathname(), $updated);
            $count++;
        }
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ SINKRONISASI TOTAL COMPLETED!</h2>";
echo "<p>Berhasil memperbarui teks di <strong>{$count} file build & manifest</strong> menjadi:</p>";
echo "<h3 style='color:#047857;'>\"{$new}\"</h3>";
echo "<p>Penting: Buka halaman website lalu tekan <strong>Ctrl + Shift + R</strong> (atau <strong>Ctrl + F5</strong>) untuk membuang cache lama browser.</p>";
echo "</div>";
