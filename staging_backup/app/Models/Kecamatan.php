<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $fillable = [
        'name', 'code', 'ibukota', 'area_km2',
        'population_total', 'male_count', 'female_count',
        'desa_count', 'sort_order', 'notes',
    ];

    protected $casts = [
        'area_km2'         => 'decimal:2',
        'population_total' => 'integer',
        'male_count'       => 'integer',
        'female_count'     => 'integer',
        'desa_count'       => 'integer',
    ];

    public function desas(): HasMany
    {
        return $this->hasMany(Desa::class)->orderBy('sort_order');
    }

    public function datasets(): HasMany
    {
        return $this->hasMany(DemographicDataset::class);
    }

    /** Sex ratio: males per 100 females */
    public function getSexRatioAttribute(): float
    {
        if ($this->female_count === 0) return 0;
        return round(($this->male_count / $this->female_count) * 100, 2);
    }
}
