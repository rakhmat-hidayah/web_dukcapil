<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ApiSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestApiController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/terms",
     *     summary="Dapatkan Ketentuan Layanan (Terms of Service) API",
     *     description="Mengembalikan teks Ketentuan Layanan penggunaan data REST API Dukcapil Dompu dalam format Markdown.",
     *     tags={"Public API"},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil Ketentuan Layanan",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ketentuan layanan berhasil diambil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="terms", type="string", example="# Ketentuan Layanan...")
     *             )
     *         )
     *     )
     * )
     */
    public function terms(): JsonResponse
    {
        $settings = ApiSetting::getAllCached();
        $terms = $settings['api_terms_of_service'] ?? 'No terms defined yet.';

        return $this->apiSuccess(['terms' => $terms], 'Ketentuan layanan berhasil diambil');
    }

    /**
     * @OA\Get(
     *     path="/test-auth",
     *     summary="Uji coba otentikasi API Key",
     *     description="Endpoint proteksi untuk menguji validitas API Key yang dikirim di header X-API-KEY.",
     *     tags={"Protected API"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="API Key Valid",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Otentikasi API Key Berhasil"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="client", type="string", example="Dinas Kominfo Kabupaten Dompu"),
     *                 @OA\Property(property="rate_limit", type="integer", example=1500),
     *                 @OA\Property(property="scopes", type="array", @OA\Items(type="string", example="news"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="API Key Tidak Valid / Hilang"
     *     )
     * )
     */
    public function testAuth(Request $request): JsonResponse
    {
        $apiKeyModel = $request->attributes->get('api_key_model');

        return $this->apiSuccess([
            'client' => $apiKeyModel->client_name,
            'rate_limit' => $apiKeyModel->rate_limit_per_hour,
            'scopes' => $apiKeyModel->permissions,
        ], 'Otentikasi API Key Berhasil');
    }
}
