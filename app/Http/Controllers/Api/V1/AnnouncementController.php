<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/announcements",
     *     summary="Daftar pengumuman aktif",
     *     description="Mengambil daftar maklumat resmi, pengumuman darurat, popup, dan running text ticker yang masih aktif.",
     *     tags={"Announcements"},
     *     @OA\Parameter(name="type", in="query", description="Filter tipe (popup, ticker, feed)", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil pengumuman"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc');

        if ($request->filled('type')) {
            if ($request->type === 'popup') {
                $query->where('is_popup', true);
            } elseif ($request->type === 'ticker') {
                $query->where('is_ticker', true);
            } elseif ($request->type === 'feed') {
                $query->where('is_popup', false)->where('is_ticker', false);
            }
        }

        $announcements = $query->get();

        return $this->apiSuccess($announcements, 'Daftar pengumuman aktif berhasil diambil');
    }
}
