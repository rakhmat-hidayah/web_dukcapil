<?php
/**
 * Skrip Pembersih Cache & OPcache Laravel di Plesk (1-Klik)
 */
define('LARAVEL_START', microtime(true));

// 1. Reset OPcache immediately
if (function_exists('opcache_reset')) {
    @opcache_reset();
}

// 2. Remove stale compiled views & bootstrap caches manually
$basePath = dirname(__DIR__);
$staleFiles = array_merge(
    glob($basePath . '/storage/framework/views/*.php') ?: [],
    glob($basePath . '/bootstrap/cache/*.php') ?: []
);
foreach ($staleFiles as $f) {
    if (basename($f) !== '.gitignore') {
        @unlink($f);
    }
}

// 3. Clear Laravel Artisan caches safely
try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('permission:cache-reset');
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
} catch (\Throwable $e) {}

echo "<div style='font-family:sans-serif; padding:20px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:12px;'>";
echo "<h2>✅ CACHE SISTEM & OPCACHE BERHASIL DIBERSIHKAN!</h2>";
echo "<p>Sekarang silakan <a href='/'>Buka Kembali Halaman Utama Website</a>.</p>";
echo "</div>";
