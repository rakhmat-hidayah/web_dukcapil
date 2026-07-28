<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SurveyArchiveService;
use App\Services\SurveyCalculationService;
use App\Services\SurveyChartService;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

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
    #[OA\Get(
        path: '/v1/survey/current',
        summary: 'Survei IKM aktif saat ini',
        description: 'Mengambil metadata survei Indeks Kepuasan Masyarakat (IKM) yang sedang aktif beserta daftar pertanyaannya.',
        tags: ['Survey IKM'],
        responses: [
            new OA\Response(response: 200, description: 'Data survei aktif atau null jika tidak ada'),
        ]
    )]
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
    #[OA\Get(
        path: '/v1/survey/results',
        summary: 'Hasil IKM periode aktif',
        description: 'Mengambil nilai IKM, kategori kualitas layanan, dan breakdown indeks per pertanyaan untuk periode survei aktif.',
        tags: ['Survey IKM'],
        responses: [
            new OA\Response(response: 200, description: 'Hasil IKM periode aktif'),
        ]
    )]
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
    #[OA\Get(
        path: '/v1/survey/archive',
        summary: 'Arsip periode survei IKM historis',
        description: 'Mengambil semua periode survei IKM yang telah selesai beserta nilai IKM historisnya.',
        tags: ['Survey IKM'],
        responses: [
            new OA\Response(response: 200, description: 'Arsip data IKM historis'),
        ]
    )]
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
    #[OA\Get(
        path: '/v1/survey/statistics',
        summary: 'Dataset statistik chart survei IKM',
        description: 'Mengambil dataset lengkap yang siap digunakan untuk merender chart statistik hasil survei IKM periode aktif.',
        tags: ['Survey IKM'],
        responses: [
            new OA\Response(response: 200, description: 'Dataset chart statistik IKM'),
        ]
    )]
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
