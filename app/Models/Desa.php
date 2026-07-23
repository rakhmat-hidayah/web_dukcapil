<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends Model
{
    protected $fillable = [
        'kecamatan_id', 'name', 'code', 'type',
        'population_total', 'male_count', 'female_count',
        'dusun_count', 'sort_order',
    ];

    protected $casts = [
        'population_total' => 'integer',
        'male_count'       => 'integer',
        'female_count'     => 'integer',
        'dusun_count'      => 'integer',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function dusuns(): HasMany
    {
        return $this->hasMany(Dusun::class)->orderBy('sort_order');
    }
}
