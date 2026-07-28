<?php

namespace App\Services;

use App\Models\SurveyPeriod;

class SurveyReportService
{
    /**
     * Generate structured printable report payload for a period.
     */
    public function generateReportPayload(int $surveyPeriodId): array
    {
        $period = SurveyPeriod::with([
            'statistic',
            'questions.options',
            'recommendations.followUpActions',
            'followUpActions',
            'publications'
        ])->findOrFail($surveyPeriodId);

        $calcService = new SurveyCalculationService();
        $chartService = new SurveyChartService();

        $stats = $period->statistic;
        $categoryInfo = SurveyCalculationService::classifyIkmCategory($stats ? $stats->ikm_value : 0);

        return [
            'period' => $period,
            'statistic' => $stats,
            'category_info' => $categoryInfo,
            'charts' => $chartService->generateChartDatasets($surveyPeriodId),
            'recommendations' => $period->recommendations,
            'follow_up_actions' => $period->followUpActions,
            'generated_at' => now()->format('d F Y H:i'),
        ];
    }
}
