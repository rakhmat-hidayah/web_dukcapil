<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    // Disable default timestamps since we manually log created_at
    public $timestamps = false;

    protected $fillable = [
        'api_key_id',
        'endpoint',
        'method',
        'ip_address',
        'response_code',
        'duration_ms',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
