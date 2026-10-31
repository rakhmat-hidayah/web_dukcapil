<?php
/**
 * DIAG3 - Final State Verification
 * Upload: httpdocs/public/diag3.php
 * Run: https://dukcapil.dompukab.go.id/diag3.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') die('403');
header('Content-Type: text/html; charset=utf-8');
$public   = __DIR__;
$root     = dirname(__DIR__);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Diag3</title>
<style>
body{font-family:monospace;padding:20px;background:#1a1a1a;color:#eee}
.card{background:#2a2a2a;padding:15px;border-radius:8px;margin:10px 0;border-left:4px solid #555}
.ok{color:#4ade80} .err{color:#f87171} .warn{color:#fb923c}
pre{background:#111;padding:10px;overflow-x:auto;font-size:12px;border-radius:4px}
h2{color:#60a5fa} h3{color:#a78bfa;margin-top:10px}
</style>
</head><body>
<h2>🔬 Final Diagnostic — Dukcapil</h2>

<?php
function ok($msg) { echo "<span class='ok'>✅ $msg</span><br>"; }
function err($msg) { echo "<span class='err'>❌ $msg</span><br>"; }
function warn($msg) { echo "<span class='warn'>⚠️ $msg</span><br>"; }
?>

<!-- 1. CHECK .HTACCESS ON SERVER -->
<div class="card">
<h3>1. .htaccess di Server (yang AKTIF saat ini)</h3>
<?php
$ht = $public . '/.htaccess';
if (file_exists($ht)) {
    $content = file_get_contents($ht);
    $hasStorageRule = str_contains($content, 'storage/') && str_contains($content, 'uploads/');
    ok(".htaccess ditemukan");
    if ($hasStorageRule) {
        ok("Berisi storage→uploads rewrite rule");
    } else {
        err("TIDAK berisi storage→uploads rewrite rule — perlu upload ulang!");
    }
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    err(".htaccess TIDAK ADA!");
}
?>
</div>

<!-- 2. CHECK MIDDLEWARE FILE -->
<div class="card">
<h3>2. Middleware FixStoragePaths.php</h3>
<?php
$mw = $root . '/app/Http/Middleware/FixStoragePaths.php';
if (file_exists($mw)) {
    ok("FixStoragePaths.php ditemukan di: " . htmlspecialchars($mw));
} else {
    err("FixStoragePaths.php TIDAK ADA di: " . htmlspecialchars($mw));
}
$boot = $root . '/bootstrap/app.php';
if (file_exists($boot)) {
    $bootContent = file_get_contents($boot);
    if (str_contains($bootContent, 'FixStoragePaths')) {
        ok("bootstrap/app.php sudah mendaftarkan FixStoragePaths");
    } else {
        err("bootstrap/app.php BELUM mendaftarkan FixStoragePaths — perlu upload!");
    }
}
?>
</div>

<!-- 3. CHECK FILE_MANAGER IMAGES -->
<div class="card">
<h3>3. File Images di /uploads/file_manager/</h3>
<?php
$fmDir = $public . '/uploads/file_manager';
if (is_dir($fmDir)) {
    ok("Folder uploads/file_manager/ ADA");
    $files = [];
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fmDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iter as $f) {
        if (in_array(strtolower($f->getExtension()), ['jpg','jpeg','png','webp','gif'])) {
            $files[] = str_replace($public, '', $f->getPathname());
        }
    }
    echo "<p>Jumlah file gambar: <strong>" . count($files) . "</strong></p>";
    if ($files) {
        ok("Ada " . count($files) . " file di file_manager");
        echo "<p>Contoh file:</p><pre>" . htmlspecialchars(implode("\n", array_slice($files, 0, 5))) . "</pre>";
        // Test browser access to first file
        $testUrl = str_replace('\\', '/', $files[0]);
        echo "<p>Test load dari /uploads/:</p>";
        echo "<img src='" . htmlspecialchars($testUrl) . "' style='max-width:100px;border:2px solid green'
              onerror=\"this.nextSibling.innerHTML='<span class=err>❌ Browser tidak bisa akses /uploads/file_manager/ !</span>'\"
              onload=\"this.nextSibling.innerHTML='<span class=ok>✅ Bisa diakses dari /uploads/file_manager/</span>'\">";
        echo "<span></span>";
    } else {
        err("TIDAK ADA file gambar di uploads/file_manager/ — file ada di storage/app/public/ bukan di uploads/!");
    }
} else {
    err("Folder uploads/file_manager/ TIDAK ADA!");
    warn("File mungkin ada di storage/app/public/file_manager/");
    $altDir = $root . '/storage/app/public/file_manager';
    if (is_dir($altDir)) {
        $altFiles = glob($altDir . '/**/*.jpg', GLOB_BRACE);
        warn("Ditemukan di storage/app/public/file_manager: " . (is_dir($altDir) ? 'YA' : 'TIDAK'));
    }
}
?>
</div>

<!-- 4. DIRECT APACHE TEST -->
<div class="card">
<h3>4. Test Apache Rewrite — Akses /storage/ URL</h3>
<?php
// Find an image in uploads to test
$testFile = null;
foreach (['file_manager', 'news', 'officials', 'services'] as $sub) {
    $dir = $public . '/uploads/' . $sub;
    if (is_dir($dir)) {
        $iter2 = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iter2 as $f2) {
            if (in_array(strtolower($f2->getExtension()), ['jpg','jpeg','png','webp'])) {
                $testFile = str_replace($public, '', str_replace('\\', '/', $f2->getPathname()));
                break 2;
            }
        }
    }
}
if ($testFile) {
    $storageUrl = str_replace('/uploads/', '/storage/', $testFile);
    $uploadsUrl = $testFile;
    echo "<p>File test: <code>" . htmlspecialchars($uploadsUrl) . "</code></p>";
    echo "<p>Test /uploads/ path:</p>";
    echo "<img src='" . htmlspecialchars($uploadsUrl) . "' style='max-width:100px'
          onerror=\"this.nextSibling.innerHTML='<span class=err>❌ /uploads/ GAGAL</span>'\"
          onload=\"this.nextSibling.innerHTML='<span class=ok>✅ /uploads/ OK</span>'\">";
    echo "<span></span><br><br>";
    echo "<p>Test /storage/ path (harus 200 jika .htaccess rewrite aktif):</p>";
    echo "<img src='" . htmlspecialchars($storageUrl) . "' style='max-width:100px'
          onerror=\"this.nextSibling.innerHTML='<span class=err>❌ /storage/ GAGAL — rewrite BELUM AKTIF!</span>'\"
          onload=\"this.nextSibling.innerHTML='<span class=ok>✅ /storage/ BERHASIL — rewrite AKTIF!</span>'\">";
    echo "<span></span>";
}
?>
</div>

<div class="card" style="border-left-color:#ef4444">
<strong class="err">⚠️ Hapus diag3.php setelah selesai!</strong>
</div>
</body></html>
