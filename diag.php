<?php
/**
 * DIAGNOSTIK SERVER SUPER LENGKAP - Dukcapil Dompu
 * Akses: https://dukcapil.dompukab.go.id/diag.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$basePath = __DIR__;
if (!file_exists($basePath . '/bootstrap/app.php') && file_exists(dirname(__DIR__) . '/bootstrap/app.php')) {
    $basePath = dirname(__DIR__);
}

$appRoot = $basePath;
$pubRoot = __DIR__;
$storage = $appRoot . '/storage/app/public';
$symlink = $pubRoot . '/storage';
$logFile = $appRoot . '/storage/logs/laravel.log';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Diagnostik Error Server — Dukcapil Dompu</title>
<style>
body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:20px;margin:0}
.card{background:#1e293b;border-radius:12px;padding:20px;margin-bottom:16px;border:1px solid #334155;}
h2{color:#38bdf8;margin:0 0 12px;font-size:1rem}
.row{display:flex;gap:12px;padding:6px 0;border-bottom:1px solid #334155;font-size:0.78rem}
.row:last-child{border:0}
.lbl{color:#94a3b8;min-width:240px}
.val{color:#e2e8f0;word-break:break-all}
.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}
pre{background:#0f172a;padding:12px;border-radius:8px;font-size:0.75rem;color:#fca5a5;overflow-x:auto;max-height:400px;}
a.btn{display:inline-block;padding:8px 14px;background:#0284c7;color:#fff;text-decoration:none;border-radius:6px;font-size:0.8rem;font-weight:bold;margin-right:8px;}
</style>
</head>
<body>

<div class="card">
    <h2>🔥 0. TEST ROUTE HANDLER (MENGAPA INDEX ERROR 500?)</h2>
    <?php
    try {
        if (!file_exists($appRoot . '/vendor/autoload.php')) {
            echo "<div class='row'><span class='err'>❌ Folder vendor/autoload.php TIDAK ADA!</span></div>";
        } else {
            require_once $appRoot . '/vendor/autoload.php';
            $app = require_once $appRoot . '/bootstrap/app.php';
            
            $req = \Illuminate\Http\Request::create('/', 'GET');
            $res = $app->handleRequest($req);
            
            echo "<div class='row'><span class='ok'>✅ TEST ROUTE GET / SUKSES! Status Code: " . $res->getStatusCode() . "</span></div>";
        }
    } catch (\Throwable $t) {
        echo "<div class='row'><span class='err'>❌ PESAN ERROR PENYEBAB 500: " . htmlspecialchars($t->getMessage()) . "</span></div>";
        echo "<pre>Lokasi Error: " . $t->getFile() . ":" . $t->getLine() . "\n\nStack Trace:\n" . htmlspecialchars($t->getTraceAsString()) . "</pre>";
    }
    ?>
</div>

<div class="card">
    <h2>📋 1. LOG TERAKHIR SERVER (storage/logs/laravel.log)</h2>
    <?php
    if (file_exists($logFile) && filesize($logFile) > 0) {
        $lines = file($logFile);
        $lastLines = array_slice($lines, -40);
        echo "<pre>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
    } else {
        echo "<div class='row'><span class='warn'>ℹ️ File log belum dibuat (storage/logs/laravel.log).</span></div>";
    }
    ?>
</div>

<div class="card">
    <h2>📁 2. IZIN FOLDER (PERMISSIONS)</h2>
    <?php
    $checkDirs = [
        'storage' => $appRoot . '/storage',
        'storage/framework/views' => $appRoot . '/storage/framework/views',
        'storage/logs' => $appRoot . '/storage/logs',
        'bootstrap/cache' => $appRoot . '/bootstrap/cache',
    ];
    foreach ($checkDirs as $name => $path) {
        $writable = is_writable($path);
        $status = $writable ? '<span class="ok">✅ Writable</span>' : '<span class="err">❌ Unwritable (Ubah Izin ke 0777)</span>';
        echo "<div class='row'><span class='lbl'>$name</span><span class='val'>$path — $status</span></div>";
    }
    ?>
</div>

<div class="card" style="margin-top:20px;">
    <a href="/fix_env.php" class="btn">⚡ Perbaiki .env</a>
    <a href="/fix_permissions.php" class="btn" style="background:#059669;">🛠️ Fix Permissions (0777)</a>
    <a href="/" class="btn">🌐 Website Utama</a>
</div>

</body>
</html>
