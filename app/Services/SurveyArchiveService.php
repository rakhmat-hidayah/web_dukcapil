<?php

namespace App\Services;

use App\Models\SurveyPeriod;

class SurveyArchiveService
{
    /**
     * Fetch list of archived survey periods with statistics.
     */
    public function getArchivedPeriods(): array
    {
        $periods = SurveyPeriod::with('statistic')
            ->where('status', 'published')
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        return $periods->map(function ($period) {
            $stats = $period->statistic;
            $ikmValue = $stats ? (float) $stats->ikm_value : 0;
            $categoryInfo = SurveyCalculationService::classifyIkmCategory($ikmValue);

            return [
                'id' => $period->id,
                'year' => $period->year,
                'semester' => $period->semester,
                'title' => $period->title,
                'start_date' => $period->start_date?->format('Y-m-d'),
                'end_date' => $period->end_date?->format('Y-m-d'),
                'ikm_score' => $ikmValue,
                'quality_category' => $categoryInfo['category'],
                'quality_label' => $categoryInfo['label'],
                'color' => $categoryInfo['color'],
                'respondents' => $stats ? $stats->total_respondents : 0,
            ];
        })->toArray();
    }
}
