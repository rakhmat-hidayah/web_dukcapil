<?php
/**
 * ============================================================
 * STORAGE TO UPLOADS SYMLINK & DIRECTORY LINKER SCRIPT
 * Dukcapil Kab. Dompu — fix_storage_symlink.php
 * ============================================================
 * FUNGSI:
 *  Menghubungkan folder 'public/storage' langsung ke 'public/uploads'
 *  sehingga URL berawalan /storage/ (di Chrome/Edge/Cache/DB)
 *  LANGSUNG membaca file dari /uploads/ dengan HTTP 200 OK.
 * ============================================================
 */

define('SECRET_KEY', 'DUKCAPIL2026');
if (($_GET['key'] ?? '') !== SECRET_KEY) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:red">403 Forbidden — Tambahkan ?key=DUKCAPIL2026 di URL</h2>');
}

header('Content-Type: text/html; charset=utf-8');

$publicDir  = __DIR__;
$storageLink = $publicDir . '/storage';
$uploadsDir  = $publicDir . '/uploads';

$status = [];

// 1. Cek keberadaan public/uploads
if (!is_dir($uploadsDir)) {
    $status[] = "❌ Folder public/uploads tidak ditemukan!";
} else {
    $status[] = "✅ Folder public/uploads ditemukan.";
}

// 2. Hapus symlink atau folder 'storage' lama di public jika bermasalah
if (file_exists($storageLink) || is_link($storageLink)) {
    if (is_link($storageLink)) {
        $target = readlink($storageLink);
        $status[] = "ℹ️ Target symlink storage saat ini: <code>{$target}</code>";
        @unlink($storageLink);
        $status[] = "🗑️ Symlink storage lama berhasil dihapus.";
    } elseif (is_dir($storageLink)) {
        // Jika folder biasa, rmdir
        @rmdir($storageLink);
        $status[] = "🗑️ Folder storage kosong lama berhasil dihapus.";
    }
}

// 3. Buat Symlink Baru: public/storage -> public/uploads
if (!file_exists($storageLink)) {
    // Coba symlink (Linux / Plesk)
    $success = @symlink($uploadsDir, $storageLink);
    
    if ($success) {
        $status[] = "🎉 <strong>BERHASIL!</strong> Symlink <code>public/storage</code> → <code>public/uploads</code> telah dibuat.";
    } else {
        // Jika symlink gagal karena permission, coba buat directory junction / fallback via PHP copy
        $status[] = "⚠️ Symlink PHP gagal, mencoba exec ln -s...";
        exec("ln -s " . escapeshellarg($uploadsDir) . " " . escapeshellarg($storageLink), $output, $returnCode);
        if ($returnCode === 0) {
            $status[] = "🎉 <strong>BERHASIL!</strong> Symlink <code>ln -s</code> berhasil dibuat.";
        } else {
            $status[] = "❌ Gagal membuat symlink via exec. Mohon buat symlink dari SSH/Plesk Terminal.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Fix Storage Symlink — Dukcapil Dompu</title>
<style>
body { font-family: sans-serif; background: #f0f4f9; padding: 30px; }
.card { max-width: 650px; margin: 0 auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
h1 { font-size: 18px; color: #1d4ed8; margin-bottom: 15px; }
ul { list-style: none; padding: 0; }
li { padding: 10px 0; border-bottom: 1px solid #eee; font-size: 14px; }
code { background: #eff6ff; padding: 2px 6px; border-radius: 4px; color: #1e40af; }
.warn { margin-top: 20px; background: #fff7ed; border: 1px solid #fdba74; padding: 12px; border-radius: 8px; font-size: 13px; color: #9a3412; }
</style>
</head>
<body>
<div class="card">
    <h1>🔗 Fix Storage Symlink ke Uploads — Dukcapil Dompu</h1>
    <ul>
        <?php foreach ($status as $msg): ?>
            <li><?= $msg ?></li>
        <?php endforeach; ?>
    </ul>

    <div class="warn">
        ⚠️ <strong>PENTING:</strong> Setelah selesai, segera hapus file ini (<code>public/fix_storage_symlink.php</code>) melalui Plesk File Manager!
    </div>
</div>
</body>
</html>
