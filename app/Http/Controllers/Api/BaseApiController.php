<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;

/**
 * @OA\Info(
 *     title="Dukcapil Dompu REST API Platform",
 *     version="1.0.0",
 *     description="Dokumentasi resmi REST API Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. API ini digunakan sebagai gerbang integrasi data kependudukan eksternal dan aplikasi internal pemerintah.",
 *     @OA\Contact(
 *         email="dukcapil@dompukab.go.id"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api/v1",
 *     description="API V1 Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-KEY",
 *     description="Masukkan Token API Key Anda di kolom value dengan format: dkp_..."
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token Bearer Sanctum untuk akses tertutup."
 * )
 */
class BaseApiController extends Controller
{
    use HasApiResponse;
}
