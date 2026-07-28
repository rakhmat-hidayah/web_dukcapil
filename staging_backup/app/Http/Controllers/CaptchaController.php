<?php

namespace App\Http\Controllers;

use App\Services\CaptchaService;
use Illuminate\Http\JsonResponse;

class CaptchaController extends Controller
{
    /**
     * Get a new math captcha.
     */
    public function math(): JsonResponse
    {
        return response()->json(CaptchaService::generateMath());
    }

    /**
     * Get a new image captcha.
     */
    public function image(): JsonResponse
    {
        return response()->json([
            'image' => CaptchaService::generateImage()
        ]);
    }
}
