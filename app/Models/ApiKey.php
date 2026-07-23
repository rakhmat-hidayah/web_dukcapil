<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiKey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_name',
        'api_key',
        'secret_token',
        'rate_limit_per_hour',
        'is_active',
        'expires_at',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'permissions' => 'array',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(ApiLog::class);
    }

    /**
     * Check if the API key is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the API key has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (is_null($this->permissions)) {
            return false;
        }

        return in_array('*', $this->permissions) || in_array($permission, $this->permissions);
    }
}
