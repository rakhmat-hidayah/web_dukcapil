<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReaction extends Model
{
    protected $fillable = [
        'news_id',
        'visitor_uuid',
        'session_id',
        'reaction_type',
        'ip_address',
        'user_agent',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }
}
