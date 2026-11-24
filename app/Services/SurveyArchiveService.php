<?php

namespace App\Services;

use App\Models\SurveyPeriod;

class SurveyArchiveService
{
    /**
     * Fetch list of archived survey periods with statistics.
     * Supports both 'internal' and 'panrb' mechanisms.
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
            $isPanRb = ($period->survey_mechanism === 'panrb');

            // For panrb: use external_ikm_value if available, else fallback to statistic
            if ($isPanRb && $period->external_ikm_value !== null) {
                $ikmValue = (float) $period->external_ikm_value;
                $respondents = $period->external_total_respondents !== null
                    ? (int) $period->external_total_respondents
                    : ($stats ? (int) $stats->total_respondents : 0);
                $qualityCategory = $period->external_service_quality ?: null;
            } else {
                $ikmValue = $stats ? (float) $stats->ikm_value : 0;
                $respondents = $stats ? (int) $stats->total_respondents : 0;
                $qualityCategory = null;
            }

            $categoryInfo = SurveyCalculationService::classifyIkmCategory($ikmValue);
            $hasScore = $ikmValue > 0;

            return [
                'id'               => $period->id,
                'year'             => $period->year,
                'semester'         => $period->semester,
                'title'            => $period->title,
                'start_date'       => $period->start_date?->format('Y-m-d'),
                'end_date'         => $period->end_date?->format('Y-m-d'),
                'survey_mechanism' => $period->survey_mechanism ?? 'internal',
                'ikm_score'        => $ikmValue,
                'has_score'        => $hasScore,
                'quality_category' => $qualityCategory ?: $categoryInfo['category'],
                'quality_label'    => $hasScore ? $categoryInfo['label'] : 'Survei Belum Tuntas',
                'color'            => $hasScore ? $categoryInfo['color'] : 'amber',
                'respondents'      => $respondents,
            ];
        })->toArray();
    }
}
