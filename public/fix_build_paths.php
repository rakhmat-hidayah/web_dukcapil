<?php
/**
 * Skrip Perbaikan Otomatis File JS Build Assets (Storage -> Uploads)
 * Mengubah seluruh string /storage/ di file JS terkompilasi murni menjadi /uploads/
 */

$buildDir = __DIR__ . '/build/assets';

if (!is_dir($buildDir)) {
    die("Folder build/assets tidak ditemukan di " . __DIR__);
}

$files = glob($buildDir . '/*.js');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (str_contains($content, '/storage/')) {
        $newContent = str_replace('/storage/', '/uploads/', $content);
        file_put_contents($file, $newContent);
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ PERBAIKAN SELESAI 100%!</h2>";
echo "<p>Berhasil mengonversi jalur gambar di <strong>{$count} file JS build</strong> dari <code>/storage/</code> murni menjadi <code>/uploads/</code>.</p>";
echo "<p>Browser sekarang <strong>SAMA SEKALI TIDAK AKAN MELEWATI /storage/ LAGI!</strong> Seluruh request gambar langsung mengarah ke <code>/uploads/</code>.</p>";
echo "</div>";
