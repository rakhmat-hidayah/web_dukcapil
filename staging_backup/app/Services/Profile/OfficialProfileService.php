<?php

namespace App\Services\Profile;

use App\Models\Profile\Official;

class OfficialProfileService
{
    /**
     * Get complete official details by ID or slug.
     */
    public function getOfficialDetail(string $identifier): ?Official
    {
        return Official::with([
            'educations',
            'histories',
            'achievements',
            'socialLinks',
            'documents',
        ])
        ->where('id', $identifier)
        ->orWhere('slug', $identifier)
        ->first();
    }
}
