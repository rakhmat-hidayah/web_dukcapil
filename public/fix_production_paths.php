<?php
/**
 * ============================================================
 * PRODUCTION DATABASE PATH FIX SCRIPT (v2 - PDO Direct)
 * Dukcapil Kab. Dompu — fix_production_paths.php
 * ============================================================
 * CARA PAKAI:
 *  1. Upload file ini ke httpdocs/ di Plesk
 *  2. Preview:  https://dukcapil.dompukab.go.id/fix_production_paths.php?key=DUKCAPIL2026&preview=1
 *  3. Fix live: https://dukcapil.dompukab.go.id/fix_production_paths.php?key=DUKCAPIL2026
 *  4. Setelah selesai, HAPUS file ini dari server!
 * ============================================================
 */

// ── SECURITY ─────────────────────────────────────────────────
define('SECRET_KEY', 'DUKCAPIL2026');
if (($_GET['key'] ?? '') !== SECRET_KEY) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:red">403 Forbidden — Tambahkan ?key=DUKCAPIL2026 di URL</h2>');
}

$dryRun = isset($_GET['preview']);

// ── BACA .env ─────────────────────────────────────────────────
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die('<h2 style="font-family:sans-serif;color:red">ERROR: File .env tidak ditemukan di ' . $envPath . '</h2>');
}

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$key, $val] = explode('=', $line, 2);
    $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
}

$host     = $env['DB_HOST']     ?? '127.0.0.1';
$port     = $env['DB_PORT']     ?? '3306';
$database = $env['DB_DATABASE'] ?? '';
$username = $env['DB_USERNAME'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

if (!$database) {
    die('<h2 style="font-family:sans-serif;color:red">ERROR: DB_DATABASE tidak ditemukan di .env</h2>');
}

// ── KONEKSI PDO ───────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('<h2 style="font-family:sans-serif;color:red">Koneksi DB gagal: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}

// ── HELPER: cek table/column ada ─────────────────────────────
function tableExists(PDO $pdo, string $db, string $table): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
    $stmt->execute([$db, $table]);
    return $stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $db, string $table, string $col): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
    $stmt->execute([$db, $table, $col]);
    return $stmt->fetchColumn() > 0;
}

// ── FIX FUNCTION ──────────────────────────────────────────────
$results    = [];
$totalFixed = 0;

/**
 * Pola yang difix (urutan penting — lebih spesifik dulu):
 *  storage/uploads/x  →  uploads/x
 *  uploads/uploads/x  →  uploads/x
 *  /storage/x         →  uploads/x   (trim leading slash)
 *  storage/x          →  uploads/x
 */
$patterns = [
    ['find' => 'storage/uploads/', 'replace' => 'uploads/'],
    ['find' => 'uploads/uploads/', 'replace' => 'uploads/'],
    ['find' => '/storage/',        'replace' => 'uploads/'],
    ['find' => 'storage/',         'replace' => 'uploads/'],
];

function fixColumn(PDO $pdo, string $db, string $table, string $column, bool $dryRun, array $patterns, array &$results, int &$totalFixed): void {
    if (!tableExists($pdo, $db, $table) || !columnExists($pdo, $db, $table, $column)) return;

    foreach ($patterns as $pat) {
        $find    = $pat['find'];
        $replace = $pat['replace'];

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` IS NOT NULL AND `{$column}` LIKE ?");
        $stmt->execute(["%{$find}%"]);
        $count = (int) $stmt->fetchColumn();

        if ($count > 0) {
            $totalFixed += $count;
            $results[] = compact('table', 'column', 'find', 'replace', 'count');

            if (!$dryRun) {
                $pdo->prepare("UPDATE `{$table}` SET `{$column}` = REPLACE(`{$column}`, ?, ?) WHERE `{$column}` IS NOT NULL AND `{$column}` LIKE ?")->execute([$find, $replace, "%{$find}%"]);
            }
        }
    }
}

// ── DAFTAR TARGET ─────────────────────────────────────────────
$targets = [
    ['table' => 'news',           'column' => 'thumbnail'],
    ['table' => 'news',           'column' => 'og_image'],
    ['table' => 'officials',      'column' => 'photo'],
    ['table' => 'org_structures', 'column' => 'photo'],
    ['table' => 'gallery_items',  'column' => 'file_path'],
    ['table' => 'gallery_items',  'column' => 'thumbnail'],
    ['table' => 'complaints',     'column' => 'attachment_path'],
    ['table' => 'pages',          'column' => 'og_image'],
    ['table' => 'ppid_requests',  'column' => 'attachment_path'],
    ['table' => 'ppid_requests',  'column' => 'response_file'],
    ['table' => 'ppid_documents', 'column' => 'file_path'],
    ['table' => 'settings',       'column' => 'value'],
    ['table' => 'home_banners',   'column' => 'image'],
    ['table' => 'home_banners',   'column' => 'image_path'],
    ['table' => 'sliders',        'column' => 'image'],
    ['table' => 'services',       'column' => 'icon'],
    ['table' => 'services',       'column' => 'image'],
];

foreach ($targets as $t) {
    fixColumn($pdo, $database, $t['table'], $t['column'], $dryRun, $patterns, $results, $totalFixed);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Fix DB Paths — Dukcapil Dompu</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:#f0f4f9;color:#1a1a2e;padding:28px}
.wrap{max-width:980px;margin:0 auto;background:#fff;border-radius:14px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,.08)}
h1{font-size:22px;color:#1d4ed8;margin-bottom:6px}
.badge{display:inline-block;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;margin-bottom:18px}
.preview{background:#fef3c7;color:#92400e}
.live{background:#d1fae5;color:#065f46}
table{width:100%;border-collapse:collapse;font-size:13px;margin-top:12px}
th{background:#1d4ed8;color:#fff;padding:10px 14px;text-align:left}
td{padding:9px 14px;border-bottom:1px solid #e5e7eb;vertical-align:top}
tr:nth-child(even)td{background:#f8faff}
code{background:#f1f5f9;border-radius:4px;padding:1px 5px;font-size:12px}
.total{margin-top:18px;font-size:15px;font-weight:700;color:#1d4ed8}
.clean{margin-top:18px;padding:14px 18px;background:#d1fae5;border-radius:10px;color:#065f46;font-weight:600}
.warn{margin-top:22px;padding:14px 18px;background:#fff7ed;border:1px solid #fdba74;border-radius:10px;color:#9a3412;font-size:13px}
.btns{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}
.btn{padding:9px 20px;border-radius:8px;text-decoration:none;font-weight:700;font-size:13px}
.btn-live{background:#1d4ed8;color:#fff}
.btn-prev{background:#e0e7ff;color:#1d4ed8}
.info{font-size:13px;color:#64748b;margin-bottom:14px}
</style>
</head>
<body>
<div class="wrap">
    <h1>🛠️ Fix Production DB Paths — Dukcapil Kab. Dompu</h1>
    <span class="badge <?= $dryRun ? 'preview' : 'live' ?>">
        <?= $dryRun ? '👁️ MODE PREVIEW — belum ada perubahan' : '⚡ MODE LIVE — database sudah diupdate' ?>
    </span>

    <p class="info">DB: <code><?= htmlspecialchars($database) ?></code> @ <code><?= htmlspecialchars($host) ?></code></p>

    <?php if (!empty($results)): ?>
    <table>
        <thead><tr><th>Table</th><th>Column</th><th>Path Lama (ditemukan)</th><th>Diganti jadi</th><th>Jumlah Record</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($results as $r): ?>
        <tr>
            <td><code><?= htmlspecialchars($r['table']) ?></code></td>
            <td><code><?= htmlspecialchars($r['column']) ?></code></td>
            <td><code style="color:#cc3300"><?= htmlspecialchars($r['find']) ?>...</code></td>
            <td><code style="color:#007700"><?= htmlspecialchars($r['replace']) ?>...</code></td>
            <td><strong><?= $r['count'] ?></strong></td>
            <td><?= $dryRun ? '👁️ Preview' : '✅ Fixed' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="total">Total: <?= $totalFixed ?> record <?= $dryRun ? 'akan diupdate' : 'berhasil diupdate ✅' ?></p>

    <?php else: ?>
    <div class="clean">✅ Database bersih — tidak ada path lama ditemukan.</div>
    <?php endif; ?>

    <div class="btns">
        <?php if ($dryRun && $totalFixed > 0): ?>
        <a class="btn btn-live" href="?key=<?= SECRET_KEY ?>">⚡ Jalankan Fix (Live Mode)</a>
        <?php elseif (!$dryRun): ?>
        <a class="btn btn-prev" href="?key=<?= SECRET_KEY ?>&preview=1">👁️ Preview Lagi</a>
        <?php endif; ?>
    </div>

    <div class="warn">
        ⚠️ <strong>PENTING:</strong> Setelah selesai, segera <strong>hapus file ini</strong>
        (<code>httpdocs/fix_production_paths.php</code>) melalui Plesk File Manager!
    </div>
</div>
</body>
</html>
