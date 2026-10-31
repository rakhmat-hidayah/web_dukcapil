<?php
/**
 * Skrip Perbaikan Teks Banner Home di File Terkompilasi Build Assets (1-Klik)
 */
$buildDir = __DIR__ . '/build/assets';

if (!is_dir($buildDir)) {
    die("Folder build/assets tidak ditemukan di " . __DIR__);
}

$files = glob($buildDir . '/*.js');
$count = 0;

$newText = $_GET['title'] ?? null;

if (!$newText) {
    echo "<div style='font-family:sans-serif; padding:20px; background:#fefce8; border:1px solid #fef08a; color:#854d0e; border-radius:12px;'>";
    echo "<h3>ℹ️ Cara Penggunaan:</h3>";
    echo "<p>Buka URL ini dengan menambahkan parameter <code>?title=Teks+Baru+Anda</code> di belakangnya.</p>";
    echo "<p>Contoh: <code>https://dukcapil.dompukab.go.id/update_home_banner.php?title=Layanan+Pengaduan+Masyarakat</code></p>";
    echo "</div>";
    exit;
}

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (str_contains($content, 'Aspirasi') || str_contains($content, 'Pengaduan Rakyat')) {
        $newContent = str_replace([
            'Aspirasi & Pengaduan Rakyat (Lapor!)',
            'Aspirasi &amp; Pengaduan Rakyat (Lapor!)',
        ], $newText, $content);

        file_put_contents($file, $newContent);
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "## ✅ UPDATE TEKS BANNER BERHASIL!</h2>";
echo "<p>Berhasil memperbarui teks banner di <strong>{$count} file JS build</strong> menjadi: <strong>" . htmlspecialchars($newText) . "</strong></p>";
echo "</div>";
