<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Profile\ProfileService;
use App\Services\Profile\OrganizationChartService;
use App\Services\Profile\OfficialDirectoryService;
use App\Services\Profile\OfficialProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfileApiController extends Controller
{
    #[OA\Get(
        path: '/v1/profile/sections',
        summary: 'Daftar seksi profil aktif',
        description: 'Mengambil semua seksi profil dinas yang aktif (Visi Misi, Sejarah, Tupoksi, dll).',
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Daftar seksi profil aktif'),
        ]
    )]
    public function sections(ProfileService $profileService): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $profileService->getActiveSections(),
        ]);
    }

    #[OA\Get(
        path: '/v1/profile/tree',
        summary: 'Struktur bagan organisasi',
        description: 'Mengambil hierarki bagan organisasi Dinas Kependudukan dan Pencatatan Sipil dalam struktur tree berjenjang.',
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Struktur organisasi'),
        ]
    )]
    public function tree(OrganizationChartService $chartService): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $chartService->getTree(),
        ]);
    }

    #[OA\Get(
        path: '/v1/profile/officials',
        summary: 'Direktori pejabat dinas',
        description: 'Mengambil daftar pejabat dengan filter pencarian nama dan unit kerja/bidang, dilengkapi paginasi.',
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', description: 'Cari nama pejabat', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'department', in: 'query', description: 'Filter unit kerja/bidang', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Jumlah data per halaman', required: false, schema: new OA\Schema(type: 'integer', example: 12)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar pejabat'),
        ]
    )]
    public function officials(Request $request, OfficialDirectoryService $officialService): JsonResponse
    {
        $filters = $request->only(['search', 'department']);
        $officials = $officialService->getOfficials($filters, $request->input('per_page', 12));

        return response()->json([
            'status' => 'success',
            'data' => $officials,
        ]);
    }

    #[OA\Get(
        path: '/v1/profile/officials/{identifier}',
        summary: 'Detail profil pejabat',
        description: 'Mengambil profil lengkap seorang pejabat berdasarkan ID atau slug jabatan.',
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', description: 'ID atau slug pejabat', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail profil pejabat'),
            new OA\Response(response: 404, description: 'Pejabat tidak ditemukan'),
        ]
    )]
    public function officialDetail(string $identifier, OfficialProfileService $profileService): JsonResponse
    {
        $official = $profileService->getOfficialDetail($identifier);

        if (!$official) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Pejabat tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $official,
        ]);
    }
}
