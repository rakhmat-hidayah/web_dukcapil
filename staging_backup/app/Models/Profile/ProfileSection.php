<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProfileSection extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function setting(): HasOne
    {
        return $this->hasOne(ProfileSectionSetting::class, 'section_id');
    }
}
