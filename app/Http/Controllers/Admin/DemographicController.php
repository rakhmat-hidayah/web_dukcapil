<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemographicDataset;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DemographicController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // HIERARCHY (Kecamatan & Desa Management)
    // ─────────────────────────────────────────────────────────

    public function hierarchy(): InertiaResponse
    {
        $kecamatans = Kecamatan::with(['desas' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Demographics/Hierarchy', [
            'kecamatans' => $kecamatans,
        ]);
    }

    public function storeKecamatan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'code'             => 'nullable|string|max:20|unique:kecamatans,code',
            'ibukota'          => 'nullable|string|max:150',
            'area_km2'         => 'nullable|numeric|min:0',
            'population_total' => 'nullable|integer|min:0',
            'male_count'       => 'nullable|integer|min:0',
            'female_count'     => 'nullable|integer|min:0',
            'sort_order'       => 'nullable|integer|min:0',
            'notes'            => 'nullable|string',
        ]);

        Kecamatan::create($data);

        return back()->with('success', 'Kecamatan berhasil ditambahkan.');
    }

    public function updateKecamatan(Request $request, Kecamatan $kecamatan): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'code'             => 'nullable|string|max:20|unique:kecamatans,code,' . $kecamatan->id,
            'ibukota'          => 'nullable|string|max:150',
            'area_km2'         => 'nullable|numeric|min:0',
            'population_total' => 'nullable|integer|min:0',
            'male_count'       => 'nullable|integer|min:0',
            'female_count'     => 'nullable|integer|min:0',
            'sort_order'       => 'nullable|integer|min:0',
            'notes'            => 'nullable|string',
        ]);

        $kecamatan->update($data);

        return back()->with('success', 'Data kecamatan diperbarui.');
    }

    public function destroyKecamatan(Kecamatan $kecamatan): RedirectResponse
    {
        $kecamatan->delete();
        return back()->with('success', 'Kecamatan dihapus.');
    }

    // Desa CRUD
    public function storeDesa(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kecamatan_id'     => 'required|exists:kecamatans,id',
            'name'             => 'required|string|max:150',
            'code'             => 'nullable|string|max:20|unique:desas,code',
            'type'             => 'required|in:desa,kelurahan',
            'population_total' => 'nullable|integer|min:0',
            'male_count'       => 'nullable|integer|min:0',
            'female_count'     => 'nullable|integer|min:0',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        Desa::create($data);

        // Update desa_count on parent kecamatan
        $data['kecamatan_id'] && Kecamatan::find($data['kecamatan_id'])
            ?->update(['desa_count' => Desa::where('kecamatan_id', $data['kecamatan_id'])->count()]);

        return back()->with('success', 'Desa/Kelurahan berhasil ditambahkan.');
    }

    public function updateDesa(Request $request, Desa $desa): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'code'             => 'nullable|string|max:20|unique:desas,code,' . $desa->id,
            'type'             => 'required|in:desa,kelurahan',
            'population_total' => 'nullable|integer|min:0',
            'male_count'       => 'nullable|integer|min:0',
            'female_count'     => 'nullable|integer|min:0',
            'sort_order'       => 'nullable|integer|min:0',
        ]);

        $desa->update($data);

        return back()->with('success', 'Data desa diperbarui.');
    }

    public function destroyDesa(Desa $desa): RedirectResponse
    {
        $kecamatanId = $desa->kecamatan_id;
        $desa->delete();

        Kecamatan::find($kecamatanId)
            ?->update(['desa_count' => Desa::where('kecamatan_id', $kecamatanId)->count()]);

        return back()->with('success', 'Desa/Kelurahan dihapus.');
    }

    // ─────────────────────────────────────────────────────────
    // DATASETS
    // ─────────────────────────────────────────────────────────

    public function datasets(Request $request): InertiaResponse
    {
        $query = DemographicDataset::with('kecamatan')
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->orderBy('type');

        if ($request->filled('region_level')) {
            $query->where('region_level', $request->input('region_level'));
        }
        if ($request->filled('region_code')) {
            $query->where('region_code', $request->input('region_code'));
        }
        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->integer('semester'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $datasets       = $query->paginate(20)->withQueryString();
        $kecamatans     = Kecamatan::orderBy('code')->get(['id', 'name', 'code']);
        $desas          = Desa::orderBy('name')->get(['id', 'kecamatan_id', 'name', 'code']);
        $availableYears = DemographicDataset::distinct()->orderByDesc('year')->pluck('year');

        $selectedYearForSem = $request->filled('year') ? $request->integer('year') : null;
        $semQuery = DemographicDataset::query();
        if ($selectedYearForSem) {
            $semQuery->where('year', $selectedYearForSem);
        }
        $availableSemesters = $semQuery->distinct()->orderBy('semester')->pluck('semester');

        return Inertia::render('Demographics/Datasets', [
            'datasets'           => $datasets,
            'kecamatans'         => $kecamatans,
            'desas'              => $desas,
            'typeLabels'         => DemographicDataset::TYPE_LABELS,
            'regionLevels'       => DemographicDataset::REGION_LEVELS,
            'regencyCode'        => DemographicDataset::REGENCY_CODE,
            'availableYears'     => $availableYears,
            'availableSemesters' => $availableSemesters,
            'filters'            => $request->only(['region_level', 'region_code', 'year', 'semester', 'type']),
        ]);
    }

    public function storeDataset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'region_level' => 'required|in:regency,district,village',
            'region_code'  => 'required|string|max:20',
            'title'        => 'required|string|max:255',
            'year'         => 'required|integer|min:2000|max:2100',
            'semester'     => 'required|integer|in:1,2',
            'type'         => 'required|string|max:100',
            'status'       => 'required|in:draft,published',
            'notes'        => 'nullable|string',
            'data_json'    => 'nullable|string',
            'file'         => 'nullable|file|mimes:pdf,xlsx,xls|max:20480',
        ]);

        $dataJson = null;
        if (!empty($data['data_json'])) {
            $decoded  = json_decode($data['data_json'], true);
            $dataJson = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        $filePath = null;
        $fileSize = 0;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $filePath = $file->store('demographics', 'public');
            $fileSize = $file->getSize();
            $fileType = $file->getClientOriginalExtension();
        }

        // Resolve kecamatan_id for legacy FK (district level only)
        $kecamatanId = null;
        if ($data['region_level'] === 'district') {
            $kec = Kecamatan::where('code', $data['region_code'])->first();
            $kecamatanId = $kec?->id;
        }

        DemographicDataset::create([
            'kecamatan_id' => $kecamatanId,
            'region_level' => $data['region_level'],
            'region_code'  => $data['region_code'],
            'title'        => $data['title'],
            'year'         => $data['year'],
            'semester'     => $data['semester'],
            'type'         => $data['type'],
            'status'       => $data['status'],
            'notes'        => $data['notes'] ?? null,
            'data_json'    => $dataJson,
            'file_path'    => $filePath,
            'file_size'    => $fileSize,
            'file_type'    => $fileType,
            'published_at' => $data['status'] === 'published' ? now() : null,
        ]);

        return back()->with('success', 'Dataset berhasil disimpan.');
    }

    public function updateDataset(Request $request, DemographicDataset $dataset): RedirectResponse
    {
        $data = $request->validate([
            'region_level' => 'required|in:regency,district,village',
            'region_code'  => 'required|string|max:20',
            'title'        => 'required|string|max:255',
            'year'         => 'required|integer|min:2000|max:2100',
            'semester'     => 'required|integer|in:1,2',
            'type'         => 'required|string|max:100',
            'status'       => 'required|in:draft,published',
            'notes'        => 'nullable|string',
            'data_json'    => 'nullable|string',
        ]);

        $dataJson = $dataset->data_json;
        if (!empty($data['data_json'])) {
            $decoded  = json_decode($data['data_json'], true);
            $dataJson = json_last_error() === JSON_ERROR_NONE ? $decoded : $dataJson;
        }

        // Resolve kecamatan_id for legacy FK
        $kecamatanId = $dataset->kecamatan_id;
        if ($data['region_level'] === 'district') {
            $kec         = Kecamatan::where('code', $data['region_code'])->first();
            $kecamatanId = $kec?->id;
        } elseif ($data['region_level'] === 'regency') {
            $kecamatanId = null;
        }

        $dataset->update([
            'kecamatan_id' => $kecamatanId,
            'region_level' => $data['region_level'],
            'region_code'  => $data['region_code'],
            'title'        => $data['title'],
            'year'         => $data['year'],
            'semester'     => $data['semester'],
            'type'         => $data['type'],
            'status'       => $data['status'],
            'notes'        => $data['notes'] ?? null,
            'data_json'    => $dataJson,
            'published_at' => $data['status'] === 'published' && !$dataset->published_at ? now() : $dataset->published_at,
        ]);

        return back()->with('success', 'Dataset diperbarui.');
    }

    public function destroyDataset(DemographicDataset $dataset): RedirectResponse
    {
        if ($dataset->file_path) {
            Storage::disk('public')->delete($dataset->file_path);
        }
        $dataset->delete();
        return back()->with('success', 'Dataset dihapus.');
    }

    // ─────────────────────────────────────────────────────────
    // DASHBOARD (Admin Statistics)
    // ─────────────────────────────────────────────────────────

    public function dashboard(Request $request): InertiaResponse
    {
        $kecamatans     = Kecamatan::orderBy('code')->get(['id', 'name', 'code', 'area_km2']);
        $desas          = Desa::orderBy('name')->get(['id', 'kecamatan_id', 'name', 'code']);
        $totalKecamatan = $kecamatans->count();
        $totalDesa      = Desa::count();

        // ── Region filter ─────────────────────────────────────────────────
        $regionLevel = $request->input('region_level', 'regency');
        $regionCode  = $request->input('region_code',  DemographicDataset::REGENCY_CODE);

        // ── Period filter — based on data available for this region ────────
        $availableYears = DemographicDataset::published()
            ->where('region_level', $regionLevel)
            ->where('region_code', $regionCode)
            ->distinct()->orderByDesc('year')->pluck('year');

        // Fallback: if no data for this region, get any available years globally
        if ($availableYears->isEmpty()) {
            $availableYears = DemographicDataset::published()
                ->distinct()->orderByDesc('year')->pluck('year');
        }

        $selectedYear = (int) $request->input('year', $availableYears->first() ?? date('Y'));

        $availableSemesters = DemographicDataset::published()
            ->where('region_level', $regionLevel)
            ->where('region_code', $regionCode)
            ->where('year', $selectedYear)
            ->distinct()->orderBy('semester')->pluck('semester');

        $requestedSemester = (int) $request->input('semester', $availableSemesters->last() ?? 1);
        $selectedSemester  = $availableSemesters->contains($requestedSemester)
            ? $requestedSemester
            : ($availableSemesters->last() ?? $requestedSemester);

        // ── Load real datasets for this region + period ───────────────────
        $rawDatasets = DemographicDataset::published()
            ->forRegion($regionLevel, $regionCode)
            ->forPeriod($selectedYear, $selectedSemester)
            ->get();

        $dataAvailable = $rawDatasets->isNotEmpty();

        $charts = [];
        foreach ($rawDatasets as $ds) {
            $charts[$ds->type] = $ds->data_json;
        }

        // ── Summary totals — ONLY from real data ──────────────────────────
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

        // ── Heatmap: districts with real population data for this period ─
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

        return Inertia::render('Demographics/Dashboard', [
            'kecamatans'         => $kecamatans,
            'desas'              => $desas,
            'heatmapData'        => $heatmapData,
            'dataAvailable'      => $dataAvailable,
            'summary'            => [
                'total_population' => $totalPopulation,
                'total_male'       => $totalMale,
                'total_female'     => $totalFemale,
                'total_households' => $totalHouseholds,
                'total_kecamatan'  => $totalKecamatan,
                'total_desa'       => $totalDesa,
            ],
            'availableYears'     => $availableYears,
            'availableSemesters' => $availableSemesters,
            'selectedYear'       => $selectedYear,
            'selectedSemester'   => $selectedSemester,
            'selectedRegionLevel'=> $regionLevel,
            'selectedRegionCode' => $regionCode,
            'charts'             => $charts,
            'typeLabels'         => DemographicDataset::TYPE_LABELS,
            'semesterLabels'     => DemographicDataset::SEMESTER_LABELS,
            'regionLevels'       => DemographicDataset::REGION_LEVELS,
            'regencyCode'        => DemographicDataset::REGENCY_CODE,
        ]);
    }
}
