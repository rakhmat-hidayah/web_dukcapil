<?php

namespace App\Models\Profile;

use Illuminate\Database\Eloquent\Model;

class OrganizationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];
}
