<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class FaqController extends BaseApiController
{
    #[OA\Get(
        path: '/v1/faq',
        summary: 'Daftar tanya jawab FAQ',
        description: 'Mengambil seluruh daftar tanya jawab administrasi kependudukan.',
        tags: ['FAQ'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'query', description: 'Filter kategori FAQ', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil FAQ'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Faq::where('is_published', true)->orderBy('sort_order');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $faqs = $query->get();

        return $this->apiSuccess($faqs, 'Daftar tanya jawab FAQ berhasil diambil');
    }
}
