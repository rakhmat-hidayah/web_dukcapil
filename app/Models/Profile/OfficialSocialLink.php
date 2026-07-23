<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialSocialLink extends Model
{
    protected $fillable = [
        'official_id',
        'platform',
        'url',
        'handle',
    ];

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'official_id');
    }
}
