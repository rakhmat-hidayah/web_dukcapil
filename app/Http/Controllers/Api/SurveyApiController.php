<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SurveyArchiveService;
use App\Services\SurveyCalculationService;
use App\Services\SurveyChartService;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;

class SurveyApiController extends Controller
{
    protected SurveyService $surveyService;
    protected SurveyChartService $chartService;
    protected SurveyArchiveService $archiveService;

    public function __construct(
        SurveyService $surveyService,
        SurveyChartService $chartService,
        SurveyArchiveService $archiveService
    ) {
        $this->surveyService = $surveyService;
        $this->chartService = $chartService;
        $this->archiveService = $archiveService;
    }

    /**
     * Public API: Get Active Survey Metadata & Questions.
     */
    public function current(): JsonResponse
    {
        $activeSurvey = $this->surveyService->getActiveSurvey();

        if (!$activeSurvey) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'Tidak ada survei IKM aktif saat ini.',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $activeSurvey->id,
                'title' => $activeSurvey->title,
                'semester' => $activeSurvey->semester,
                'year' => $activeSurvey->year,
                'start_date' => $activeSurvey->start_date?->format('Y-m-d'),
                'end_date' => $activeSurvey->end_date?->format('Y-m-d'),
                'questions' => $activeSurvey->questions->map(fn($q) => [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_type' => $q->question_type,
                    'service_category' => $q->service_category,
                    'is_required' => $q->is_required,
                ]),
            ]
        ]);
    }

    /**
     * Public API: Get Current IKM Results & Quality Category.
     */
    public function results(): JsonResponse
    {
        $activeSurvey = $this->surveyService->getActiveSurvey();

        if (!$activeSurvey || !$activeSurvey->statistic) {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        $stats = $activeSurvey->statistic;
        $categoryInfo = SurveyCalculationService::classifyIkmCategory($stats->ikm_value);

        return response()->json([
            'status' => 'success',
            'data' => [
                'period_title' => $activeSurvey->title,
                'total_respondents' => $stats->total_respondents,
                'average_score' => $stats->average_score,
                'ikm_value' => $stats->ikm_value,
                'service_quality_category' => $stats->service_quality_category,
                'category_label' => $categoryInfo['label'],
                'color' => $categoryInfo['color'],
                'breakdown' => $stats->index_breakdown_json,
            ]
        ]);
    }

    /**
     * Public API: Get Historical IKM Archive.
     */
    public function archive(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->archiveService->getArchivedPeriods(),
        ]);
    }

    /**
     * Public API: Get Detailed Chart Statistics Datasets.
     */
    public function statistics(): JsonResponse
    {
        $activeSurvey = $this->surveyService->getActiveSurvey();

        if (!$activeSurvey) {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $this->chartService->generateChartDatasets($activeSurvey->id),
        ]);
    }
}
