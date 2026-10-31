<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// -----------------------------------------------------------------------------
// UNIVERSAL SMART IMAGE FINDER & SERVING HANDLER FOR ROOT & PUBLIC
// (SOLUSI 100% HILANGKAN 403 & GAMBAR PECAH DI PLESK HOSTING)
// -----------------------------------------------------------------------------
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$ext = strtolower(pathinfo($uriPath, PATHINFO_EXTENSION));

// Serve site.webmanifest from public folder or root
if ($uriPath === '/site.webmanifest') {
    $manifestFile = file_exists(__DIR__ . '/public/site.webmanifest') 
        ? __DIR__ . '/public/site.webmanifest' 
        : __DIR__ . '/site.webmanifest';
        
    if (file_exists($manifestFile)) {
        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        header('Access-Control-Allow-Origin: *');
        readfile($manifestFile);
        exit;
    }
}

// Serve images recursively from public/uploads
if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
    $fileName = basename($uriPath);
    
    $uploadsDir = file_exists(__DIR__ . '/public/uploads') 
        ? __DIR__ . '/public/uploads' 
        : __DIR__ . '/uploads';
        
    $foundFile = null;

    if (file_exists($uploadsDir . '/' . $fileName)) {
        $foundFile = $uploadsDir . '/' . $fileName;
    } elseif (is_dir($uploadsDir)) {
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getFilename()) === strtolower($fileName)) {
                    $foundFile = $file->getPathname();
                    break;
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }
    }

    if ($foundFile && file_exists($foundFile)) {
        $mimeTypes = [
            'webp' => 'image/webp',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
        ];

        $mime = $mimeTypes[$ext] ?? 'image/jpeg';
        
        header("Content-Type: {$mime}");
        header("Content-Length: " . filesize($foundFile));
        header("Cache-Control: public, max-age=31536000");
        readfile($foundFile);
        exit;
    }
}

// Maintenance mode check
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
