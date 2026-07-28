<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;

class OrganizationLayout extends Model
{
    protected $fillable = [
        'name',
        'layout_type',
        'is_default',
        'config',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'config' => 'array',
    ];
}
