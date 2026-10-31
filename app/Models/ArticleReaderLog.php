<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReaderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'visitor_uuid',
        'session_id',
        'ip_address',
        'user_agent',
        'is_robot',
        'first_read_at',
        'last_read_at',
        'read_count',
    ];

    protected $casts = [
        'is_robot' => 'boolean',
        'first_read_at' => 'datetime',
        'last_read_at' => 'datetime',
        'read_count' => 'integer',
    ];

    /**
     * Get the associated news article.
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
