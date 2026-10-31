<?php
/**
 * TEST IMAGE ACCESSIBILITY & FILE PERMISSIONS
 * Upload: httpdocs/public/test_img_access.php
 * Run: https://dukcapil.dompukab.go.id/test_img_access.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') die('403');
header('Content-Type: text/html; charset=utf-8');

$public = __DIR__;
$file1 = $public . '/uploads/file_manager/2026/07/16daceb1-b35c-4ceb-94cf-f870a7d3d7e3.jpg';

echo '<pre style="font-family:monospace;font-size:14px;padding:20px">';
echo "🧪 TESTING IMAGE ACCESS:\n\n";

echo "File path: {$file1}\n";
echo "File exists: " . (file_exists($file1) ? "YES ✅" : "NO ❌") . "\n";

if (file_exists($file1)) {
    echo "File permissions: " . substr(sprintf('%o', fileperms($file1)), -4) . "\n";
    echo "File size: " . filesize($file1) . " bytes\n";
    
    // Fix permissions if needed
    @chmod($file1, 0644);
    @chmod(dirname($file1), 0755);
    @chmod(dirname(dirname($file1)), 0755);
    @chmod(dirname(dirname(dirname($file1))), 0755);
    echo "Updated folder & file permissions to 0755/0644!\n";
}

echo "\n--- DIR CONTENT OF uploads/file_manager/2026/07/ ---\n";
$dir = $public . '/uploads/file_manager/2026/07';
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            echo "  - {$f} (" . filesize($dir . '/' . $f) . " bytes)\n";
        }
    }
} else {
    echo "Directory does not exist!\n";
}

echo '</pre>';
