<?php
/**
 * INSPECT SANAI REDIRECT LOGS & VISITOR COUNTS
 * Upload: httpdocs/public/inspect_sanai.php
 * Run: https://dukcapil.dompukab.go.id/inspect_sanai.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') die('403');
header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo '<pre style="font-family:monospace;font-size:14px;background:#111;color:#eee;padding:20px">';
echo "🔍 SANAI & VISITOR LOGS STATS:\n\n";

$apiTotal = DB::table('api_logs')->count();
$apiToday = DB::table('api_logs')->whereDate('created_at', now()->today())->count();

$vTotal = DB::table('visitor_logs')->count();
$vToday = DB::table('visitor_logs')->whereDate('created_at', now()->today())->count();

echo "api_logs Total: {$apiTotal}\n";
echo "api_logs Today: {$apiToday}\n";
echo "visitor_logs Total: {$vTotal}\n";
echo "visitor_logs Today: {$vToday}\n\n";

// Check if sanai_redirects or similar table exists
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    foreach ($t as $k => $tableName) {
        if (str_contains($tableName, 'sanai') || str_contains($tableName, 'redirect') || str_contains($tableName, 'api')) {
            $cnt = DB::table($tableName)->count();
            echo "Table '{$tableName}': {$cnt} rows\n";
        }
    }
}

echo '</pre>';
