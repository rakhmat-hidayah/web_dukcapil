<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsShare extends Model
{
    protected $table = 'news_shares';

    protected $fillable = [
        'news_id',
        'platform',
        'ip_address',
        'user_agent',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
