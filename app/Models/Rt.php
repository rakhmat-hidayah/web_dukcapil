<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rt extends Model
{
    protected $table = 'rts';

    protected $fillable = ['rw_id', 'name', 'number'];

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class);
    }
}
