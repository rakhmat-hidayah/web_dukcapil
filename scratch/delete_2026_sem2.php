<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deleted = App\Models\DemographicDataset::where('year', 2026)
    ->where('semester', 2)
    ->delete();

echo "BERHASIL: Deleting {$deleted} datasets for 2026 Semester 2.\n";

$remainingYearsSem = App\Models\DemographicDataset::select('year', 'semester')
    ->distinct()
    ->orderByDesc('year')
    ->orderByDesc('semester')
    ->get();

echo "Sisa Periode Data:\n";
foreach ($remainingYearsSem as $item) {
    echo "- Tahun {$item->year} Semester {$item->semester}\n";
}
