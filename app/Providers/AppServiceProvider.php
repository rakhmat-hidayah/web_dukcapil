<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\ApiSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define standard API Rate Limiter resolving limits dynamically
        RateLimiter::for('api', function (Request $request) {
            // 1. Resolve API Key if present
            $key = $request->header('X-API-KEY') ?: $request->query('api_key');
            
            if ($key) {
                // Find matching key (uncached, but db lookup indexed on api_key is very fast)
                $apiKey = ApiKey::where('api_key', $key)->first();
                if ($apiKey && $apiKey->isValid()) {
                    // Check if internal system (unlimited)
                    if ($apiKey->client_name === 'Internal System') {
                        return Limit::none();
                    }
                    // Limit per hour for registered partners
                    return Limit::perHour($apiKey->rate_limit_per_hour)->by($key);
                }
            }

            // 2. Default Public API Limiter (cached configs)
            $settings = ApiSetting::getAllCached();
            $publicLimit = isset($settings['api_rate_limit_public']) ? (int) $settings['api_rate_limit_public'] : 100;

            return Limit::perHour($publicLimit)->by($request->ip());
        });
    }
}
