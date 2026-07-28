<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PageController extends BaseApiController
{
    #[OA\Get(
        path: '/v1/pages/{slug}',
        summary: 'Detail halaman profil / dinamis',
        description: 'Mengambil konten lengkap halaman dinamis (Profil Dinas, Visi Misi, Standar Pelayanan, dll) berdasarkan slug.',
        tags: ['Pages'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', description: 'Slug URL halaman', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Halaman ditemukan'),
            new OA\Response(response: 404, description: 'Halaman tidak ditemukan'),
        ]
    )]
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
