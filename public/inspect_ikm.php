<?php
/**
 * SURVEY & IKM INSPECTOR AND SYNCHRONIZER
 * Upload: httpdocs/public/inspect_ikm.php
 * Run: https://dukcapil.dompukab.go.id/inspect_ikm.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') die('403');
header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\SurveyPeriod;
use App\Models\SurveyResultStatistic;

echo '<pre style="font-family:monospace;font-size:14px;background:#111;color:#eee;padding:20px">';
echo "📊 INSPECTING & SYNCHRONIZING SURVEY IKM DATA:\n\n";

$periods = DB::table('survey_periods')->get();
echo "Survei Periods Count: " . $periods->count() . "\n";

foreach ($periods as $p) {
    echo "  - Period ID {$p->id}: title='{$p->title}', year={$p->year}, semester={$p->semester}, is_active={$p->is_active}, status={$p->status}\n";
    $stat = DB::table('survey_result_statistics')->where('survey_period_id', $p->id)->first();
    if ($stat) {
        echo "    -> Stat ID {$stat->id}: ikm_value={$stat->ikm_value}, category={$stat->service_quality_category}, total_respondents={$stat->total_respondents}\n";
    } else {
        echo "    -> NO STATISTIC RECORD FOUND!\n";
    }
}

echo "\n--- SURVEY RESPONSES ---\n";
$responsesCount = DB::table('survey_responses')->count();
echo "Total Survey Responses: {$responsesCount}\n";
$avgScore = DB::table('survey_responses')->avg('ikm_score');
echo "Average IKM Score from survey_responses: " . ($avgScore ? number_format($avgScore, 2) : 'NULL') . "\n";

echo '</pre>';
