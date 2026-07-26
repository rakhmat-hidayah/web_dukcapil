<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DemographicDataset;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Demographics', description: 'Statistik kependudukan dan hierarki wilayah administratif')]
class DemographicController extends Controller
{
    #[OA\Get(
        path: '/v1/demographics/summary',
        summary: 'Ringkasan KPI data penduduk',
        description: 'Mengambil total keseluruhan penduduk, pemilahan gender, jumlah kecamatan dan desa, serta sex ratio Kabupaten Dompu.',
        tags: ['Demographics'],
        responses: [
            new OA\Response(response: 200, description: 'Data ringkasan populasi'),
        ]
    )]
    public function summary(): JsonResponse
    {
        $kecamatans      = Kecamatan::all();
        $totalPopulation = $kecamatans->sum('population_total');
        $totalMale       = $kecamatans->sum('male_count');
        $totalFemale     = $kecamatans->sum('female_count');

        return response()->json([
            'success' => true,
            'data'    => [
                'total_population' => $totalPopulation,
                'total_male'       => $totalMale,
                'total_female'     => $totalFemale,
                'total_kecamatan'  => $kecamatans->count(),
                'total_desa'       => Desa::count(),
                'sex_ratio'        => $totalFemale > 0 ? round(($totalMale / $totalFemale) * 100, 2) : null,
            ],
        ]);
    }

    #[OA\Get(
        path: '/v1/demographics/kecamatans',
        summary: 'Daftar kecamatan beserta data penduduk',
        description: 'Mengambil semua kecamatan di Kabupaten Dompu lengkap dengan data jumlah penduduk, laki-laki, perempuan, dan jumlah desa.',
        tags: ['Demographics'],
        responses: [
            new OA\Response(response: 200, description: 'Daftar kecamatan'),
        ]
    )]
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

    #[OA\Get(
        path: '/v1/demographics/datasets',
        summary: 'Daftar dataset demografis yang dipublikasikan',
        description: 'Mengambil daftar dataset kependudukan yang tersedia, dapat difilter berdasarkan tahun dan tipe.',
        tags: ['Demographics'],
        parameters: [
            new OA\Parameter(name: 'year', in: 'query', description: 'Filter tahun dataset', required: false, schema: new OA\Schema(type: 'integer', example: 2024)),
            new OA\Parameter(name: 'type', in: 'query', description: 'Filter tipe dataset', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar dataset'),
        ]
    )]
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

    #[OA\Get(
        path: '/v1/demographics/chart/{type}',
        summary: 'Data chart JSON untuk tipe dataset tertentu',
        description: 'Mengambil data siap-render untuk chart berdasarkan tipe dataset, opsional difilter berdasarkan tahun.',
        tags: ['Demographics'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'path', description: 'Tipe dataset (contoh: age_group, education, job)', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'year', in: 'query', description: 'Filter tahun (opsional, default: terbaru)', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Data chart berhasil diambil'),
            new OA\Response(response: 404, description: 'Data tidak tersedia untuk tipe ini'),
        ]
    )]
    public function chartData(Request $request, string $type): JsonResponse
    {
        $year  = $request->integer('year', 0);
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
