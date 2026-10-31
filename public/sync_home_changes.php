<?php
/**
 * Skrip Sinkronisasi Otomatis Perubahan Home.vue ke JS Build Assets (1-Klik)
 */
$vueFile = __DIR__ . '/../resources/js/Pages/Public/Home.vue';
$buildDir = __DIR__ . '/build/assets';

if (!file_exists($vueFile)) {
    die("File Home.vue tidak ditemukan.");
}

$vueContent = file_get_contents($vueFile);

// Ambil string teks judul dari Home.vue (baris 502)
if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $vueContent, $match)) {
    $newTitleHtml = trim($match[1]);
    $newTitleClean = html_entity_decode(strip_tags($newTitleHtml));
} else {
    $newTitleClean = 'Aspirasi & Pengaduan Masyarakat (Lapor!)';
}

$files = glob($buildDir . '/*.js');
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (str_contains($content, 'Aspirasi') || str_contains($content, 'Pengaduan')) {
        // Replace target strings
        $newContent = str_replace([
            'Aspirasi & Pengaduan Rakyat (Lapor!)',
            'Aspirasi &amp; Pengaduan Rakyat (Lapor!)',
            'Pengaduan Rakyat',
        ], [
            $newTitleClean,
            $newTitleClean,
            'Pengaduan Masyarakat',
        ], $content);

        file_put_contents($file, $newContent);
        $count++;
    }
}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ SINKRONISASI TEKS HOME.VUE BERHASIL!</h2>";
echo "<p>Berhasil menerapkan perubahan teks dari <code>Home.vue</code> ke <strong>{$count} file JS build</strong>:</p>";
echo "<h3 style='color:#047857;'>\"" . htmlspecialchars($newTitleClean) . "\"</h3>";
echo "</div>";
