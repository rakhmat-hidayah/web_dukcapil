<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rw extends Model
{
    protected $table = 'rws';

    protected $fillable = ['dusun_id', 'name', 'number'];

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function rts(): HasMany
    {
        return $this->hasMany(Rt::class)->orderBy('number');
    }
}
