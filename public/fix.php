<?php
/**
 * DUKCAPIL DOMPU - MASTER FIX SCRIPT
 * ====================================
 * Script ini memperbaiki SEMUA masalah sekaligus:
 * 1. Database: Update path gambar banner yang salah
 * 2. Database: Set leader_photo yang benar
 * 3. Storage: Pastikan semua file ada di tempat yang benar
 * 4. Cache: Bersihkan semua cache Laravel
 *
 * Akses: https://dukcapil.dompukab.go.id/fix.php
 * HAPUS setelah selesai!
 */

set_time_limit(300);
$appRoot = dirname(__DIR__);
$pubRoot = __DIR__;
$storSrc = $appRoot . '/storage/app/public';  // sumber asli
$storDst = $pubRoot . '/storage';             // tujuan (real folder atau symlink)

$log = [];
function ok($msg)  { global $log; $log[] = ['ok' => true,  'msg' => $msg]; }
function err($msg) { global $log; $log[] = ['ok' => false, 'msg' => $msg]; }
function info($msg){ global $log; $log[] = ['ok' => null,  'msg' => $msg]; }

// ============================================================
// LANGKAH 1: Sambungkan ke Database
// ============================================================
info('=== LANGKAH 1: Koneksi Database ===');
$env = [];
foreach (file($appRoot . '/.env') as $line) {
    $line = trim($line);
    if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, '"\'');
    }
}
$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
        $env['DB_USERNAME'], $env['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    ok("Terhubung ke database: {$env['DB_DATABASE']}");
} catch (Exception $e) {
    err("Gagal koneksi DB: " . $e->getMessage());
}

// ============================================================
// LANGKAH 2: Fix Direktori Storage
// ============================================================
info('=== LANGKAH 2: Menyiapkan Direktori Storage ===');

// Jika public/storage adalah symlink, hapus dan buat folder asli
if (is_link($storDst)) {
    $target = readlink($storDst);
    // Copy file dari symlink target ke tempat baru
    @unlink($storDst);
    if (!is_link($storDst)) {
        ok("Symlink lama dihapus: $storDst → $target");
        mkdir($storDst, 0775, true);
        ok("Folder nyata dibuat: $storDst");
    } else {
        err("Gagal hapus symlink - akan tetap menggunakan symlink");
    }
} elseif (!is_dir($storDst)) {
    mkdir($storDst, 0775, true);
    ok("Folder storage dibuat: $storDst");
} else {
    ok("Folder public/storage sudah ada sebagai folder nyata");
}

// Subfolder yang diperlukan
$subfolders = ['officials','banners','branding','news','leader','gallery',
               'downloads','file_manager','ppid-documents','ppid-responses',
               'complaints','demographics','gallery/covers','gallery/1',
               'news/2026/07','news/inline/2026/07'];
foreach ($subfolders as $sub) {
    $p = $storDst . '/' . $sub;
    if (!is_dir($p)) { mkdir($p, 0775, true); }
}
ok("Semua subfolder storage siap");

// Copy semua file dari storage/app/public ke public/storage
function copyDir($src, $dst) {
    if (!is_dir($src)) return [0,0];
    if (!is_dir($dst)) mkdir($dst, 0775, true);
    $c = $f = 0;
    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    ) as $item) {
        $t = $dst . str_replace($src, '', $item->getRealPath());
        if ($item->isDir()) { @mkdir($t, 0775, true); }
        elseif (!file_exists($t)) { @copy($item->getRealPath(), $t) ? $c++ : $f++; }
        else $c++;
    }
    return [$c, $f];
}

[$c, $f] = copyDir($storSrc, $storDst);
ok("File media disalin: $c file berhasil" . ($f ? ", $f gagal" : ""));

// ============================================================
// LANGKAH 3: Audit dan Fix Database Banners
// ============================================================
info('=== LANGKAH 3: Fix Database Banners ===');

if ($pdo) {
    $banners = $pdo->query("SELECT id, title, image FROM banners WHERE deleted_at IS NULL ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
    
    // Daftar file banner yang BENAR-BENAR ada di storage
    $realBannerFiles = array_filter(
        scandir($storDst . '/banners') ?: [],
        fn($f) => $f !== '.' && $f !== '..' && !str_starts_with($f, 'mobile-')
    );
    sort($realBannerFiles);

    $bannerIdx = 0;
    foreach ($banners as $b) {
        $currentImage = $b['image'];
        $currentPath = $storDst . '/' . $currentImage;
        
        if ($currentImage && file_exists($currentPath)) {
            ok("Banner #{$b['id']} '{$b['title']}': ✅ File OK → $currentImage");
        } else {
            // File tidak ada, ambil file banner berikutnya yang tersedia
            $availFiles = array_values($realBannerFiles);
            if (isset($availFiles[$bannerIdx % count($availFiles)])) {
                $newFile = 'banners/' . $availFiles[$bannerIdx % count($availFiles)];
                $pdo->prepare("UPDATE banners SET image = ?, updated_at = NOW() WHERE id = ?")->execute([$newFile, $b['id']]);
                ok("Banner #{$b['id']} '{$b['title']}': DIPERBAIKI → $currentImage ⟹ $newFile");
            } else {
                err("Banner #{$b['id']}': Tidak ada file pengganti tersedia!");
            }
            $bannerIdx++;
        }
    }

    if (empty($banners)) {
        // Tidak ada banner sama sekali - buat dari file yang ada
        $availFiles = array_values($realBannerFiles);
        $titles = ['Layanan Kependudukan Dompu', 'SANAI Online', 'Pelayanan Mobile Keliling'];
        foreach (array_slice($availFiles, 0, 3) as $i => $fn) {
            $pdo->prepare("INSERT INTO banners (title, subtitle, image, type, is_active, sort_order, created_at, updated_at) VALUES (?, ?, ?, 'hero', 1, ?, NOW(), NOW())")
                ->execute([$titles[$i] ?? "Banner $i", 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu', "banners/$fn", $i+1]);
            ok("Banner baru dibuat: banners/$fn");
        }
    }
}

// ============================================================
// LANGKAH 4: Fix leader_photo di WebsiteSettings
// ============================================================
info('=== LANGKAH 4: Fix Foto Kepala Dinas ===');

if ($pdo) {
    $leaderSetting = $pdo->query("SELECT * FROM website_settings WHERE `key` = 'leader_photo' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $leaderFiles = array_filter(scandir($storDst . '/leader') ?: [], fn($f)=>$f!='.'&&$f!='..');
    
    if ($leaderSetting) {
        $currentVal = $leaderSetting['value'];
        $needsFix = false;
        
        if ($currentVal === '/images/kepala_dinas.jpg' || !$currentVal) {
            $needsFix = true;
        } elseif (str_starts_with($currentVal, '/storage/')) {
            $filePath = $pubRoot . $currentVal;
            if (!file_exists($filePath)) $needsFix = true;
        }
        
        if ($needsFix && count($leaderFiles) > 0) {
            $leaderFile = array_values($leaderFiles)[0];
            $newPath = '/storage/leader/' . $leaderFile;
            $pdo->prepare("UPDATE website_settings SET value = ?, updated_at = NOW() WHERE `key` = 'leader_photo'")->execute([$newPath]);
            ok("leader_photo diperbaiki: $currentVal ⟹ $newPath");
        } elseif ($needsFix) {
            err("leader_photo: masih menggunakan default - tidak ada file di storage/leader/");
        } else {
            ok("leader_photo: sudah benar → $currentVal");
        }
    } else {
        if (count($leaderFiles) > 0) {
            $leaderFile = array_values($leaderFiles)[0];
            $newPath = '/storage/leader/' . $leaderFile;
            $pdo->prepare("INSERT INTO website_settings (`key`, value, `group`, created_at, updated_at) VALUES ('leader_photo', ?, 'pimpinan', NOW(), NOW())")->execute([$newPath]);
            ok("leader_photo dibuat baru: $newPath");
        }
    }
}

// ============================================================
// LANGKAH 5: Verifikasi Officials (tampilkan path yang salah)
// ============================================================
info('=== LANGKAH 5: Verifikasi Officials ===');
if ($pdo) {
    $offs = $pdo->query("SELECT id, name, photo FROM officials WHERE deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($offs as $o) {
        if (!$o['photo']) {
            info("Official #{$o['id']} {$o['name']}: photo NULL (belum diupload)");
            continue;
        }
        $cleanPath = ltrim($o['photo'], '/');
        if (str_starts_with($cleanPath, 'storage/')) $cleanPath = substr($cleanPath, 8);
        $filePath = $storDst . '/' . $cleanPath;
        $altPath  = $storSrc . '/' . $cleanPath;
        if (file_exists($filePath) || file_exists($altPath)) {
            ok("Official #{$o['id']} {$o['name']}: ✅ {$o['photo']}");
        } else {
            err("Official #{$o['id']} {$o['name']}: ❌ file tidak ada → {$o['photo']}");
        }
    }
}

// ============================================================
// LANGKAH 6: Bersihkan Cache Laravel
// ============================================================
info('=== LANGKAH 6: Bersihkan Cache ===');
$cacheDirs = [
    $appRoot . '/storage/framework/views',
    $appRoot . '/storage/framework/cache/data',
    $appRoot . '/bootstrap/cache',
];
foreach ($cacheDirs as $cd) {
    if (is_dir($cd)) {
        $cnt = 0;
        foreach (glob($cd . '/*') as $f) {
            if (is_file($f) && basename($f) !== '.gitignore') { @unlink($f); $cnt++; }
        }
        ok("Cache dibersihkan: $cd ($cnt file)");
    }
}

// ============================================================
// OUTPUT HTML
// ============================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><title>Master Fix - Dukcapil Dompu</title>
<style>
*{box-sizing:border-box}body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;margin:0;padding:24px}
.card{max-width:900px;margin:0 auto;background:#1e293b;border-radius:16px;padding:28px}
h1{color:#38bdf8;margin:0 0 4px;font-size:1.3rem}p.sub{color:#64748b;font-size:.8rem;margin:0 0 20px}
.step{display:flex;gap:10px;padding:7px 4px;border-bottom:1px solid #1e3a5f22;font-size:.78rem;align-items:flex-start}
.ico{min-width:20px;margin-top:1px}.ok{color:#4ade80}.err{color:#f87171}.info{color:#38bdf8;font-weight:600;margin-top:4px}
.summary{margin-top:20px;padding:16px;border-radius:10px;font-size:.9rem;font-weight:600;text-align:center}
.s-ok{background:#052e16;border:1px solid #166534;color:#4ade80}
.s-warn{background:#1c1200;border:1px solid #854d0e;color:#fbbf24}
.warn-box{margin-top:16px;background:#1c1200;border:1px solid #92400e;border-radius:8px;padding:12px;font-size:.78rem;color:#fbbf24}
</style>
</head>
<body><div class="card">
<h1>🛠️ Master Fix - Dukcapil Dompu</h1>
<p class="sub">Memperbaiki database, storage, dan cache secara otomatis.</p>
<?php
$hasErr = false;
foreach ($log as $item) {
    if ($item['ok'] === false) $hasErr = true;
    if ($item['ok'] === null) echo "<div class='step info'><span class='ico'>📋</span><span class='info'>" . htmlspecialchars($item['msg']) . "</span></div>";
    else echo "<div class='step'><span class='ico'>".($item['ok']?'✅':'⚠️')."</span><span class='".($item['ok']?'ok':'err')."'>".htmlspecialchars($item['msg'])."</span></div>";
}
echo "<div class='summary ".(!$hasErr?'s-ok':'s-warn')."'>".(!$hasErr?'🎉 Semua berhasil diperbaiki! Silakan refresh halaman website.':'⚠️ Ada beberapa item perlu perhatian — lihat baris merah di atas.')."</div>";
?>
<div class="warn-box">⚠️ <strong>HAPUS</strong> file <code>fix.php</code> dan <code>diag.php</code> dan <code>setup.php</code> dari <code>httpdocs/public/</code> setelah selesai!</div>
</div></body></html>
