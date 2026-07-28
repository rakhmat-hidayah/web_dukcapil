<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DemographicDataset;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DemographicController extends Controller
{
    /**
     * Display the public citizen-facing statistics page.
     * Data is ONLY sourced from real uploaded datasets — no estimation.
     */
    public function statistics(Request $request): InertiaResponse
    {
        // ── Administrative references (display only) ──────────────────────
        $kecamatans = Kecamatan::orderBy('code')
            ->get(['id', 'name', 'code', 'area_km2']);

        $desas = Desa::orderBy('name')
            ->get(['id', 'kecamatan_id', 'name', 'code']);

        // ── CMS Filter Controls & Settings ────────────────────────────────
        $showRegionFilter   = WebsiteSetting::where('key', 'demographics_show_region_filter')->value('value') === '1';
        $showDistrictFilter = WebsiteSetting::where('key', 'demographics_show_district_filter')->value('value') === '1';
        $defaultLevel       = WebsiteSetting::where('key', 'demographics_default_level')->value('value') ?: 'regency';

        if ($showRegionFilter) {
            $regionLevel = $request->input('region_level', $defaultLevel);
            $regionCode  = $request->input('region_code', $regionLevel === 'regency' ? DemographicDataset::REGENCY_CODE : ($kecamatans->first()->code ?? '520501'));
        } else {
            $regionLevel = 'regency';
            $regionCode  = DemographicDataset::REGENCY_CODE;
        }

        // ── Period filter — based on real data available for this region ──
        $availableYears = DemographicDataset::published()
            ->where('region_level', $regionLevel)
            ->where('region_code', $regionCode)
            ->distinct()->orderByDesc('year')->pluck('year');

        // Fallback: show all years if no data for this region yet
        if ($availableYears->isEmpty()) {
            $availableYears = DemographicDataset::published()
                ->distinct()->orderByDesc('year')->pluck('year');
        }

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) date('Y')]);
        }

        $selectedYear = $request->integer('year', $availableYears->first() ?? date('Y'));

        $availableSemesters = DemographicDataset::published()
            ->where('region_level', $regionLevel)
            ->where('region_code', $regionCode)
            ->where('year', $selectedYear)
            ->distinct()->orderBy('semester')->pluck('semester');

        if ($availableSemesters->isEmpty()) {
            $availableSemesters = collect([1, 2]);
        }

        $requestedSemester = $request->integer('semester', $availableSemesters->last() ?? 1);
        $selectedSemester  = $availableSemesters->contains($requestedSemester)
            ? $requestedSemester
            : ($availableSemesters->last() ?? $requestedSemester);

        // ── Load ONLY real datasets for the selected region + period ──────
        $rawDatasets = DemographicDataset::published()
            ->forRegion($regionLevel, $regionCode)
            ->forPeriod($selectedYear, $selectedSemester)
            ->get();

        // Fallback: If specific district/village lacks certain dataset types, fill from Regency aggregate
        if ($regionLevel !== 'regency') {
            $regencyDatasets = DemographicDataset::published()
                ->forRegion('regency', DemographicDataset::REGENCY_CODE)
                ->forPeriod($selectedYear, $selectedSemester)
                ->get();
            
            $existingTypes = $rawDatasets->pluck('type')->toArray();
            foreach ($regencyDatasets as $rDs) {
                if (!in_array($rDs->type, $existingTypes)) {
                    $rawDatasets->push($rDs);
                }
            }
        }

        $dataAvailable = $rawDatasets->isNotEmpty();

        $charts = [];
        foreach ($rawDatasets as $ds) {
            $charts[$ds->type] = $ds->data_json;
        }

        // ── Summary — ONLY from real uploaded data ────────────────────────
        $totalPopulation = 0;
        $totalMale       = 0;
        $totalFemale     = 0;
        $totalHouseholds = 0;

        if ($dataAvailable) {
            $popChart = $charts['population'] ?? null;
            if ($popChart && is_array($popChart)) {
                $totalMale       = isset($popChart['male'])   && is_array($popChart['male'])   ? array_sum($popChart['male'])   : 0;
                $totalFemale     = isset($popChart['female']) && is_array($popChart['female']) ? array_sum($popChart['female']) : 0;
                $totalPopulation = $popChart['total'] ?? ($totalMale + $totalFemale);

                // Auto-derive lansia, productive_age, and dependency_ratio if not explicitly present
                $cats   = $popChart['categories'] ?? [];
                $males   = $popChart['male'] ?? [];
                $females = $popChart['female'] ?? [];

                $youngTotal    = 0; // 0-14 Thn (Belum Produktif)
                $prodTotal     = 0; // 15-64 Thn (Usia Produktif)
                $seniorTotal   = 0; // 65+ Thn (Tidak Produktif / Senja)
                $lansia60Total = 0; // 60+ Thn (UU Lansia)

                foreach ($cats as $idx => $cat) {
                    $tot = ($males[$idx] ?? 0) + ($females[$idx] ?? 0);
                    if (in_array($cat, ['0-4', '5-9', '10-14'])) {
                        $youngTotal += $tot;
                    } elseif (in_array($cat, ['15-19', '20-24', '25-29', '30-34', '35-39', '40-44', '45-49', '50-54', '55-59', '60-64'])) {
                        $prodTotal += $tot;
                        if ($cat === '60-64') {
                            $lansia60Total += $tot;
                        }
                    } elseif (in_array($cat, ['65-69', '70-74', '75+', '65+'])) {
                        $seniorTotal += $tot;
                        $lansia60Total += $tot;
                    } elseif ($cat === '60-64' || $cat === '60+') {
                        $lansia60Total += $tot;
                    }
                }

                $totPop = $youngTotal + $prodTotal + $seniorTotal;

                if (!isset($charts['productive_age']) && $prodTotal > 0) {
                    $charts['productive_age'] = [
                        'total'      => $prodTotal,
                        'percentage' => $totPop > 0 ? round(($prodTotal / $totPop) * 100, 1) : 0,
                    ];
                }

                if (!isset($charts['lansia']) && $lansia60Total > 0) {
                    $charts['lansia'] = [
                        'total'      => $lansia60Total,
                        'percentage' => $totPop > 0 ? round(($lansia60Total / $totPop) * 100, 1) : 0,
                    ];
                }

                if (!isset($charts['dependency_ratio']) && $prodTotal > 0) {
                    $nonProd = $youngTotal + $seniorTotal;
                    $ratio   = round(($nonProd / $prodTotal) * 100, 2);
                    $charts['dependency_ratio'] = [
                        'ratio'          => $ratio,
                        'non_productive' => $nonProd,
                        'productive'     => $prodTotal,
                    ];
                }
            }

            $householdsChart = $charts['households'] ?? null;
            if ($householdsChart && isset($householdsChart['total'])) {
                $totalHouseholds = $householdsChart['total'];
            } elseif (isset($householdsChart['items'])) {
                $totalHouseholds = array_sum(array_column($householdsChart['items'], 'value'));
            }
        }

        // ── District population summary heatmap ───────────────────────────
        $districtPopDatasets = DemographicDataset::published()
            ->where('region_level', 'district')
            ->forPeriod($selectedYear, $selectedSemester)
            ->where('type', 'population')
            ->get()
            ->keyBy('region_code');

        $heatmapData = $kecamatans->map(function ($k) use ($districtPopDatasets) {
            $ds = $districtPopDatasets->get($k->code);
            if ($ds && is_array($ds->data_json)) {
                $j      = $ds->data_json;
                $male   = isset($j['male'])   && is_array($j['male'])   ? array_sum($j['male'])   : 0;
                $female = isset($j['female']) && is_array($j['female']) ? array_sum($j['female']) : 0;
                return [
                    'id'        => $k->id,
                    'name'      => $k->name,
                    'code'      => $k->code,
                    'population'=> $j['total'] ?? ($male + $female),
                    'male'      => $male,
                    'female'    => $female,
                    'area_km2'  => $k->area_km2,
                    'hasData'   => true,
                ];
            }
            return [
                'id'        => $k->id,
                'name'      => $k->name,
                'code'      => $k->code,
                'population'=> null,
                'male'      => null,
                'female'    => null,
                'area_km2'  => $k->area_km2,
                'hasData'   => false,
            ];
        });

        return Inertia::render('Public/Statistics', [
            'kecamatans'          => $kecamatans,
            'desas'               => $desas,
            'dataAvailable'       => $dataAvailable,
            'availableYears'      => $availableYears,
            'availableSemesters'  => $availableSemesters,
            'selectedYear'        => $selectedYear,
            'selectedSemester'    => $selectedSemester,
            'selectedRegionLevel' => $regionLevel,
            'selectedRegionCode'  => $regionCode,
            'summary'             => [
                'total_population' => $totalPopulation,
                'total_male'       => $totalMale,
                'total_female'     => $totalFemale,
                'total_households' => $totalHouseholds,
                'total_kecamatan'  => $kecamatans->count(),
                'total_desa'       => $desas->count(),
            ],
            'charts'              => $charts,
            'heatmapData'         => $heatmapData,
            'typeLabels'          => DemographicDataset::TYPE_LABELS,
            'semesterLabels'      => DemographicDataset::SEMESTER_LABELS,
            'regionLevels'        => DemographicDataset::REGION_LEVELS,
            'regencyCode'         => DemographicDataset::REGENCY_CODE,
        ]);
    }
}
