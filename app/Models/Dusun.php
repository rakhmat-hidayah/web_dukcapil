<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dusun extends Model
{
    protected $fillable = ['desa_id', 'name', 'sort_order'];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function rws(): HasMany
    {
        return $this->hasMany(Rw::class)->orderBy('number');
    }
}
