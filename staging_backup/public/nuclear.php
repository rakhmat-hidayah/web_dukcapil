<?php
/**
 * DUKCAPIL DOMPU - SAFE NUCLEAR FIX v3
 * =====================================
 * Akses: https://dukcapil.dompukab.go.id/nuclear.php
 * HAPUS SETELAH SELESAI!
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(120);
ini_set('memory_limit', '256M');

$appRoot = dirname(__DIR__);
$pubRoot = __DIR__;

$log = [];
function L($msg, $ok = true) { global $log; $log[] = ['ok' => $ok, 'msg' => $msg]; }
function section($title) { global $log; $log[] = ['section' => $title]; }

// Safe non-recursive copy for flat directories or controlled subfolders
function safeCopyDir($src, $dst) {
    if (!is_dir($src)) return [0, 0];
    if (!is_dir($dst)) @mkdir($dst, 0775, true);
    
    // Normalize real paths to prevent recursion
    $realSrc = realpath($src);
    $realDst = realpath($dst);
    if ($realSrc && $realDst && str_starts_with($realDst, $realSrc)) {
        return [0, 0]; // Prevent copying parent into child!
    }

    $ok = $fail = 0;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iter = $items as $item) {
        $itemPath = $item->getRealPath();
        // Skip if destination is inside item path
        if ($realDst && str_starts_with($realDst, $itemPath)) continue;

        $target = $dst . str_replace($src, '', $itemPath);
        if ($item->isDir()) {
            @mkdir($target, 0775, true);
        } else {
            @copy($itemPath, $target) ? $ok++ : $fail++;
        }
    }
    return [$ok, $fail];
}

// ===== 1. CLEAR OPCACHE =====
section('1. PHP ENVIRONMENT');
if (function_exists('opcache_reset')) {
    @opcache_reset();
    L('OPcache reset ✓');
} else {
    L('OPcache OK');
}

// ===== 2. KONEKSI DATABASE =====
section('2. KONEKSI DATABASE');
$env = [];
if (file_exists($appRoot . '/.env')) {
    foreach (file($appRoot . '/.env') as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v, '"\'');
        }
    }
}
$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
        $env['DB_USERNAME'], $env['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    L("Koneksi DB Sukses: {$env['DB_DATABASE']} ✓");
} catch (Throwable $e) {
    L("DB Warning: " . $e->getMessage(), false);
}

// ===== 3. DIREKTORI STORAGE WEB =====
section('3. SYNC MEDIA FILE SYSTEM');

// Target folder yang harus berisi media:
$storageAppPublic = $appRoot . '/storage/app/public';
$publicStorage   = $pubRoot . '/storage';
$appPublicStorage= $appRoot . '/public/storage';

$subDirs = [
    'officials', 'banners', 'branding', 'news', 'leader', 'gallery',
    'gallery/covers', 'gallery/1', 'downloads', 'downloads/forms',
    'file_manager', 'ppid-documents', 'ppid-responses', 'complaints', 'demographics'
];

foreach ([$storageAppPublic, $publicStorage, $appPublicStorage] as $targetDir) {
    if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
    foreach ($subDirs as $sd) {
        $p = $targetDir . '/' . $sd;
        if (!is_dir($p)) @mkdir($p, 0775, true);
    }
}

// Sync storage/app/public -> public/storage
[$c1, $f1] = safeCopyDir($storageAppPublic, $publicStorage);
L("Disalin $c1 file dari <code>storage/app/public</code> ➔ <code>public/storage</code> ✓");

if ($publicStorage !== $appPublicStorage) {
    [$c2, $f2] = safeCopyDir($storageAppPublic, $appPublicStorage);
    L("Disalin $c2 file dari <code>storage/app/public</code> ➔ <code>app/public/storage</code> ✓");
}

// ===== 4. AMBIL FILE NYASAR (Sepert httpdocs/gallery, httpdocs/officials, dll) =====
section('4. AMBIL FOLDER MEDIA TERPISAH');

$mediaTypes = ['gallery', 'officials', 'banners', 'leader', 'news', 'downloads', 'branding', 'complaints'];
$possibleRootDirs = array_unique([$appRoot, $pubRoot, dirname($pubRoot)]);

foreach ($possibleRootDirs as $rd) {
    foreach ($mediaTypes as $mt) {
        $misplaced = $rd . '/' . $mt;
        // Jika ada folder seperti httpdocs/gallery yang BUKAN di dalam storage
        if (is_dir($misplaced) && !str_contains(realpath($misplaced) ?: '', 'storage')) {
            L("Ditemukan folder media di luar storage: <code>$misplaced</code>");
            // Salin ke public/storage/gallery dll
            [$sc, $sf] = safeCopyDir($misplaced, $publicStorage . '/' . $mt);
            [$sc2, $sf2] = safeCopyDir($misplaced, $storageAppPublic . '/' . $mt);
            L("Berhasil memindahkan $sc file dari <code>$misplaced</code> ➔ <code>storage/$mt</code> ✓");
        }
    }
}

// ===== 5. FIX DATABASE RECORD LEADER & BANNERS =====
section('5. UPDATE DATABASE RECORDS');
if ($pdo) {
    // Leader Photo Fix
    $row = $pdo->query("SELECT value FROM website_settings WHERE `key` = 'leader_photo' LIMIT 1")->fetch();
    $currentLeader = $row ? $row['value'] : null;

    $leaderFiles = [];
    foreach ([$publicStorage . '/leader', $storageAppPublic . '/leader'] as $ldDir) {
        if (is_dir($ldDir)) {
            foreach (scandir($ldDir) as $f) {
                if ($f !== '.' && $f !== '..') $leaderFiles[] = $f;
            }
        }
    }

    if (count($leaderFiles) > 0) {
        $newLeader = '/storage/leader/' . $leaderFiles[0];
        $pdo->prepare("UPDATE website_settings SET value = ?, updated_at = NOW() WHERE `key` = 'leader_photo'")->execute([$newLeader]);
        L("leader_photo diperbarui di DB: <code>$newLeader</code> ✓");
    } else {
        L("leader_photo: " . ($currentLeader ?: 'default'));
    }

    // Banners Audit
    $banners = $pdo->query("SELECT id, title, image FROM banners WHERE deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($banners as $b) {
        if ($b['image']) {
            $chk = $publicStorage . '/' . $b['image'];
            if (file_exists($chk)) {
                L("Banner #{$b['id']} '{$b['title']}': File terverifikasi ✓");
            } else {
                L("Banner #{$b['id']} '{$b['title']}': File <code>{$b['image']}</code> belum ada di storage", false);
            }
        }
    }
}

// ===== 6. CLEAR CACHE =====
section('6. HAPUS CACHE LARAVEL');
foreach ([$appRoot . '/storage/framework/views', $appRoot . '/storage/framework/cache/data', $appRoot . '/bootstrap/cache'] as $cp) {
    if (is_dir($cp)) {
        $cnt = 0;
        foreach (glob($cp . '/*') as $f) {
            if (is_file($f) && basename($f) !== '.gitignore') { @unlink($f); $cnt++; }
        }
        L("Cache dibersihkan: " . basename($cp) . " ($cnt file)");
    }
}
@unlink($appRoot . '/bootstrap/cache/config.php');
@unlink($appRoot . '/bootstrap/cache/routes-v7.php');

// ===== OUTPUT =====
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Nuclear Fix v3</title>
<style>
body{font-family:system-ui,sans-serif;background:#0a0f1e;color:#e2e8f0;margin:0;padding:20px}
.card{max-width:900px;margin:0 auto;background:#111827;border-radius:16px;padding:24px}
h1{color:#f59e0b;margin:0 0 16px;font-size:1.2rem}
.sec{color:#38bdf8;font-weight:700;font-size:.75rem;text-transform:uppercase;padding:12px 0 4px;border-top:1px solid #1e3a5f;margin-top:8px}
.row{display:flex;gap:8px;padding:5px 2px;font-size:.77rem;border-bottom:1px solid #ffffff08;align-items:flex-start}
.ico{min-width:18px}.ok{color:#4ade80}.err{color:#f87171}
.sum{margin-top:16px;padding:14px;border-radius:10px;text-align:center;font-weight:700;font-size:.9rem;background:#052e16;border:1px solid #166534;color:#4ade80}
code{background:#1e293b;padding:2px 6px;border-radius:4px;color:#38bdf8}
</style></head><body>
<div class="card">
<h1>💥 Safe Nuclear Fix v3</h1>
<?php
foreach ($log as $item) {
    if (isset($item['section'])) {
        echo "<div class='sec'>{$item['section']}</div>";
        continue;
    }
    $cls = $item['ok'] ? 'ok' : 'err';
    $ico = $item['ok'] ? '✅' : '⚠️';
    echo "<div class='row'><span class='ico'>$ico</span><span class='$cls'>{$item['msg']}</span></div>";
}
echo "<div class='sum'>🎉 Selesai! Semua media disinkronkan & cache dibersihkan. Refresh halaman web Anda!</div>";
?>
</div></body></html>
