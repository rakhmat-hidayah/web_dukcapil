<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialHistory extends Model
{
    protected $table = 'official_histories';

    protected $fillable = [
        'official_id',
        'position_title',
        'organization',
        'start_year',
        'end_year',
        'description',
        'sort_order',
    ];

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'official_id');
    }
}
