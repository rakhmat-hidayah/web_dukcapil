<?php
/**
 * FIND EXACT DB TABLE AND RECORD FOR THE BROKEN IMAGES
 * Upload: httpdocs/public/db_search.php
 * Run: https://dukcapil.dompukab.go.id/db_search.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') die('403');
header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo '<pre style="font-family:monospace;font-size:14px;background:#111;color:#0f0;padding:20px">';
echo "🔍 SEARCHING ALL TABLES FOR BROKEN IMAGE REFERENCES...\n\n";

$searchTerms = ['2576714f', '16daceb1', '6dc4f618', 'storage/file_manager'];

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tableKey = "Tables_in_" . $dbName;

foreach ($tables as $t) {
    $tableName = $t->$tableKey;
    $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
    
    foreach ($columns as $col) {
        foreach ($searchTerms as $term) {
            try {
                $results = DB::table($tableName)
                    ->where($col, 'LIKE', "%{$term}%")
                    ->get();
                if ($results->count() > 0) {
                    echo "📌 FOUND IN TABLE [{$tableName}], COLUMN [{$col}] ({$results->count()} rows):\n";
                    foreach ($results as $row) {
                        $id = $row->id ?? 'N/A';
                        $val = substr((string)$row->$col, 0, 150);
                        echo "   -> Row ID {$id}: {$val}...\n";
                    }
                    echo "\n";
                }
            } catch (\Throwable $e) {
                // skip non-text columns
            }
        }
    }
}

echo "=== SEARCH COMPLETE ===\n";
echo '</pre>';
