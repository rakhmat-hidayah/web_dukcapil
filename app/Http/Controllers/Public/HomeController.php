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
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Render the public homepage.
     */
    public function index(): InertiaResponse
    {
        // 1. Get active banners (Sliders / Campaigns) - Cached 10 mins as pure array
        $banners = Cache::remember('home_banners_v3', 600, function () {
            return Banner::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        });
        if (!is_array($banners) || isset($banners['__PHP_Incomplete_Class_Name'])) {
            Cache::forget('home_banners_v3');
            $banners = Banner::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        }

        // 2. Get active announcements ticker text - Cached 5 mins
        $tickers = Cache::remember('home_tickers_v3', 300, function () {
            return Announcement::where('status', 'published')
                ->where('is_ticker', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->pluck('content')
                ->map(function ($item) {
                    $text = strip_tags(html_entity_decode($item, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    return trim(preg_replace('/\s+/', ' ', $text));
                })
                ->filter()
                ->values()
                ->toArray();
        });
        if (!is_array($tickers) || isset($tickers['__PHP_Incomplete_Class_Name'])) {
            Cache::forget('home_tickers_v3');
            $tickers = [];
        }

        // 3. Get active popup warning alert (fresh lookup for urgent announcements)
        $popup = Announcement::where('status', 'published')
            ->where('is_popup', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->orderBy('is_pinned', 'desc')
            ->first(['id', 'title', 'content']);

        // 4. Get 6 recent news articles (Berita Terkini/Terbaru) - Lean select & Cached 3 mins as pure array
        $recentNews = Cache::remember('home_recent_news_v3', 180, function () {
            return News::with('category:id,name,slug')
                ->published()
                ->select(['id', 'title', 'slug', 'excerpt', 'thumbnail', 'news_category_id', 'published_at', 'view_count'])
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($item) {
                    $thumb = $item->thumbnail;
                    if ($thumb) {
                        $thumb = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $thumb), '/');
                    }
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => $item->slug,
                        'excerpt' => $item->excerpt,
                        'thumbnail' => $thumb,
                        'published_at' => $item->published_at ? $item->published_at->toISOString() : null,
                        'view_count' => $item->view_count,
                        'category' => $item->category ? [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                            'slug' => $item->category->slug,
                        ] : null,
                    ];
                })
                ->toArray();
        });
        if (!is_array($recentNews) || isset($recentNews['__PHP_Incomplete_Class_Name'])) {
            Cache::forget('home_recent_news_v3');
            $recentNews = [];
        }

        // 4b. Get active featured innovations - Cached 10 mins as pure array
        $innovations = Cache::remember('home_innovations_v3', 600, function () {
            return \App\Models\Innovation::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(4)
                ->get()
                ->toArray();
        });
        if (!is_array($innovations) || isset($innovations['__PHP_Incomplete_Class_Name'])) {
            Cache::forget('home_innovations_v3');
            $innovations = \App\Models\Innovation::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(4)
                ->get()
                ->toArray();
        }

        // 5. Get top downloads count - Cached 10 mins as pure array
        $downloadShortcuts = Cache::remember('home_download_shortcuts_v3', 600, function () {
            return Download::where('status', 'published')
                ->orderBy('download_count', 'desc')
                ->limit(4)
                ->get(['id', 'title', 'file_type', 'file_size', 'file_path', 'download_count'])
                ->toArray();
        });
        if (!is_array($downloadShortcuts) || isset($downloadShortcuts['__PHP_Incomplete_Class_Name'])) {
            Cache::forget('home_download_shortcuts_v3');
            $downloadShortcuts = [];
        }

        // 6. Aggregate demographic highlights - Cached 1 hour
        $demographicSummary = Cache::remember('home_demographic_summary_v3', 3600, function () {
            $latestDataset = \App\Models\DemographicDataset::published()
                ->where('region_level', 'regency')
                ->where('type', 'population')
                ->orderByDesc('year')
                ->orderByDesc('semester')
                ->first();

            $latestYear     = $latestDataset ? $latestDataset->year : 2026;
            $latestSemester = $latestDataset ? $latestDataset->semester : 1;
            $periodLabel    = "Semester {$latestSemester} (s.d. " . ($latestSemester == 1 ? 'Juni' : 'Desember') . " {$latestYear})";

            $householdsDataset = \App\Models\DemographicDataset::published()
                ->where('region_level', 'regency')
                ->where('type', 'households')
                ->where('year', $latestYear)
                ->where('semester', $latestSemester)
                ->first();

            if ($latestDataset && $latestDataset->data_json && is_array($latestDataset->data_json)) {
                $popData     = $latestDataset->data_json;
                $totalMale   = isset($popData['male']) && is_array($popData['male']) ? array_sum($popData['male']) : 143240;
                $totalFemale = isset($popData['female']) && is_array($popData['female']) ? array_sum($popData['female']) : 142788;
                $totalPop    = $popData['total'] ?? ($totalMale + $totalFemale);

                $hhData = $householdsDataset ? $householdsDataset->data_json : null;
                if ($hhData && isset($hhData['total'])) {
                    $totalHouseholds = $hhData['total'];
                } elseif ($hhData && isset($hhData['items']) && is_array($hhData['items'])) {
                    $totalHouseholds = array_sum(array_column($hhData['items'], 'value'));
                } else {
                    $totalHouseholds = (int) round($totalPop / 3.646);
                }

                return [
                    'total_population' => $totalPop,
                    'total_male'       => $totalMale,
                    'total_female'     => $totalFemale,
                    'total_households' => $totalHouseholds,
                    'period_label'     => $periodLabel,
                ];
            }

            $kecamatans = \App\Models\Kecamatan::all();
            $totalPop   = $kecamatans->sum('population_total') ?: 286028;
            $totalMale  = $kecamatans->sum('male_count') ?: 143240;
            $totalFemale = $kecamatans->sum('female_count') ?: 142788;
            $totalHouseholds = 74800;

            return [
                'total_population' => $totalPop,
                'total_male'       => $totalMale,
                'total_female'     => $totalFemale,
                'total_households' => $totalHouseholds,
                'period_label'     => $periodLabel,
            ];
        });

        // 7. Get Active IKM Survey Widget - Cached 5 mins
        $ikmWidget = Cache::remember('home_ikm_widget', 300, function () {
            $activeSurvey = SurveyPeriod::with('statistic')
                ->where('is_active', true)
                ->where('status', 'published')
                ->first();

            if ($activeSurvey && $activeSurvey->statistic) {
                $score = (float) $activeSurvey->statistic->ikm_value;
                $cat = SurveyCalculationService::classifyIkmCategory($score);

                return [
                    'period_title' => $activeSurvey->title,
                    'year' => $activeSurvey->year,
                    'semester' => $activeSurvey->semester,
                    'score' => $score,
                    'category' => $cat['category'],
                    'category_label' => $cat['label'],
                    'total_respondents' => $activeSurvey->statistic->total_respondents,
                ];
            }

            return null;
        });

        return Inertia::render('Public/Home', [
            'banners' => $banners,
            'tickers' => $tickers,
            'popup' => $popup,
            'recentNews' => $recentNews,
            'innovations' => $innovations,
            'downloadShortcuts' => $downloadShortcuts,
            'stats' => $demographicSummary,
            'ikmWidget' => $ikmWidget,
        ]);
    }
}
