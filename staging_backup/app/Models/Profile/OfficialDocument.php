<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialDocument extends Model
{
    protected $fillable = [
        'official_id',
        'title',
        'file_path',
        'document_type',
        'year',
    ];

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'official_id');
    }
}
