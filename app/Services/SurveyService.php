<?php

namespace App\Services;

use App\Models\SurveyPeriod;
use App\Models\SurveyResponse;
use App\Models\SurveyResponseAnswer;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    protected SurveyCalculationService $calculationService;

    public function __construct(SurveyCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Get active published survey period with questions and statistics.
     */
    public function getActiveSurvey(): ?SurveyPeriod
    {
        return SurveyPeriod::with(['questions.options', 'statistic', 'recommendations', 'followUpActions'])
            ->where('is_active', true)
            ->where('status', 'published')
            ->first();
    }

    /**
     * Store public survey response submission.
     */
    public function storeSubmission(array $data): SurveyResponse
    {
        return DB::transaction(function () use ($data) {
            $period = SurveyPeriod::findOrFail($data['survey_period_id']);

            $response = SurveyResponse::create([
                'survey_period_id' => $period->id,
                'respondent_name' => $data['respondent_name'] ?? 'Anonim',
                'respondent_age' => $data['respondent_age'] ?? null,
                'respondent_gender' => $data['respondent_gender'] ?? null,
                'respondent_education' => $data['respondent_education'] ?? null,
                'respondent_job' => $data['respondent_job'] ?? null,
                'service_accessed' => $data['service_accessed'] ?? 'Pelayanan Dukcapil',
                'suggestion' => $data['suggestion'] ?? null,
                'ikm_score' => 0,
            ]);

            $totalScore = 0;
            $ratingCount = 0;

            if (isset($data['answers']) && is_array($data['answers'])) {
                foreach ($data['answers'] as $qId => $val) {
                    $scoreVal = is_numeric($val) ? (float) $val : 0;

                    SurveyResponseAnswer::create([
                        'survey_response_id' => $response->id,
                        'survey_question_id' => $qId,
                        'answer_value' => (string) $val,
                        'score' => $scoreVal,
                    ]);

                    if ($scoreVal > 0) {
                        $totalScore += $scoreVal;
                        $ratingCount++;
                    }
                }
            }

            $avgScore = $ratingCount > 0 ? ($totalScore / $ratingCount) : 0;
            $response->update(['ikm_score' => round($avgScore * 25, 2)]);

            // Recalculate overall period statistics
            $this->calculationService->calculatePeriodStats($period->id);

            return $response;
        });
    }
}
