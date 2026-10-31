<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'subtitle', 'image', 'image_mobile', 'url', 'url_target',
        'type', 'button_text', 'is_active', 'sort_order', 'starts_at', 'ends_at',
    ];

    protected $appends = ['image_url', 'image_mobile_url'];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) return $this->image;
        $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $this->image), '/');
        return '/uploads/' . $clean;
    }

    public function getImageMobileUrlAttribute(): ?string
    {
        if (!$this->image_mobile) return null;
        if (str_starts_with($this->image_mobile, 'http://') || str_starts_with($this->image_mobile, 'https://')) return $this->image_mobile;
        $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $this->image_mobile), '/');
        return '/uploads/' . $clean;
    }
}
