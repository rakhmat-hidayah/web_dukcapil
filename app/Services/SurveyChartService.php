<?php

namespace App\Services;

use App\Models\SurveyPeriod;
use App\Models\SurveyResultStatistic;

class SurveyChartService
{
    /**
     * Generate dataset for all survey charts.
     */
    public function generateChartDatasets(int $surveyPeriodId): array
    {
        $period = SurveyPeriod::with(['questions', 'statistic', 'responses'])->findOrFail($surveyPeriodId);
        $statistic = $period->statistic;
        $breakdown = $statistic ? ($statistic->index_breakdown_json ?? []) : [];

        // 1. Bar Chart (Nilai per Unsur)
        $barChart = [
            'categories' => array_column($breakdown, 'category'),
            'data' => array_column($breakdown, 'ikm_unsur'),
        ];

        // 2. Radar Chart (Visualisasi Multi Unsur)
        $radarChart = [
            'indicators' => array_map(function ($b) {
                return ['name' => $b['category'], 'max' => 100];
            }, $breakdown),
            'values' => array_column($breakdown, 'ikm_unsur'),
        ];

        // 3. Trend Chart (Perkembangan IKM Antar Periode)
        $pastPeriods = SurveyPeriod::with('statistic')
            ->where('status', 'published')
            ->orderBy('year')
            ->orderBy('semester')
            ->get();

        $trendChart = [
            'periods' => $pastPeriods->map(fn($p) => "{$p->year} Sem {$p->semester}")->toArray(),
            'scores' => $pastPeriods->map(fn($p) => $p->statistic ? $p->statistic->ikm_value : 0)->toArray(),
        ];

        // 4. Pie Chart (Demografi Gender Responden)
        $genderDistribution = $period->responses
            ->groupBy('respondent_gender')
            ->map(fn($group, $key) => ['name' => $key ?: 'Lainnya', 'value' => $group->count()])
            ->values()
            ->toArray();

        // 5. Comparison Chart (Unsur Tertinggi vs Terendah)
        $sortedBreakdown = $breakdown;
        usort($sortedBreakdown, fn($a, $b) => $b['ikm_unsur'] <=> $a['ikm_unsur']);

        $topUnsur = array_slice($sortedBreakdown, 0, 3);
        $lowestUnsur = array_slice($sortedBreakdown, -3);

        $comparisonChart = [
            'highest' => $topUnsur,
            'lowest' => $lowestUnsur,
        ];

        return [
            'bar_chart' => $barChart,
            'radar_chart' => $radarChart,
            'trend_chart' => $trendChart,
            'pie_chart' => $genderDistribution,
            'comparison_chart' => $comparisonChart,
            'breakdown' => $breakdown,
        ];
    }
}
