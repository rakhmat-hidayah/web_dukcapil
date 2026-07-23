<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Profile\ProfileService;
use App\Services\Profile\OrganizationChartService;
use App\Services\Profile\OfficialDirectoryService;
use App\Services\Profile\OfficialProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileApiController extends Controller
{
    public function sections(ProfileService $profileService): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $profileService->getActiveSections(),
        ]);
    }

    public function tree(OrganizationChartService $chartService): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $chartService->getTree(),
        ]);
    }

    public function officials(Request $request, OfficialDirectoryService $officialService): JsonResponse
    {
        $filters = $request->only(['search', 'department']);
        $officials = $officialService->getOfficials($filters, $request->input('per_page', 12));

        return response()->json([
            'status' => 'success',
            'data' => $officials,
        ]);
    }

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
