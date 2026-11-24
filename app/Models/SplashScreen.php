<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SplashScreen extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'content_type',
        'custom_content',
        'image_path',
        'button_label',
        'button_url',
        'button_external',
        'show_frequency',
        'start_date',
        'end_date',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'button_external' => 'boolean',
        'start_date'      => 'date',
        'end_date'        => 'date',
        'sort_order'      => 'integer',
    ];

    /**
     * Scope: hanya yang aktif dan dalam periode.
     */
    public function scopeCurrentlyActive($query)
    {
        $today = Carbon::today();

        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            });
    }

    /**
     * Apakah splash screen sedang aktif sekarang.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        $today = Carbon::today();
        if ($this->start_date && $this->start_date->gt($today)) {
            return false;
        }
        if ($this->end_date && $this->end_date->lt($today)) {
            return false;
        }
        return true;
    }

    /**
     * Label status untuk tampilan admin.
     */
    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) return 'Nonaktif';
        $today = Carbon::today();
        if ($this->start_date && $this->start_date->gt($today)) return 'Terjadwal';
        if ($this->end_date && $this->end_date->lt($today)) return 'Kedaluwarsa';
        return 'Aktif';
    }
}
