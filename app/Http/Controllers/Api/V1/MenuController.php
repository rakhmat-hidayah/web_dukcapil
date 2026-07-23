<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;

class MenuController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/menus/{location}",
     *     summary="Menu navigasi berdasarkan lokasi",
     *     description="Mengambil struktur menu navigasi berjenjang (nested header/footer) berdasarkan lokasi penempatan.",
     *     tags={"Menus"},
     *     @OA\Parameter(name="location", in="path", description="Lokasi penempatan menu (header, footer, sidebar)", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil menu navigasi"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Menu tidak ditemukan"
     *     )
     * )
     */
    public function show(string $location): JsonResponse
    {
        $menu = Menu::where('is_active', true)
            ->where('location', $location)
            ->first();

        if (!$menu) {
            return $this->apiError('Menu untuk lokasi tersebut tidak ditemukan.', 404);
        }

        // Return nested structures
        $items = $menu->items()->with('children')->get();

        return $this->apiSuccess($items, 'Struktur menu berhasil diambil');
    }
}
