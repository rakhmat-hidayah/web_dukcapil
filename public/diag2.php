<?php
/**
 * Server Diagnostic Script
 * Upload to: httpdocs/public/diag2.php
 * Run: https://dukcapil.dompukab.go.id/diag2.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') { die('403'); }
header('Content-Type: text/html; charset=utf-8');

$public = __DIR__;
$storage = $public . '/storage';
$uploads = $public . '/uploads';

// Find a real image to test
$testImgUrl = null;
$testImgPath = null;
if (is_dir($uploads)) {
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploads, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($iter as $f) {
        if (in_array(strtolower($f->getExtension()), ['jpg','jpeg','png','webp'])) {
            $testImgPath = $f->getPathname();
            $testImgUrl  = '/uploads/' . str_replace($uploads . DIRECTORY_SEPARATOR, '', $f->getPathname());
            $testImgUrl  = str_replace('\\', '/', $testImgUrl);
            break;
        }
    }
}

// Detect web server
$server = $_SERVER['SERVER_SOFTWARE'] ?? 'unknown';
$isNginx = stripos($server, 'nginx') !== false;
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Server Diag</title>
<style>body{font-family:sans-serif;padding:20px;background:#f5f5f5} .card{background:white;padding:20px;border-radius:10px;margin:10px 0;box-shadow:0 2px 8px rgba(0,0,0,.1)} .ok{color:green;font-weight:700} .err{color:red;font-weight:700} .warn{color:orange;font-weight:700} code{background:#f0f0f0;padding:2px 6px;border-radius:4px} table{width:100%;border-collapse:collapse} td,th{border:1px solid #ddd;padding:8px;font-size:13px} th{background:#1d4ed8;color:white}</style>
</head><body>
<h2>🔍 Server Diagnostic — Dukcapil Dompu</h2>

<div class="card">
<h3>🖥️ Web Server</h3>
<table>
<tr><td>Server Software</td><td><code><?= htmlspecialchars($server) ?></code></td><td><?= $isNginx ? '<span class="warn">⚠️ Nginx (bypasses .htaccess for static files!)</span>' : '<span class="ok">✅ Apache</span>' ?></td></tr>
<tr><td>PHP Version</td><td colspan="2"><code><?= PHP_VERSION ?></code></td></tr>
<tr><td>Document Root</td><td colspan="2"><code><?= htmlspecialchars($public) ?></code></td></tr>
</table>
</div>

<div class="card">
<h3>📁 Folder Status</h3>
<table>
<tr><th>Path</th><th>Type</th><th>Status</th></tr>
<tr>
    <td><code>public/uploads/</code></td>
    <td><?= is_dir($uploads) ? 'Directory' : 'NOT FOUND' ?></td>
    <td><?= is_dir($uploads) ? '<span class="ok">✅ Exists</span>' : '<span class="err">❌ Missing!</span>' ?></td>
</tr>
<tr>
    <td><code>public/storage/</code></td>
    <td><?= is_link($storage) ? 'Symlink → ' . readlink($storage) : (is_dir($storage) ? 'Physical Directory' : 'NOT FOUND') ?></td>
    <td>
    <?php
    if (is_link($storage)) {
        $target = readlink($storage);
        if (strpos($target, 'uploads') !== false) {
            echo '<span class="ok">✅ Symlink → uploads (CORRECT!)</span>';
        } else {
            echo '<span class="warn">⚠️ Symlink → ' . htmlspecialchars($target) . ' (wrong target)</span>';
        }
    } elseif (is_dir($storage)) {
        echo '<span class="err">❌ Physical folder EXISTS — this is causing Nginx/Apache 403!</span>';
    } else {
        echo '<span class="warn">⚠️ Does not exist (OK if using .htaccess rewrite)</span>';
    }
    ?>
    </td>
</tr>
</table>
</div>

<?php if ($testImgPath): ?>
<div class="card">
<h3>🖼️ Image Test</h3>
<p>Test image found: <code><?= htmlspecialchars($testImgUrl) ?></code></p>
<p>File exists via PHP: <strong class="ok">YES ✅</strong></p>
<p>File size: <?= number_format(filesize($testImgPath)) ?> bytes</p>
<p>Direct URL test:</p>
<img src="<?= htmlspecialchars($testImgUrl) ?>" 
     style="max-width:200px;border:2px solid green;"
     onerror="this.style.border='2px solid red';this.nextElementSibling.innerHTML='<span class=err>❌ FAILED to load via browser — Server is blocking /uploads/ too!</span>'"
     onload="this.nextElementSibling.innerHTML='<span class=ok>✅ Image loads from /uploads/ — browser access OK!</span>'">
<p id="imgStatus"><em>Testing...</em></p>

<?php
// Also test /storage/ path
$storagePath = str_replace('/uploads/', '/storage/', $testImgUrl);
?>
<p>Storage URL (should redirect or be blocked):</p>
<code><?= htmlspecialchars($storagePath) ?></code><br>
<img src="<?= htmlspecialchars($storagePath) ?>"
     style="max-width:200px;border:2px solid orange;margin-top:5px"
     onerror="this.nextElementSibling.innerHTML='<span class=err>❌ /storage/ returns error (expected if symlink not created)</span>'"
     onload="this.nextElementSibling.innerHTML='<span class=ok>✅ /storage/ works! Symlink or rewrite is active.</span>'">
<p><em>Testing storage path...</em></p>
</div>
<?php endif; ?>

<div class="card">
<h3>🔧 .htaccess Status</h3>
<?php
$htaccess = $public . '/.htaccess';
if (file_exists($htaccess)) {
    $content = file_get_contents($htaccess);
    $hasStorageRule = strpos($content, 'storage') !== false;
    echo '<p>File exists: <span class="ok">✅</span></p>';
    echo '<p>Contains storage rewrite: <span class="' . ($hasStorageRule ? 'ok' : 'err') . '">' . ($hasStorageRule ? '✅ YES' : '❌ NO — needs to be added!') . '</span></p>';
    echo '<pre style="background:#f5f5f5;padding:10px;font-size:12px;overflow-x:auto">' . htmlspecialchars($content) . '</pre>';
} else {
    echo '<p><span class="err">❌ .htaccess NOT FOUND!</span></p>';
}
?>
</div>

<div class="card" style="background:#fff7ed;border:2px solid orange">
<strong>⚠️ HAPUS file ini setelah selesai!</strong> (<code>httpdocs/public/diag2.php</code>)
</div>
</body></html>
