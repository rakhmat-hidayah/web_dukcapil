<?php
/**
 * REPAIR & ENV CONNECT TOOL — Dukcapil Dompu
 * Akses: https://dukcapil.dompukab.go.id/fix_env.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$basePath = __DIR__;
if (!file_exists($basePath . '/bootstrap/app.php') && file_exists(dirname(__DIR__) . '/bootstrap/app.php')) {
    $basePath = dirname(__DIR__);
}

$envFile = $basePath . '/.env';
$envExample = $basePath . '/.env.example';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $port = trim($_POST['db_port'] ?? '3306');
    $name = trim($_POST['db_name'] ?? 'dukcapil_dompukab');
    $user = trim($_POST['db_user'] ?? '');
    $pass = trim($_POST['db_pass'] ?? '');

    try {
        // Try PDO connection with host
        $pdo = null;
        $connectedHost = $host;
        $hostsToTry = array_unique([$host, 'localhost', '127.0.0.1']);

        foreach ($hostsToTry as $tryHost) {
            try {
                $dsn = "mysql:host={$tryHost};port={$port};dbname={$name};charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $connectedHost = $tryHost;
                break;
            } catch (Throwable $t) {
                // try next
            }
        }

        if (!$pdo) {
            throw new Exception("Koneksi MySQL Gagal untuk user '$user'. Mohon periksa kembali password database MySQL Anda.");
        }

        // Test if tables exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        $hasUsers = $stmt->rowCount() > 0;

        // Build .env content
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        if (file_exists($envFile) && preg_match('/APP_KEY=(base64:[A-Za-z0-9+\/=]+)/', file_get_contents($envFile), $m)) {
            $appKey = $m[1];
        }

        $envContent = <<<ENV
APP_NAME="Disdukcapil Kabupaten Dompu"
APP_ENV=production
APP_KEY={$appKey}
APP_DEBUG=false
APP_URL=https://dukcapil.dompukab.go.id

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$connectedHost}
DB_PORT={$port}
DB_DATABASE={$name}
DB_USERNAME={$user}
DB_PASSWORD="{$pass}"

BROADCAST_DRIVER=log
CACHE_DRIVER=file
CACHE_STORE=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
ENV;

        @chmod($envFile, 0666);
        $written = @file_put_contents($envFile, $envContent);

        if ($written === false) {
            throw new Exception("Gagal menulis file .env! Periksa izin akses folder httpdocs.");
        }

        // Clear Laravel view/config cache files
        $cacheDirs = [$basePath . '/storage/framework/views', $basePath . '/bootstrap/cache'];
        foreach ($cacheDirs as $cd) {
            if (is_dir($cd)) {
                foreach (glob($cd . '/*.php') ?: [] as $cf) @unlink($cf);
            }
        }

        $msg = "✅ KONEKSI DATABASE BERHASIL! File .env berhasil tersimpan (Host: {$connectedHost}, DB: {$name}). " . ($hasUsers ? "Tabel database sudah ada!" : "Database belum terisi tabel.");

    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

// Read current .env
$currUser = '';
$currName = 'dukcapil_dompukab';
$currHost = 'localhost';
if (file_exists($envFile)) {
    $c = file_get_contents($envFile);
    if (preg_match('/DB_USERNAME=(.*)/', $c, $m)) $currUser = trim(trim($m[1]), '"\'');
    if (preg_match('/DB_DATABASE=(.*)/', $c, $m)) $currName = trim(trim($m[1]), '"\'');
    if (preg_match('/DB_HOST=(.*)/', $c, $m)) $currHost = trim(trim($m[1]), '"\'');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hubungkan Database & Fix .env — Dukcapil Dompu</title>
<style>
body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#0f172a;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
.card{background:#1e293b;border-radius:16px;padding:32px;width:100%;max-width:540px;border:1px solid #334155;}
h1{font-size:1.3rem;color:#38bdf8;margin-top:0;}
p{font-size:0.85rem;color:#94a3b8;line-height:1.5;}
.alert{padding:14px;border-radius:10px;font-size:0.85rem;margin-bottom:20px;line-height:1.5;}
.alert-ok{background:#064e3b;color:#6ee7b7;border:1px solid #047857;}
.alert-err{background:#7f1d1d;color:#fca5a5;border:1px solid #b91c1c;}
.form-group{margin-bottom:14px;}
label{display:block;font-size:0.75rem;font-weight:700;color:#cbd5e1;margin-bottom:4px;text-transform:uppercase;}
input{width:100%;padding:10px 12px;border-radius:8px;border:1px solid #475569;background:#0f172a;color:#fff;font-size:0.9rem;}
button{width:100%;padding:12px;border-radius:10px;border:none;background:#0284c7;color:#fff;font-size:0.95rem;font-weight:800;cursor:pointer;margin-top:10px;}
button:hover{background:#0369a1;}
a.btn{display:inline-block;padding:10px 16px;background:#0284c7;color:#fff;text-decoration:none;border-radius:8px;font-size:0.85rem;font-weight:700;margin-top:12px;margin-right:8px;}
</style>
</head>
<body>
<div class="card">
    <h1>🔌 Hubungkan Database MySQL ke Web</h1>
    <p>Gunakan alat ini untuk memasukkan password MySQL Plesk Anda agar file <code>.env</code> terhubung 100% dengan database phpMyAdmin Anda.</p>

    <?php if ($msg): ?>
        <div class="alert alert-ok">
            <?php echo htmlspecialchars($msg); ?>
            <div style="margin-top:12px;">
                <a href="/" class="btn" target="_blank">🌐 Buka Website Utama</a>
                <a href="/admin/login" class="btn" target="_blank">🔐 Buka Login CMS Admin</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div class="alert alert-err">❌ <?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Database Host</label>
            <input type="text" name="db_host" value="<?php echo htmlspecialchars($currHost ?: 'localhost'); ?>" required>
        </div>
        <div class="form-group">
            <label>Database Port</label>
            <input type="text" name="db_port" value="3306" required>
        </div>
        <div class="form-group">
            <label>Nama Database MySQL</label>
            <input type="text" name="db_name" value="<?php echo htmlspecialchars($currName ?: 'dukcapil_dompukab'); ?>" required>
        </div>
        <div class="form-group">
            <label>Username Database MySQL</label>
            <input type="text" name="db_user" value="<?php echo htmlspecialchars($currUser ?: 'user_dukcapil'); ?>" required>
        </div>
        <div class="form-group">
            <label>Password Database MySQL Plesk</label>
            <input type="password" name="db_pass" placeholder="Masukkan password MySQL Anda" required>
        </div>
        <button type="submit">⚡ Hubungkan Database Sekarang</button>
    </form>
</div>
</body>
</html>
