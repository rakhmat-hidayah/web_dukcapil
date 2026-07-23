<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileSectionSetting extends Model
{
    protected $fillable = [
        'section_id',
        'layout_type',
        'bg_color',
        'animation_type',
        'visible_desktop',
        'visible_tablet',
        'visible_mobile',
        'content_data',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'visible_desktop' => 'boolean',
        'visible_tablet' => 'boolean',
        'visible_mobile' => 'boolean',
        'content_data' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProfileSection::class, 'section_id');
    }
}
