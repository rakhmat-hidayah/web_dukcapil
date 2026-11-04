<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\ApiSetting;

use Illuminate\Support\Facades\Gate;

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
        // Auto-synchronize misplaced media and broken DB paths on boot
        // Set Indonesian default locale for Carbon date formatting
        \Carbon\Carbon::setLocale('id');
        \Illuminate\Support\Carbon::setLocale('id');
        config(['app.locale' => 'id']);

        \App\Services\AutoMediaSync::sync();
        \App\Services\AutoRoleSync::sync();

        // Self-heal queue migrations if column is_active is pending in production
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('queue_service_documents') &&
                !\Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Queue auto-migrate warning: ' . $e->getMessage());
        }

        // Super Administrator has implicit full permissions override
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('Super Administrator') || $user->hasRole('Administrator Utama')) ? true : null;
        });
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
