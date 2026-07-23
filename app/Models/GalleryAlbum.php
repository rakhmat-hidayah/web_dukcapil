<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'description', 'cover_image', 'type', 'is_published', 'sort_order'];

    protected $casts = ['is_published' => 'boolean'];

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }
}
