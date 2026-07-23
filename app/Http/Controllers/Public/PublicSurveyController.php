<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SurveyArchiveService;
use App\Services\SurveyCalculationService;
use App\Services\SurveyChartService;
use App\Services\SurveyReportService;
use App\Services\SurveyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublicSurveyController extends Controller
{
    protected SurveyService $surveyService;
    protected SurveyChartService $chartService;
    protected SurveyArchiveService $archiveService;
    protected SurveyReportService $reportService;

    public function __construct(
        SurveyService $surveyService,
        SurveyChartService $chartService,
        SurveyArchiveService $archiveService,
        SurveyReportService $reportService
    ) {
        $this->surveyService = $surveyService;
        $this->chartService = $chartService;
        $this->archiveService = $archiveService;
        $this->reportService = $reportService;
    }

    /**
     * Display single-page Survei Kepuasan Masyarakat (IKM).
     */
    public function index(): InertiaResponse
    {
        $activeSurvey = $this->surveyService->getActiveSurvey();

        if (!$activeSurvey) {
            return Inertia::render('Public/Survei', [
                'activeSurvey' => null,
                'stats' => null,
                'categoryInfo' => null,
                'chartData' => null,
                'archives' => $this->archiveService->getArchivedPeriods(),
            ]);
        }

        $stats = $activeSurvey->statistic;
        $ikmValue = $stats ? (float) $stats->ikm_value : 0;
        $categoryInfo = SurveyCalculationService::classifyIkmCategory($ikmValue);
        $chartData = $this->chartService->generateChartDatasets($activeSurvey->id);
        $archives = $this->archiveService->getArchivedPeriods();

        return Inertia::render('Public/Survei', [
            'activeSurvey' => [
                'id' => $activeSurvey->id,
                'title' => $activeSurvey->title,
                'semester' => $activeSurvey->semester,
                'year' => $activeSurvey->year,
                'start_date' => $activeSurvey->start_date?->format('Y-m-d'),
                'end_date' => $activeSurvey->end_date?->format('Y-m-d'),
                'target_respondents' => $activeSurvey->target_respondents,
                'questions' => $activeSurvey->questions->map(fn($q) => [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_type' => $q->question_type,
                    'service_category' => $q->service_category,
                    'is_required' => $q->is_required,
                    'options' => $q->options->map(fn($o) => [
                        'label' => $o->option_label,
                        'value' => $o->option_value,
                    ]),
                ]),
                'recommendations' => $activeSurvey->recommendations->map(fn($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'description' => $r->description,
                    'priority' => $r->priority,
                    'status' => $r->status,
                    'target_completion' => $r->target_completion?->format('d M Y'),
                    'pic' => $r->pic,
                ]),
                'follow_up_actions' => $activeSurvey->followUpActions->map(fn($a) => [
                    'id' => $a->id,
                    'action_name' => $a->action_name,
                    'description' => $a->description,
                    'responsible_unit' => $a->responsible_unit,
                    'progress' => $a->progress,
                    'completion_date' => $a->completion_date?->format('d M Y'),
                    'status' => $a->status,
                ]),
            ],
            'stats' => $stats ? [
                'total_respondents' => $stats->total_respondents,
                'average_score' => $stats->average_score,
                'ikm_value' => $stats->ikm_value,
                'service_quality_category' => $stats->service_quality_category,
                'response_rate' => $activeSurvey->target_respondents > 0
                    ? round(($stats->total_respondents / $activeSurvey->target_respondents) * 100, 1)
                    : 100,
            ] : null,
            'categoryInfo' => $categoryInfo,
            'chartData' => $chartData,
            'archives' => $archives,
        ]);
    }

    /**
     * Submit survey response from public.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'survey_period_id' => 'required|exists:survey_periods,id',
            'respondent_name' => 'nullable|string|max:255',
            'respondent_age' => 'nullable|string|max:50',
            'respondent_gender' => 'nullable|string|max:50',
            'respondent_education' => 'nullable|string|max:50',
            'respondent_job' => 'nullable|string|max:100',
            'service_accessed' => 'nullable|string|max:255',
            'suggestion' => 'nullable|string|max:1000',
            'answers' => 'required|array',
        ]);

        $this->surveyService->storeSubmission($request->all());

        return redirect()->back()->with('success', 'Terima kasih! Partisipasi dan penilaian survei Anda berhasil disimpan.');
    }
}
