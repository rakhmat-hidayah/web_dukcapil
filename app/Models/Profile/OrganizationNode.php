<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationNode extends Model
{
    protected $fillable = [
        'position_id',
        'official_id',
        'parent_id',
        'node_title',
        'color_code',
        'icon',
        'sort_order',
        'is_active',
        'start_date',
        'end_date',
        'layout_coords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'layout_coords' => 'array',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(OrganizationPosition::class, 'position_id');
    }

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'official_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationNode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrganizationNode::class, 'parent_id')->orderBy('sort_order');
    }
}
