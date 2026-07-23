<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/pages/{slug}",
     *     summary="Detail halaman profil / dinamis",
     *     description="Mengambil konten lengkap halaman dinamis (Profil Dinas, Visi Misi, Standar Pelayanan, dll) berdasarkan slug.",
     *     tags={"Pages"},
     *     @OA\Parameter(name="slug", in="path", description="Slug URL halaman", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Halaman ditemukan"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Halaman tidak ditemukan"
     *     )
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (!$page) {
            return $this->apiError('Halaman tidak ditemukan.', 404);
        }

        return $this->apiSuccess($page, 'Halaman berhasil diambil');
    }
}
