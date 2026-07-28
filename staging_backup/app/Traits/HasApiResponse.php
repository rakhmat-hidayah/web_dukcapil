<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HasApiResponse
{
    /**
     * Return a standardized success JSON response.
     */
    protected function apiSuccess($data = [], string $message = 'Operasi berhasil', int $statusCode = 200, array $meta = [], array $links = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'links' => $links,
        ], $statusCode);
    }

    /**
     * Return a standardized error JSON response.
     */
    protected function apiError(string $message = 'Terjadi kesalahan', int $statusCode = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
