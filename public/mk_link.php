<?php
/**
 * Create storage → uploads symlink via PHP
 * Upload to: httpdocs/public/mk_link.php
 * Run: https://dukcapil.dompukab.go.id/mk_link.php?key=DUKCAPIL2026
 * DELETE AFTER USE!
 */
define('SECRET_KEY', 'DUKCAPIL2026');
if (($_GET['key'] ?? '') !== SECRET_KEY) {
    http_response_code(403);
    die('403 Forbidden');
}

$publicDir   = __DIR__;
$storageLink = $publicDir . '/storage';
$uploadsDir  = $publicDir . '/uploads';

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:monospace;font-size:14px;padding:20px;">';

echo "📂 Public Dir  : {$publicDir}\n";
echo "📂 Uploads Dir : {$uploadsDir}\n";
echo "🔗 Storage Link: {$storageLink}\n\n";

echo "📁 uploads exists  : " . (is_dir($uploadsDir) ? "YES ✅" : "NO ❌") . "\n";
echo "📁 storage exists  : " . (file_exists($storageLink) ? "YES" : "NO") . "\n";
echo "🔗 storage is link : " . (is_link($storageLink) ? "YES, target=" . readlink($storageLink) : "NO") . "\n\n";

// Step 1: Remove old storage
if (is_link($storageLink)) {
    unlink($storageLink);
    echo "🗑️  Removed old symlink\n";
} elseif (is_dir($storageLink)) {
    // Try to remove empty dir
    @rmdir($storageLink);
    echo "🗑️  Tried rmdir storage/\n";
}

// Step 2: Try symlink
if (!file_exists($storageLink)) {
    $r = @symlink($uploadsDir, $storageLink);
    if ($r) {
        echo "🎉 SYMLINK CREATED: storage → uploads\n";
    } else {
        $err = error_get_last();
        echo "❌ symlink() failed: " . ($err['message'] ?? 'unknown') . "\n";

        // Try exec
        $cmd = "ln -sfn " . escapeshellarg($uploadsDir) . " " . escapeshellarg($storageLink);
        exec($cmd, $out, $code);
        echo "🔧 exec: {$cmd}\n";
        echo "   return: {$code}\n";
        echo "   output: " . implode("\n", $out) . "\n";

        if ($code === 0) {
            echo "🎉 SYMLINK CREATED via exec!\n";
        } else {
            // Last resort: create a static .htaccess inside storage dir
            echo "\n⚠️  Symlink failed. Trying .htaccess alias inside storage/ dir...\n";
            if (!is_dir($storageLink)) {
                mkdir($storageLink, 0755, true);
            }
            $ht = "Options -Indexes\nRewriteEngine On\nRewriteRule ^(.*)$ ../uploads/\$1 [L]\n";
            file_put_contents($storageLink . '/.htaccess', $ht);
            echo "📄 .htaccess written to storage/ folder\n";
        }
    }
} else {
    echo "⚠️  storage/ still exists, cannot create symlink!\n";
    echo "     Type: " . filetype($storageLink) . "\n";
}

echo "\n--- CURRENT STATE ---\n";
echo "storage is_link    : " . (is_link($storageLink) ? "YES → " . readlink($storageLink) : "NO") . "\n";
echo "storage is_dir     : " . (is_dir($storageLink) ? "YES" : "NO") . "\n";

// Test: can we read a file through the symlink?
if (is_dir($storageLink)) {
    $files = glob($uploadsDir . '/news/*/*.jpg');
    if ($files) {
        $testFile = str_replace($uploadsDir, $storageLink, $files[0]);
        $relPath  = str_replace($publicDir, '', $files[0]);
        echo "\n🧪 Test file via uploads: {$relPath}\n";
        echo "   exists via storage: " . (file_exists($testFile) ? "YES ✅" : "NO ❌") . "\n";
    }
}

echo '</pre>';
echo '<p style="padding:20px;background:#fff7ed;border:1px solid orange;margin:10px;font-family:sans-serif;">⚠️ <strong>HAPUS FILE INI</strong> dari Plesk File Manager setelah selesai!</p>';
