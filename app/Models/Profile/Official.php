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

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'show_phone' => 'boolean',
        'show_email' => 'boolean',
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

    public function scopeOrderByHierarchy($query)
    {
        return $query->orderByRaw("
            CASE 
                WHEN position_title LIKE '%Kepala Dinas%' AND position_title NOT LIKE '%Bidang%' THEN 1
                WHEN position_title LIKE '%Sekretaris%' THEN 2
                WHEN position_title LIKE '%Pendaftaran Penduduk%' THEN 3
                WHEN position_title LIKE '%Pencatatan Sipil%' THEN 4
                WHEN position_title LIKE '%Pengelolaan Informasi%' OR position_title LIKE '%PIAK%' THEN 5
                WHEN position_title LIKE '%Pemanfaatan Data%' THEN 6
                WHEN position_title LIKE '%Keuangan%' THEN 7
                WHEN position_title LIKE '%Umum%' OR position_title LIKE '%Kepegawaian%' THEN 8
                WHEN position_title LIKE '%Kasubag%' OR position_title LIKE '%Subbag%' THEN 9
                WHEN position_title LIKE '%Plt.%' OR position_title LIKE '%Kabid%' THEN 10
                ELSE 20
            END ASC
        ")->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
