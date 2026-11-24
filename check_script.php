<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check ALL periods with ALL their data
$all = \App\Models\SurveyPeriod::with('statistic')->orderBy('id')->get();
echo "Total periods: " . $all->count() . "\n\n";
foreach ($all as $p) {
    echo "--- Period ID: {$p->id} ---\n";
    echo "  Title: {$p->title}\n";
    echo "  Active: " . ($p->is_active ? 'YES' : 'no') . "\n";
    echo "  Mechanism: " . ($p->survey_mechanism ?? 'null') . "\n";
    echo "  External IKM: " . var_export($p->external_ikm_value, true) . "\n";
    echo "  External Responden: " . var_export($p->external_total_respondents, true) . "\n";
    echo "  External Quality: " . var_export($p->external_service_quality, true) . "\n";
    echo "  Statistic IKM: " . var_export($p->statistic?->ikm_value, true) . "\n";
    echo "  Statistic Responden: " . var_export($p->statistic?->total_respondents, true) . "\n";
    echo "  Statistic Quality: " . var_export($p->statistic?->service_quality_category, true) . "\n";
    echo "\n";
}

// Also check raw DB
$rawPeriods = \Illuminate\Support\Facades\DB::table('survey_periods')->get();
echo "\nRaw survey_periods count: " . $rawPeriods->count() . "\n";
foreach ($rawPeriods as $p) {
    echo "  ID:{$p->id} | {$p->title} | active={$p->is_active}\n";
}

$rawStats = \Illuminate\Support\Facades\DB::table('survey_result_statistics')->get();
echo "\nRaw survey_result_statistics count: " . $rawStats->count() . "\n";
foreach ($rawStats as $s) {
    echo "  ID:{$s->id} | period_id={$s->survey_period_id} | ikm={$s->ikm_value} | responden={$s->total_respondents}\n";
}
