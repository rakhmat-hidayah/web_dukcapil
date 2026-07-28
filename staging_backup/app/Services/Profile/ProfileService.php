<?php

namespace App\Services\Profile;

use App\Models\Profile\ProfileSection;
use Illuminate\Support\Facades\Cache;

class ProfileService
{
    /**
     * Get all active profile sections with settings for frontend rendering.
     */
    public function getActiveSections(): array
    {
        return Cache::remember('active_profile_sections', 300, function () {
            return ProfileSection::with('setting')
                ->where('is_enabled', true)
                ->orderBy('sort_order', 'asc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Clear profile section cache.
     */
    public function clearCache(): void
    {
        Cache::forget('active_profile_sections');
    }
}
