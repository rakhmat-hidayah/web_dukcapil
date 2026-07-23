<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Announcement;
use App\Models\News;
use App\Models\Download;
use App\Models\SurveyPeriod;
use App\Services\SurveyCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Render the public homepage.
     */
    public function index(): InertiaResponse
    {
        // 1. Get active banners (Sliders / Campaigns)
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // 2. Get active announcements ticker text
        $tickers = Announcement::where('status', 'published')
            ->where('is_ticker', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->pluck('content')
            ->toArray();

        // 3. Get active popup warning alert
        $popup = Announcement::where('status', 'published')
            ->where('is_popup', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->orderBy('is_pinned', 'desc')
            ->first();

        // 4. Get 3 recent news articles
        $recentNews = News::with('category')
            ->published()
            ->limit(3)
            ->get();

        // 5. Get top downloads count
        $downloadShortcuts = Download::where('status', 'published')
            ->orderBy('download_count', 'desc')
            ->limit(4)
            ->get(['id', 'title', 'file_type', 'file_size']);

        // 6. Aggregate demographic highlights dynamically from latest published dataset
        $latestDataset = \App\Models\DemographicDataset::published()->where('type', 'population')->orderByDesc('year')->orderByDesc('semester')->first();
        $latestYear = $latestDataset ? $latestDataset->year : 2026;
        $latestSemester = $latestDataset ? $latestDataset->semester : 1;
        $periodLabel = "Semester {$latestSemester} (s.d. " . ($latestSemester == 1 ? 'Juni' : 'Desember') . " {$latestYear})";

        if ($latestDataset && $latestDataset->data_json && is_array($latestDataset->data_json)) {
            $popData     = $latestDataset->data_json;
            $totalMale   = isset($popData['male']) && is_array($popData['male']) ? array_sum($popData['male']) : 136108;
            $totalFemale = isset($popData['female']) && is_array($popData['female']) ? array_sum($popData['female']) : 134658;
            $totalPop    = $popData['total'] ?? ($totalMale + $totalFemale);
            $totalHouseholds = (int) round($totalPop / 3.646);

            $demographicSummary = [
                'total_population' => $totalPop,
                'total_male'       => $totalMale,
                'total_female'     => $totalFemale,
                'total_households' => $totalHouseholds,
                'period_label'     => $periodLabel,
            ];
        } else {
            $kecamatans = \App\Models\Kecamatan::all();
            $totalPop   = $kecamatans->sum('population_total') ?: 270766;
            $totalMale  = $kecamatans->sum('male_count') ?: 136108;
            $totalFemale = $kecamatans->sum('female_count') ?: 134658;
            $totalHouseholds = (int) round($totalPop / 3.646);

            $demographicSummary = [
                'total_population' => $totalPop,
                'total_male'       => $totalMale,
                'total_female'     => $totalFemale,
                'total_households' => $totalHouseholds,
                'period_label'     => $periodLabel,
            ];
        }

        // 7. Get Active IKM Survey Widget
        $activeSurvey = SurveyPeriod::with('statistic')
            ->where('is_active', true)
            ->where('status', 'published')
            ->first();

        $ikmWidget = null;
        if ($activeSurvey && $activeSurvey->statistic) {
            $score = (float) $activeSurvey->statistic->ikm_value;
            $cat = SurveyCalculationService::classifyIkmCategory($score);

            $ikmWidget = [
                'period_title' => $activeSurvey->title,
                'year' => $activeSurvey->year,
                'semester' => $activeSurvey->semester,
                'score' => $score,
                'category' => $cat['category'],
                'category_label' => $cat['label'],
                'total_respondents' => $activeSurvey->statistic->total_respondents,
            ];
        }

        return Inertia::render('Public/Home', [
            'banners' => $banners,
            'tickers' => $tickers,
            'popup' => $popup,
            'recentNews' => $recentNews,
            'downloadShortcuts' => $downloadShortcuts,
            'stats' => $demographicSummary,
            'ikmWidget' => $ikmWidget,
        ]);
    }
}
