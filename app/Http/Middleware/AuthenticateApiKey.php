<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Traits\HasApiResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    use HasApiResponse;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        // 1. Resolve API Key from Header or Query
        $key = $request->header('X-API-KEY') ?: $request->query('api_key');

        if (!$key) {
            return $this->apiError('API Key tidak disediakan. Harap sertakan header X-API-KEY.', 401);
        }

        // 2. Lookup key in DB
        $apiKey = ApiKey::where('api_key', $key)->first();

        if (!$apiKey || !$apiKey->isValid()) {
            return $this->apiError('API Key tidak valid atau telah dinonaktifkan / kedaluwarsa.', 401);
        }

        // 3. Optional: Permission validation
        if ($permission && !$apiKey->hasPermission($permission)) {
            return $this->apiError('API Key tidak memiliki hak akses (permission) untuk mengakses data ini.', 403);
        }

        // 4. Bind the model to request attributes for log audit logs to access it
        $request->attributes->set('api_key_model', $apiKey);

        return $next($request);
    }
}
