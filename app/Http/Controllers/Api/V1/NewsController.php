<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NewsController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/news",
     *     summary="Daftar artikel berita",
     *     description="Mengambil daftar artikel berita resmi dengan filter kategori, kata kunci pencarian, dan paginasi.",
     *     tags={"News"},
     *     @OA\Parameter(name="search", in="query", description="Cari judul berita", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", description="Filter berdasarkan slug kategori", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", description="Nomor halaman", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil berita",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Daftar berita berhasil diambil")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = News::with(['category', 'author'])
            ->published()
            ->orderBy('published_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $news = $query->paginate(10);

        return $this->apiSuccess($news->items(), 'Daftar berita berhasil diambil', 200, [
            'current_page' => $news->currentPage(),
            'last_page' => $news->lastPage(),
            'per_page' => $news->perPage(),
            'total' => $news->total(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/news/{slug}",
     *     summary="Detail artikel berita",
     *     description="Mengambil artikel berita secara detail berdasarkan slug URL dan menambah view counter secara otomatis.",
     *     tags={"News"},
     *     @OA\Parameter(name="slug", in="path", description="Slug URL berita", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Detail berita ditemukan"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Berita tidak ditemukan"
     *     )
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $news = News::with(['category', 'author', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->first();

        if (!$news) {
            return $this->apiError('Artikel berita tidak ditemukan atau masih berstatus draft.', 404);
        }

        // Increment view count securely
        $news->incrementViews();

        return $this->apiSuccess($news, 'Detail berita berhasil diambil');
    }
}
