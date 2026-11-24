<?php

namespace App\Services;

use App\Models\SurveyPeriod;
use App\Models\SurveyResponse;
use App\Models\SurveyResultStatistic;

class SurveyCalculationService
{
    /**
     * Determine PermenPANRB IKM Category (A, B, C, D) and Label.
     */
    public static function classifyIkmCategory(float $score): array
    {
        if ($score <= 0) {
            return [
                'category' => '-',
                'label' => 'Dalam Evaluasi',
                'description' => 'Dalam Pengumpulan / Evaluasi Data',
                'color' => 'gray',
            ];
        }

        if ($score >= 88.31) {
            return [
                'category' => 'A',
                'label' => 'Sangat Baik',
                'description' => 'Mutu Pelayanan Sangat Baik (A)',
                'color' => 'emerald',
            ];
        } elseif ($score >= 76.61) {
            return [
                'category' => 'B',
                'label' => 'Baik',
                'description' => 'Mutu Pelayanan Baik (B)',
                'color' => 'blue',
            ];
        } elseif ($score >= 65.00) {
            return [
                'category' => 'C',
                'label' => 'Kurang Baik',
                'description' => 'Mutu Pelayanan Kurang Baik (C)',
                'color' => 'amber',
            ];
        } else {
            return [
                'category' => 'D',
                'label' => 'Tidak Baik',
                'description' => 'Mutu Pelayanan Tidak Baik (D)',
                'color' => 'rose',
            ];
        }
    }

    /**
     * Calculate and refresh statistics for a given survey period.
     */
    public function calculatePeriodStats(int $surveyPeriodId): SurveyResultStatistic
    {
        $period = SurveyPeriod::with(['questions.answers', 'responses.answers'])->findOrFail($surveyPeriodId);

        // If survey mechanism is external PAN-RB (skm.go.id)
        if ($period->survey_mechanism === 'panrb') {
            $ikmVal = $period->external_ikm_value !== null ? (float) $period->external_ikm_value : 0;
            $catInfo = self::classifyIkmCategory($ikmVal);
            $quality = $period->external_service_quality ?: $catInfo['category'];
            $totalResp = $period->external_total_respondents !== null ? (int) $period->external_total_respondents : $period->responses->count();

            return SurveyResultStatistic::updateOrCreate(
                ['survey_period_id' => $surveyPeriodId],
                [
                    'total_respondents' => $totalResp,
                    'average_score' => round($ikmVal / 25, 2),
                    'ikm_value' => $ikmVal,
                    'service_quality_category' => $quality,
                    'index_breakdown_json' => [
                        'source' => 'Kementerian PAN-RB (skm.go.id)',
                        'url' => $period->external_survey_url,
                        'notes' => $period->external_notes,
                    ],
                ]
            );
        }

        $responses = $period->responses;

        $totalRespondents = $responses->count();

        if ($totalRespondents === 0) {
            return SurveyResultStatistic::updateOrCreate(
                ['survey_period_id' => $surveyPeriodId],
                [
                    'total_respondents' => 0,
                    'average_score' => 0,
                    'ikm_value' => 0,
                    'service_quality_category' => 'B',
                    'index_breakdown_json' => [],
                ]
            );
        }

        // Calculate average score per question
        $questions = $period->questions->where('is_enabled', true);
        $breakdown = [];
        $sumQuestionAverages = 0;
        $activeQuestionCount = 0;

        foreach ($questions as $q) {
            if ($q->question_type !== 'rating') {
                continue;
            }

            $answers = $q->answers;
            $avgRating = $answers->count() > 0 ? (float) $answers->avg('score') : 0;
            $nrrUnsur = $avgRating;
            $ikmUnsur = $nrrUnsur * 25; // Scale to 100

            $breakdown[] = [
                'question_id' => $q->id,
                'question_text' => $q->question_text,
                'category' => $q->service_category,
                'average_rating' => round($avgRating, 2),
                'ikm_unsur' => round($ikmUnsur, 2),
            ];

            $sumQuestionAverages += $avgRating;
            $activeQuestionCount++;
        }

        $overallAverageRating = $activeQuestionCount > 0 ? ($sumQuestionAverages / $activeQuestionCount) : 0;
        $overallIkmValue = $overallAverageRating * 25; // PermenPANRB formula: NRR Total * 25
        $classification = self::classifyIkmCategory($overallIkmValue);

        return SurveyResultStatistic::updateOrCreate(
            ['survey_period_id' => $surveyPeriodId],
            [
                'total_respondents' => $totalRespondents,
                'average_score' => round($overallAverageRating, 2),
                'ikm_value' => round($overallIkmValue, 2),
                'service_quality_category' => $classification['category'],
                'index_breakdown_json' => $breakdown,
            ]
        );
    }
}
