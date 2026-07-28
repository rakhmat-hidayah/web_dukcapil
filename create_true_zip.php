<?php

// Script to build a 100% genuine standard PKZIP file for Plesk File Manager
set_time_limit(600);
ini_set('memory_limit', '1024M');

$root = __DIR__;
$zipFile = $root . '/dukcapil_dompu_plesk_v1.zip';

if (file_exists($zipFile)) {
    @unlink($zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("ERROR: Cannot open zip file for writing.\n");
}

$excludeTopLevel = ['.git', 'node_modules', 'tests', 'backups', '.gemini', 'dukcapil_dompu_plesk_ready.zip', 'dukcapil_dompu_plesk_v1.zip'];

$dirIterator = new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

$addedCount = 0;
foreach ($iterator as $file) {
    $realPath = $file->getRealPath();
    $relativePath = substr($realPath, strlen($root) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    // Skip top level excluded items
    $firstPart = explode('/', $relativePath)[0];
    if (in_array($firstPart, $excludeTopLevel)) {
        continue;
    }

    if ($file->isDir()) {
        $zip->addEmptyDir($relativePath);
    } else if ($file->isFile()) {
        $zip->addFile($realPath, $relativePath);
        $addedCount++;
    }
}

$success = $zip->close();

if ($success && file_exists($zipFile)) {
    $sizeMB = round(filesize($zipFile) / (1024 * 1024), 2);
    echo "SUCCESS: Created genuine zip archive with $addedCount files ($sizeMB MB)\n";
} else {
    echo "ERROR: Failed to save zip archive.\n";
}
