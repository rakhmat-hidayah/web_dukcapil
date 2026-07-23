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
        'email',
        'office_hours',
        'status',
        'start_date',
        'end_date',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

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
        return $this->hasMany(OfficialDocument::class, 'official_id')->orderBy('year', 'desc');
    }

    public function orgNodes(): HasMany
    {
        return $this->hasMany(OrganizationNode::class, 'official_id');
    }
}
