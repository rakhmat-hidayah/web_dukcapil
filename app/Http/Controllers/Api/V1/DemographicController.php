<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DemographicDataset;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Demographics", description="Demographic statistics & administrative hierarchy")
 */
class DemographicController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/v1/demographics/summary",
     *   summary="Get population summary KPIs",
     *   tags={"Demographics"},
     *   @OA\Response(response=200, description="Summary totals")
     * )
     */
    public function summary(): JsonResponse
    {
        $kecamatans      = Kecamatan::all();
        $totalPopulation = $kecamatans->sum('population_total');
        $totalMale       = $kecamatans->sum('male_count');
        $totalFemale     = $kecamatans->sum('female_count');

        return response()->json([
            'success' => true,
            'data' => [
                'total_population'  => $totalPopulation,
                'total_male'        => $totalMale,
                'total_female'      => $totalFemale,
                'total_kecamatan'   => $kecamatans->count(),
                'total_desa'        => Desa::count(),
                'sex_ratio'         => $totalFemale > 0 ? round(($totalMale / $totalFemale) * 100, 2) : null,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/demographics/kecamatans",
     *   summary="List all kecamatan districts with population data",
     *   tags={"Demographics"},
     *   @OA\Response(response=200, description="Kecamatan list")
     * )
     */
    public function kecamatans(): JsonResponse
    {
        $data = Kecamatan::orderBy('sort_order')
            ->withCount('desas')
            ->get()
            ->map(fn ($k) => [
                'id'               => $k->id,
                'name'             => $k->name,
                'code'             => $k->code,
                'ibukota'          => $k->ibukota,
                'area_km2'         => $k->area_km2,
                'population_total' => $k->population_total,
                'male_count'       => $k->male_count,
                'female_count'     => $k->female_count,
                'desa_count'       => $k->desas_count,
                'sex_ratio'        => $k->sex_ratio,
            ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/demographics/datasets",
     *   summary="List published demographic datasets",
     *   tags={"Demographics"},
     *   @OA\Parameter(name="year", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Datasets list")
     * )
     */
    public function datasets(Request $request): JsonResponse
    {
        $query = DemographicDataset::published()->with('kecamatan:id,name');

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $datasets = $query->orderByDesc('year')->get()->map(fn ($d) => [
            'id'           => $d->id,
            'title'        => $d->title,
            'year'         => $d->year,
            'type'         => $d->type,
            'type_label'   => $d->type_label,
            'kecamatan'    => $d->kecamatan ? ['id' => $d->kecamatan->id, 'name' => $d->kecamatan->name] : null,
            'published_at' => $d->published_at?->toDateString(),
        ]);

        return response()->json(['success' => true, 'data' => $datasets]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/demographics/chart/{type}",
     *   summary="Get chart-ready JSON data for a given dataset type",
     *   tags={"Demographics"},
     *   @OA\Parameter(name="type", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Parameter(name="year", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Chart data")
     * )
     */
    public function chartData(Request $request, string $type): JsonResponse
    {
        $year = $request->integer('year', 0);

        $query = DemographicDataset::published()->where('type', $type);

        if ($year > 0) {
            $query->where('year', $year);
        } else {
            $query->orderByDesc('year');
        }

        $dataset = $query->first();

        if (!$dataset) {
            return response()->json(['success' => false, 'message' => 'Data tidak tersedia.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'title'      => $dataset->title,
                'year'       => $dataset->year,
                'type'       => $dataset->type,
                'type_label' => $dataset->type_label,
                'chart_data' => $dataset->data_json,
            ],
        ]);
    }
}
