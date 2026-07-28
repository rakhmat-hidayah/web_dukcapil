<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialAchievement extends Model
{
    protected $table = 'official_achievements';

    protected $fillable = [
        'official_id',
        'title',
        'issuer',
        'year',
        'description',
        'document_url',
        'sort_order',
    ];

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'official_id');
    }
}
