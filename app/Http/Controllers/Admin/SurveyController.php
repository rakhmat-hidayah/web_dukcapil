<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyFollowUpAction;
use App\Models\SurveyPeriod;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveyRecommendation;
use App\Services\ActivityLogger;
use App\Services\SurveyArchiveService;
use App\Services\SurveyCalculationService;
use App\Services\SurveyChartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SurveyController extends Controller
{
    protected SurveyCalculationService $calcService;
    protected SurveyChartService $chartService;
    protected SurveyArchiveService $archiveService;

    public function __construct(
        SurveyCalculationService $calcService,
        SurveyChartService $chartService,
        SurveyArchiveService $archiveService
    ) {
        $this->calcService = $calcService;
        $this->chartService = $chartService;
        $this->archiveService = $archiveService;
    }

    /**
     * Display unified Admin CMS tabbed workspace for Survey Management.
     */
    public function index(Request $request): InertiaResponse
    {
        $periods = SurveyPeriod::with('statistic')->orderBy('year', 'desc')->orderBy('semester', 'desc')->get();
        $activePeriod = SurveyPeriod::with([
            'questions.options',
            'statistic',
            'recommendations.followUpActions',
            'followUpActions',
            'publications'
        ])->where('is_active', true)->first() ?? $periods->first();

        $activePeriodId = $activePeriod?->id;
        $chartData = $activePeriodId ? $this->chartService->generateChartDatasets($activePeriodId) : null;
        $archives = $this->archiveService->getArchivedPeriods();

        return Inertia::render('Admin/Surveys/Index', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
            'chartData' => $chartData,
            'archives' => $archives,
        ]);
    }

    /**
     * Store new survey period.
     */
    public function storePeriod(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'semester' => 'required|in:1,2',
            'year' => 'required|integer|min:2020|max:2099',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'target_respondents' => 'required|integer|min:10',
            'status' => 'required|in:draft,published,closed',
            'is_active' => 'required|boolean',
        ]);

        if ($data['is_active']) {
            SurveyPeriod::query()->update(['is_active' => false]);
        }

        $period = SurveyPeriod::create($data);

        ActivityLogger::log("Membuat periode survei IKM baru: {$period->title}", null, 'create_survey_period');

        return redirect()->back()->with('success', 'Periode Survei IKM berhasil ditambahkan.');
    }

    /**
     * Update survey period.
     */
    public function updatePeriod(Request $request, SurveyPeriod $period): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'semester' => 'required|in:1,2',
            'year' => 'required|integer|min:2020|max:2099',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'target_respondents' => 'required|integer|min:10',
            'status' => 'required|in:draft,published,closed',
            'is_active' => 'required|boolean',
        ]);

        if ($data['is_active']) {
            SurveyPeriod::where('id', '!=', $period->id)->update(['is_active' => false]);
        }

        $period->update($data);

        ActivityLogger::log("Memperbarui periode survei IKM: {$period->title}", null, 'update_survey_period');

        return redirect()->back()->with('success', 'Periode Survei IKM berhasil diperbarui.');
    }

    /**
     * Store new survey question.
     */
    public function storeQuestion(Request $request, SurveyPeriod $period): RedirectResponse
    {
        $data = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:rating,multiple_choice,text,yes_no',
            'service_category' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:1',
            'is_required' => 'required|boolean',
            'is_enabled' => 'required|boolean',
        ]);

        $data['survey_period_id'] = $period->id;
        $question = SurveyQuestion::create($data);

        ActivityLogger::log("Menambahkan pertanyaan survei IKM baru", null, 'create_survey_question');

        return redirect()->back()->with('success', 'Pertanyaan survei berhasil ditambahkan.');
    }

    /**
     * Update survey question.
     */
    public function updateQuestion(Request $request, SurveyQuestion $question): RedirectResponse
    {
        $data = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:rating,multiple_choice,text,yes_no',
            'service_category' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:1',
            'is_required' => 'required|boolean',
            'is_enabled' => 'required|boolean',
        ]);

        $question->update($data);

        return redirect()->back()->with('success', 'Pertanyaan survei berhasil diperbarui.');
    }

    /**
     * Delete survey question.
     */
    public function destroyQuestion(SurveyQuestion $question): RedirectResponse
    {
        $question->delete();

        return redirect()->back()->with('success', 'Pertanyaan survei berhasil dihapus.');
    }

    /**
     * Store survey recommendation.
     */
    public function storeRecommendation(Request $request, SurveyPeriod $period): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'status' => 'required|in:pending,in_progress,completed',
            'target_completion' => 'nullable|date',
            'pic' => 'nullable|string|max:255',
        ]);

        $data['survey_period_id'] = $period->id;
        SurveyRecommendation::create($data);

        return redirect()->back()->with('success', 'Rekomendasi perbaikan berhasil ditambahkan.');
    }

    /**
     * Update survey recommendation.
     */
    public function updateRecommendation(Request $request, SurveyRecommendation $recommendation): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'status' => 'required|in:pending,in_progress,completed',
            'target_completion' => 'nullable|date',
            'pic' => 'nullable|string|max:255',
        ]);

        $recommendation->update($data);

        return redirect()->back()->with('success', 'Rekomendasi perbaikan berhasil diperbarui.');
    }

    /**
     * Delete survey recommendation.
     */
    public function destroyRecommendation(SurveyRecommendation $recommendation): RedirectResponse
    {
        $recommendation->delete();

        return redirect()->back()->with('success', 'Rekomendasi berhasil dihapus.');
    }

    /**
     * Store follow-up action.
     */
    public function storeFollowUpAction(Request $request, SurveyPeriod $period): RedirectResponse
    {
        $data = $request->validate([
            'recommendation_id' => 'nullable|exists:survey_recommendations,id',
            'action_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'responsible_unit' => 'required|string|max:255',
            'progress' => 'required|integer|min:0|max:100',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:planned,on_track,completed,delayed',
        ]);

        $data['survey_period_id'] = $period->id;
        SurveyFollowUpAction::create($data);

        return redirect()->back()->with('success', 'Rencana tindak lanjut IKM berhasil disimpan.');
    }

    /**
     * Update follow-up action.
     */
    public function updateFollowUpAction(Request $request, SurveyFollowUpAction $followUpAction): RedirectResponse
    {
        $data = $request->validate([
            'action_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'responsible_unit' => 'required|string|max:255',
            'progress' => 'required|integer|min:0|max:100',
            'completion_date' => 'nullable|date',
            'status' => 'required|in:planned,on_track,completed,delayed',
        ]);

        $followUpAction->update($data);

        return redirect()->back()->with('success', 'Rencana tindak lanjut IKM berhasil diperbarui.');
    }

    /**
     * Delete follow-up action.
     */
    public function destroyFollowUpAction(SurveyFollowUpAction $followUpAction): RedirectResponse
    {
        $followUpAction->delete();

        return redirect()->back()->with('success', 'Rencana tindak lanjut berhasil dihapus.');
    }

    /**
     * Recalculate survey statistics.
     */
    public function recalculate(SurveyPeriod $period): RedirectResponse
    {
        $this->calcService->calculatePeriodStats($period->id);

        return redirect()->back()->with('success', 'Kalkulasi Ulang Nilai IKM PermenPANRB Berhasil.');
    }
}
