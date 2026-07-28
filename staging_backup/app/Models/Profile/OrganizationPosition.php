<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationPosition extends Model
{
    protected $fillable = [
        'code',
        'title',
        'rank_level',
        'department',
        'description',
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(OrganizationNode::class, 'position_id');
    }
}
