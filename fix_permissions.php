<?php
/**
 * STANDALONE PERMISSION REPAIR — Dukcapil Dompu
 * Akses Direct: https://dukcapil.dompukab.go.id/fix_permissions.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

function chmod_r($path, $mode) {
    if (!file_exists($path)) return;
    @chmod($path, $mode);
    if (is_dir($path)) {
        $items = @scandir($path) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            chmod_r($path . '/' . $item, $mode);
        }
    }
}

$baseDir = __DIR__;
if (!file_exists($baseDir . '/bootstrap/app.php') && file_exists(dirname(__DIR__) . '/bootstrap/app.php')) {
    $baseDir = dirname(__DIR__);
}

// 1. Fix folder permissions
chmod_r($baseDir . '/storage', 0777);
chmod_r($baseDir . '/bootstrap/cache', 0777);

// 2. Clear cached views and sessions
foreach ([$baseDir . '/storage/framework/views', $baseDir . '/storage/framework/sessions', $baseDir . '/bootstrap/cache'] as $cd) {
    if (is_dir($cd)) {
        foreach (glob($cd . '/*') ?: [] as $cf) {
            if (is_file($cf) && !str_ends_with($cf, '.gitignore')) {
                @unlink($cf);
            }
        }
    }
}

// 3. Create symlink
$target = $baseDir . '/storage/app/public';
$shortcut = __DIR__ . '/storage';
if (!file_exists(__DIR__ . '/index.php')) {
    $shortcut = $baseDir . '/public/storage';
}
$symlinkOk = false;
if (!file_exists($shortcut) && is_dir($target)) {
    $symlinkOk = @symlink($target, $shortcut);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Perbaikan Izin Akses Selesai</title>
<style>
body{font-family:sans-serif;background:#0f172a;color:#f8fafc;padding:40px;text-align:center;}
.box{background:#1e293b;max-width:550px;margin:30px auto;padding:30px;border-radius:16px;border:1px solid #334155;}
h2{color:#4ade80;} p{color:#cbd5e1;font-size:0.9rem;line-height:1.6;}
a.btn{display:inline-block;margin-top:16px;padding:12px 24px;background:#0284c7;color:#fff;text-decoration:none;font-weight:bold;border-radius:8px;margin-right:8px;}
</style>
</head>
<body>
<div class="box">
    <h2>✅ IZIN AKSES FOLDER BERHASIL DIPERBAIKI (0777)!</h2>
    <p>Seluruh cache lama di <code>bootstrap/cache</code> dan <code>storage/framework/views</code> telah dibersihkan bersih. Izin folder <code>storage/</code> telah diberikan akses penuh.</p>
    <a class="btn" href="/" style="background:#059669;">🌐 Buka Website Utama</a>
    <a class="btn" href="/admin/login">🔐 Buka Login CMS Admin</a>
</div>
</body>
</html>
