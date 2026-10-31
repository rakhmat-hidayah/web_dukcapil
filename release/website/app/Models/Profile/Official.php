<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Official extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'nip',
        'position_title',
        'rank_golongan',
        'department',
        'photo',
        'biography',
        'main_duties',
        'office_address',
        'phone',
        'show_phone',
        'email',
        'show_email',
        'office_hours',
        'status',
        'start_date',
        'end_date',
        'sort_order',
    ];

    protected $appends = ['photo_url'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'show_phone' => 'boolean',
        'show_email' => 'boolean',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) return null;

        $clean = ltrim(str_replace(['/storage/', 'storage/', '/uploads/', 'uploads/'], '', $this->photo), '/');
        return '/uploads/' . $clean;
    }

    public function scopeOrderByHierarchy($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(OfficialEducation::class, 'official_id')->orderBy('start_year', 'desc');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OfficialHistory::class, 'official_id')->orderBy('start_year', 'desc');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(OfficialAchievement::class, 'official_id')->orderBy('year', 'desc');
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(OfficialSocialLink::class, 'official_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OfficialDocument::class, 'official_id');
    }
}
