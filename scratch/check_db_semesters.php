<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$datasets = App\Models\DemographicDataset::select('year', 'semester')
    ->distinct()
    ->orderByDesc('year')
    ->orderByDesc('semester')
    ->get();

echo "Existing datasets in DB:\n";
foreach ($datasets as $d) {
    $count = App\Models\DemographicDataset::where('year', $d->year)->where('semester', $d->semester)->count();
    echo "- Year {$d->year} Semester {$d->semester} ({$count} datasets)\n";
}
