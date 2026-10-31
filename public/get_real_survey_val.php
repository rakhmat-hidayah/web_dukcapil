<?php
/**
 * GET ACTUAL REAL SURVEY IKM SCORE FROM DATABASE
 * Upload: httpdocs/public/get_real_survey_val.php
 * Run: https://dukcapil.dompukab.go.id/get_real_survey_val.php?key=DUKCAPIL2026
 */
if (($_GET['key'] ?? '') !== 'DUKCAPIL2026') die('403');
header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\SurveyPeriod;
use App\Services\SurveyCalculationService;

echo '<pre style="font-family:monospace;font-size:14px;background:#111;color:#eee;padding:20px">';
echo "📊 REAL SURVEY IKM CALCULATION FROM DATABASE:\n\n";

$calcService = new SurveyCalculationService();

$activePeriod = SurveyPeriod::where('is_active', true)->first();
if ($activePeriod) {
    echo "Active Period: ID {$activePeriod->id} - {$activePeriod->title}\n";
    $stat = $calcService->calculatePeriodStats($activePeriod->id);
    echo "Calculated Statistic:\n";
    echo "  - Total Respondents: {$stat->total_respondents}\n";
    echo "  - Average Score: {$stat->average_score}\n";
    echo "  - IKM Value: {$stat->ikm_value}\n";
    echo "  - Category: {$stat->service_quality_category}\n";
} else {
    echo "No active period found!\n";
}

$avgFromResponses = DB::table('survey_responses')->avg('ikm_score');
echo "\nAverage ikm_score directly from survey_responses table: " . ($avgFromResponses !== null ? $avgFromResponses : 'No records') . "\n";

echo '</pre>';
