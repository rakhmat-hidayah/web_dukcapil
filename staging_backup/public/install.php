<?php
/**
 * AUTOMATED ONE-CLICK DEPLOYMENT & DATABASE INITIALIZER
 * Dukcapil Kabupaten Dompu
 * Akses via Browser: https://dukcapil.dompukab.go.id/install.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';
$envExample = $basePath . '/.env.example';
$sqlFile = $basePath . '/dukcapil_database.sql';
$publicStorage = __DIR__ . '/storage';
$storageAppPublic = $basePath . '/storage/app/public';

// Read existing .env if present
$envData = [];
if (file_exists($envPath)) {
    foreach (file($envPath) as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
            list($k, $v) = explode('=', $line, 2);
            $envData[trim($k)] = trim(trim($v), '"\'');
        }
    }
}

$dbHost = $_POST['db_host'] ?? ($envData['DB_HOST'] ?? '127.0.0.1');
$dbPort = $_POST['db_port'] ?? ($envData['DB_PORT'] ?? '3306');
$dbName = $_POST['db_name'] ?? ($envData['DB_DATABASE'] ?? '');
$dbUser = $_POST['db_user'] ?? ($envData['DB_USERNAME'] ?? '');
$dbPass = $_POST['db_pass'] ?? ($envData['DB_PASSWORD'] ?? '');

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    try {
        if (!$dbName || !$dbUser) {
            throw new Exception("Nama Database dan Username Database wajib diisi!");
        }

        // 1. Test PDO connection
        $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);

        // Create DB if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");

        // 2. Import SQL Dump
        if (!file_exists($sqlFile)) {
            throw new Exception("File database `dukcapil_database.sql` tidak ditemukan!");
        }

        $sqlContent = file_get_contents($sqlFile);
        $pdo->exec($sqlContent);

        // 3. Update .env file
        if (!file_exists($envPath) && file_exists($envExample)) {
            copy($envExample, $envPath);
        }

        $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
        $updates = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
            'DB_HOST' => $dbHost,
            'DB_PORT' => $dbPort,
            'DB_DATABASE' => $dbName,
            'DB_USERNAME' => $dbUser,
            'DB_PASSWORD' => $dbPass,
            'FILESYSTEM_DISK' => 'public',
        ];

        foreach ($updates as $k => $v) {
            if (preg_match("/^{$k}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$k}=.*/m", "{$k}=\"{$v}\"", $envContent);
            } else {
                $envContent .= "\n{$k}=\"{$v}\"";
            }
        }

        // Generate APP_KEY if missing
        if (!str_contains($envContent, 'APP_KEY=base64:')) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $envContent = preg_replace("/^APP_KEY=.*/m", "APP_KEY={$key}", $envContent);
        }

        file_put_contents($envPath, $envContent);

        // 4. Create storage directory tree & sync media files
        $subDirs = ['officials', 'banners', 'branding', 'news', 'leader', 'gallery', 'gallery/covers', 'downloads', 'file_manager', 'complaints'];
        foreach ([$publicStorage, $storageAppPublic] as $targetDir) {
            if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
            foreach ($subDirs as $sd) {
                $p = $targetDir . '/' . $sd;
                if (!is_dir($p)) @mkdir($p, 0775, true);
            }
        }

        // Copy missing media files
        $mediaTypes = ['gallery', 'officials', 'banners', 'leader', 'news', 'downloads', 'branding'];
        foreach ($mediaTypes as $mt) {
            $misplaced = $basePath . '/' . $mt;
            if (is_dir($misplaced)) {
                safeCopyDir($misplaced, $publicStorage . '/' . $mt);
                safeCopyDir($misplaced, $storageAppPublic . '/' . $mt);
            }
        }
        safeCopyDir($storageAppPublic, $publicStorage);

        // Clear Laravel cache files
        $cacheDirs = [
            $basePath . '/storage/framework/cache/data',
            $basePath . '/storage/framework/views',
            $basePath . '/bootstrap/cache',
        ];
        foreach ($cacheDirs as $cd) {
            if (is_dir($cd)) {
                foreach (glob($cd . '/*.php') ?: [] as $cf) @unlink($cf);
            }
        }

        $message = "🎉 DEPLOYMENT BERHASIL! Database telah di-import, konfigurasi .env disetel, dan semua file media tersinkronisasi sempurna.";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function safeCopyDir($src, $dst) {
    if (!is_dir($src) || !is_dir($dst)) return;
    $realSrc = realpath($src);
    $realDst = realpath($dst);
    if ($realSrc && $realDst && str_starts_with($realDst, $realSrc)) return;

    try {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($items as $item) {
            $itemPath = $item->getRealPath();
            if ($realDst && str_starts_with($realDst, $itemPath)) continue;
            $target = $dst . str_replace($src, '', $itemPath);
            if ($item->isDir()) {
                if (!is_dir($target)) @mkdir($target, 0775, true);
            } else {
                if (!file_exists($target)) @copy($itemPath, $target);
            }
        }
    } catch (Throwable $t) {}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auto-Installer & Deployment — Dukcapil Dompu</title>
<style>
* { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
body { background: #0f172a; color: #f8fafc; margin: 0; padding: 40px 20px; display: flex; justify-content: center; min-height: 100vh; }
.card { background: #1e293b; border-radius: 16px; padding: 32px; width: 100%; max-width: 600px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); border: 1px solid #334155; }
h1 { font-size: 1.4rem; color: #38bdf8; margin-top: 0; margin-bottom: 8px; font-weight: 800; }
p.sub { font-size: 0.85rem; color: #94a3b8; margin-bottom: 24px; line-height: 1.5; }
.alert { padding: 14px 18px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 20px; line-height: 1.5; }
.alert-success { background: #064e3b; color: #6ee7b7; border: 1px solid #047857; }
.alert-error { background: #7f1d1d; color: #fca5a5; border: 1px solid #b91c1c; }
.form-group { margin-bottom: 16px; }
label { display: block; font-size: 0.78rem; font-weight: 700; color: #cbd5e1; margin-bottom: 6px; text-transform: uppercase; tracking-wider; }
input { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: #fff; font-size: 0.9rem; }
input:focus { border-color: #38bdf8; outline: none; }
button { width: 100%; padding: 14px; border-radius: 10px; border: none; background: #0284c7; color: #fff; font-size: 0.95rem; font-weight: 800; cursor: pointer; transition: 0.2s; margin-top: 10px; }
button:hover { background: #0369a1; }
.btn-secondary { display: inline-block; padding: 10px 16px; background: #334155; color: #fff; text-decoration: none; border-radius: 8px; font-size: 0.85rem; font-weight: 700; margin-right: 8px; margin-top: 12px; }
.btn-primary { display: inline-block; padding: 10px 16px; background: #0284c7; color: #fff; text-decoration: none; border-radius: 8px; font-size: 0.85rem; font-weight: 700; margin-top: 12px; }
</style>
</head>
<body>
<div class="card">
    <h1>🚀 Auto-Installer Deployment Dukcapil Dompu</h1>
    <p class="sub">Alat ini akan secara otomatis mengimpor seluruh database, mengatur `.env`, serta menyinkronkan seluruh folder foto &amp; media secara otomatis.</p>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
            <div style="margin-top: 14px;">
                <a href="/" class="btn-secondary" target="_blank">🌐 Lihat Website Utama</a>
                <a href="/admin/login" class="btn-primary" target="_blank">🔐 Login CMS Admin</a>
            </div>
            <div style="margin-top: 12px; font-size: 0.8rem; color: #94a3b8;">
                <strong>Admin Credential Bawaan:</strong><br>
                Email: <code>admin@dompukab.go.id</code><br>
                Password: <code>password</code>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            ❌ <strong>Gagal:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="action" value="install">

        <div class="form-group">
            <label>Database Host</label>
            <input type="text" name="db_host" value="<?php echo htmlspecialchars($dbHost); ?>" required>
        </div>

        <div class="form-group">
            <label>Database Port</label>
            <input type="text" name="db_port" value="<?php echo htmlspecialchars($dbPort); ?>" required>
        </div>

        <div class="form-group">
            <label>Nama Database (Plesk MySQL)</label>
            <input type="text" name="db_name" value="<?php echo htmlspecialchars($dbName); ?>" placeholder="Contoh: dukcapil_dompukab" required>
        </div>

        <div class="form-group">
            <label>Username Database MySQL</label>
            <input type="text" name="db_user" value="<?php echo htmlspecialchars($dbUser); ?>" placeholder="Contoh: dukcapil_user" required>
        </div>

        <div class="form-group">
            <label>Password Database MySQL</label>
            <input type="password" name="db_pass" value="<?php echo htmlspecialchars($dbPass); ?>" placeholder="Masukkan password MySQL Plesk Anda">
        </div>

        <button type="submit">⚡ Mulai Setup &amp; Import Database Otomatis</button>
    </form>
</div>
</body>
</html>
