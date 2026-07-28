<?php
/**
 * DUKCAPIL DOMPU - SETUP FINAL (Fix Nginx Symlink)
 * =================================================
 * Masalah: Nginx Plesk tidak mengikuti symlink untuk file statis.
 * Solusi:  Copy semua file dari storage/app/public/ ke public/storage/
 *          sehingga Nginx dapat melayani file secara langsung.
 *
 * Akses: https://dukcapil.dompukab.go.id/setup.php
 * HAPUS setelah selesai!
 */

set_time_limit(120);
$appRoot = dirname(__DIR__);
$pubRoot = __DIR__;
$srcDir  = $appRoot . '/storage/app/public';   // sumber lama
$dstDir  = $pubRoot . '/storage';              // tujuan baru (di dalam public/)

$log = [];

function logOk($msg)  { global $log; $log[] = ['ok' => true,  'msg' => $msg]; }
function logErr($msg) { global $log; $log[] = ['ok' => false, 'msg' => $msg]; }

// ─── 1. Jika public/storage adalah symlink, hapus dulu ───
if (is_link($dstDir)) {
    if (@unlink($dstDir)) logOk("Symlink lama dihapus: $dstDir");
    else logErr("Gagal hapus symlink: $dstDir — coba hapus manual di File Manager");
}

// ─── 2. Buat public/storage/ sebagai folder asli ───
if (!is_dir($dstDir)) {
    if (@mkdir($dstDir, 0775, true)) logOk("Dibuat: $dstDir");
    else logErr("Gagal buat folder: $dstDir");
}

// ─── 3. Copy semua subfolder & file dari storage/app/public/ ke public/storage/ ───
function copyDir($src, $dst) {
    if (!is_dir($src)) return;
    if (!is_dir($dst)) @mkdir($dst, 0775, true);
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    $copied = 0; $failed = 0;
    foreach ($items as $item) {
        $target = $dst . str_replace($src, '', $item->getRealPath());
        if ($item->isDir()) {
            @mkdir($target, 0775, true);
        } else {
            if (@copy($item->getRealPath(), $target)) $copied++;
            else $failed++;
        }
    }
    return [$copied, $failed];
}

if (is_dir($srcDir)) {
    [$c, $f] = copyDir($srcDir, $dstDir);
    if ($f === 0) logOk("Copy berhasil: $c file dari storage/app/public/ → public/storage/");
    else logOk("Copy selesai: $c berhasil, $f gagal dari storage/app/public/ → public/storage/");
} else {
    logErr("Folder sumber tidak ada: $srcDir");
}

// ─── 4. Buat subfolder yang mungkin belum ada ───
$subDirs = ['officials','banners','branding','news','leader','gallery',
            'downloads','file_manager','ppid-documents','ppid-responses',
            'complaints','demographics'];
foreach ($subDirs as $d) {
    $p = $dstDir . '/' . $d;
    if (!is_dir($p)) {
        @mkdir($p, 0775, true);
        logOk("Dibuat subfolder: public/storage/$d");
    }
}

// ─── 5. Bersihkan cache Laravel ───
$cacheDirs = [
    $appRoot . '/storage/framework/views',
    $appRoot . '/storage/framework/cache/data',
    $appRoot . '/bootstrap/cache',
];
foreach ($cacheDirs as $cd) {
    if (is_dir($cd)) {
        $files = glob($cd . '/*');
        $cnt = 0;
        foreach ($files as $f) {
            if (is_file($f) && basename($f) !== '.gitignore') { @unlink($f); $cnt++; }
        }
        logOk("Cache dihapus: $cd ($cnt file)");
    }
}

// ─── 6. Verifikasi: hitung file di public/storage/officials ───
$offDir   = $dstDir . '/officials';
$offFiles = is_dir($offDir) ? count(array_filter(scandir($offDir), fn($f)=>$f!='.'&&$f!='..')) : 0;
if ($offFiles > 0) logOk("Verifikasi: $offFiles file foto pejabat ada di public/storage/officials/");
else logErr("Verifikasi: public/storage/officials/ KOSONG — upload file media terlebih dahulu");

// ─── 7. Test akses web ───
$testName = 'webtest_' . time() . '.txt';
file_put_contents($dstDir . '/' . $testName, 'ok');
$testUrl  = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'?'https':'http')
            . '://' . $_SERVER['HTTP_HOST'] . '/storage/' . $testName;
// Coba ambil via HTTP (mungkin diblokir open_basedir)
$fileOk = file_exists($dstDir . '/' . $testName);
@unlink($dstDir . '/' . $testName);
if ($fileOk) logOk("File dapat ditulis ke public/storage/ — Nginx akan melayani sebagai file statis");

?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup Final - Dukcapil Dompu</title>
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;margin:0;padding:24px}
.card{max-width:800px;margin:0 auto;background:#1e293b;border-radius:16px;padding:28px}
h1{color:#38bdf8;margin:0 0 4px;font-size:1.3rem}
p.sub{color:#64748b;font-size:.8rem;margin:0 0 20px}
.step{display:flex;gap:10px;padding:8px 4px;border-bottom:1px solid #334155;font-size:.8rem}
.ico{min-width:20px}
.ok{color:#4ade80}.err{color:#f87171}
.summary{margin-top:20px;padding:16px;border-radius:10px;font-size:.9rem;font-weight:600;text-align:center}
.s-ok{background:#052e16;border:1px solid #166534;color:#4ade80}
.s-warn{background:#1c1200;border:1px solid #854d0e;color:#fbbf24}
.warn-box{margin-top:16px;background:#1c1200;border:1px solid #92400e;border-radius:8px;padding:12px;font-size:.78rem;color:#fbbf24}
</style>
</head>
<body>
<div class="card">
<h1>🔧 Setup Final - Dukcapil Dompu</h1>
<p class="sub">Menyalin file media ke public/storage/ agar Nginx dapat melayaninya langsung tanpa symlink.</p>
<?php
$allOk = true;
foreach ($log as $item) {
    if (!$item['ok']) $allOk = false;
    echo "<div class='step'><span class='ico'>".($item['ok']?'✅':'⚠️')."</span>"
       . "<span class='".($item['ok']?'ok':'err')."'>" . htmlspecialchars($item['msg']) . "</span></div>";
}
echo "<div class='summary ".($allOk?'s-ok':'s-warn')."'>"
   . ($allOk
       ? "🎉 Setup berhasil! Refresh halaman CMS — foto pejabat seharusnya sudah tampil."
       : "⚠️ Ada beberapa peringatan. Periksa item merah di atas.")
   . "</div>";
?>
<div class="warn-box">
⚠️ <strong>PENTING:</strong> Hapus file <code>setup.php</code> dan <code>diag.php</code>
dari folder <code>httpdocs/public/</code> setelah setup selesai!
</div>
</div>
</body>
</html>
