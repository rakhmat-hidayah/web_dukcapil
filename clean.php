<?php

// Cleanup script to remove weird windows-named symlinks and files from Linux Plesk
$publicDir = __DIR__ . '/public';
$baseDir = __DIR__;

function removeWeirdEntries($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if (str_contains($item, 'C:') || str_contains($item, 'laragon')) {
            $fullPath = $dir . '/' . $item;
            if (is_link($fullPath) || is_file($fullPath)) {
                @unlink($fullPath);
                echo "Deleted file/link: " . htmlspecialchars($item) . "<br>";
            } elseif (is_dir($fullPath)) {
                @exec("rm -rf " . escapeshellarg($fullPath));
                echo "Deleted directory: " . htmlspecialchars($item) . "<br>";
            }
        }
    }
}

removeWeirdEntries($baseDir);
removeWeirdEntries($publicDir);

echo "<h3>✅ Cleanup Successful! All weird Windows entries have been deleted.</h3>";
