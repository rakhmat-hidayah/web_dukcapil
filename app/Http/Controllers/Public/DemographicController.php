<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DemographicDataset;
use App\Models\Desa;
use App\Models\Kecamatan;
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

        // ── Region filter ─────────────────────────────────────────────────
        $regionLevel = $request->input('region_level', 'regency');
        $regionCode  = $request->input('region_code', DemographicDataset::REGENCY_CODE);

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

        $selectedYear = $request->integer('year', $availableYears->first() ?? date('Y'));

        $availableSemesters = DemographicDataset::published()
            ->where('region_level', $regionLevel)
            ->where('region_code', $regionCode)
            ->where('year', $selectedYear)
            ->distinct()->orderBy('semester')->pluck('semester');

        $requestedSemester = $request->integer('semester', $availableSemesters->last() ?? 1);
        $selectedSemester  = $availableSemesters->contains($requestedSemester)
            ? $requestedSemester
            : ($availableSemesters->last() ?? $requestedSemester);

        // ── Load ONLY real datasets for the selected region + period ──────
        $rawDatasets = DemographicDataset::published()
            ->forRegion($regionLevel, $regionCode)
            ->forPeriod($selectedYear, $selectedSemester)
            ->get();

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
