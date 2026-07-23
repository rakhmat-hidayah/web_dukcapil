<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\GalleryAlbum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GalleryController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/gallery",
     *     summary="Daftar album galeri",
     *     description="Mengambil seluruh album foto dan video yang dipublikasikan.",
     *     tags={"Gallery"},
     *     @OA\Parameter(name="type", in="query", description="Filter tipe (photo, video)", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil album galeri"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = GalleryAlbum::where('is_published', true)->orderBy('sort_order');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $albums = $query->withCount('items')->get();

        return $this->apiSuccess($albums, 'Daftar album galeri berhasil diambil');
    }

    /**
     * @OA\Get(
     *     path="/gallery/{slug}",
     *     summary="Detail media album galeri",
     *     description="Mengambil detail album beserta isi media di dalamnya (foto/video/link youtube).",
     *     tags={"Gallery"},
     *     @OA\Parameter(name="slug", in="path", description="Slug URL album", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Album ditemukan"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Album tidak ditemukan"
     *     )
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $album = GalleryAlbum::where('is_published', true)
            ->where('slug', $slug)
            ->with('items')
            ->first();

        if (!$album) {
            return $this->apiError('Album galeri tidak ditemukan.', 404);
        }

        return $this->apiSuccess($album, 'Detail album galeri berhasil diambil');
    }
}
