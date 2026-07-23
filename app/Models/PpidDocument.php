<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpidDocument extends Model
{
    protected $fillable = [
        'category', 'subcategory', 'title', 'description',
        'file_path', 'file_url', 'file_type', 'file_size',
        'year', 'download_count', 'sort_order', 'is_published', 'created_by',
    ];

    protected $casts = [
        'is_published'   => 'boolean',
        'sort_order'     => 'integer',
        'download_count' => 'integer',
        'file_size'      => 'integer',
        'year'           => 'integer',
    ];

    public static array $categoryLabels = [
        'informasi_publik'   => 'Informasi Publik',
        'prosedur'           => 'Prosedur',
        'layanan_informasi'  => 'Layanan Informasi',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::$categoryLabels[$this->category] ?? $this->category;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '-';
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 2) . ' MB';
    }
}
