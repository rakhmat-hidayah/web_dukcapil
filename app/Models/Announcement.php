<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'content', 'attachment', 'priority',
        'is_pinned', 'is_popup', 'is_ticker', 'status', 'published_at', 'expires_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_popup' => 'boolean',
        'is_ticker' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'published' && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
