<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

class DownloadController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/downloads",
     *     summary="Daftar berkas unduhan resmi",
     *     description="Mengambil daftar berkas, regulasi, dan formulir permohonan. Mengembalikan URL download sementara yang ditandatangani secara kriptografis (signed URL) demi keamanan.",
     *     tags={"Downloads"},
     *     @OA\Parameter(name="category", in="query", description="Filter slug kategori", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil daftar berkas"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Download::with('category')
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $downloads = $query->get()->map(function ($doc) {
            // Generate temporary signed URL valid for 30 minutes
            // Pointing to the secure streaming controller route
            $signedUrl = URL::temporarySignedRoute(
                'public.downloads.stream',
                now()->addMinutes(30),
                ['id' => $doc->id]
            );

            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'description' => $doc->description,
                'file_name' => $doc->file_name,
                'file_type' => $doc->file_type,
                'file_size' => $doc->file_size,
                'file_size_formatted' => $doc->fileSizeFormatted(),
                'document_number' => $doc->document_number,
                'document_date' => $doc->document_date ? $doc->document_date->format('Y-m-d') : null,
                'download_count' => $doc->download_count,
                'download_url' => $signedUrl, // Temporary URL. Signature expires in 30 mins
            ];
        });

        return $this->apiSuccess($downloads, 'Daftar berkas unduhan berhasil diambil');
    }
}
