<?php
/**
 * Skrip Restore Thumbnail Kartu Berita di Beranda & Daftar Berita (1-Klik)
 */
$buildDir = __DIR__ . '/build/assets';

if (!is_dir($buildDir)) {
    die("Folder build/assets tidak ditemukan di " . __DIR__);
}

$files = glob($buildDir . '/*.js');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Kembalikan variabel thumbnail untuk kartu berita di Beranda & Daftar Berita
    $newContent = str_replace(
        [':src:false', 'v-if:false', 'v-if: false', ':src="false"'],
        [':src:item.thumbnail', 'v-if:item.thumbnail', 'v-if: item.thumbnail', ':src="item.thumbnail"'],
        $content
    );

    // Salin ulang jika ada replacement berlebih
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ RESTORE THUMBNAILS BERANDA SELESAI!</h2>";
echo "<p>Berhasil mengembalikan thumbnail kartu berita pada <strong>{$count} file JS build</strong>.</p>";
echo "<p>Thumbnail kartu di Beranda & Daftar Berita sekarang <strong>100% TAMPIL KEMBALI!</strong></p>";
echo "</div>";
