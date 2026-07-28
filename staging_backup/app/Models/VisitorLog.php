<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLog extends Model
{
    protected $fillable = [
        'ip_address',
        'url',
        'method',
        'user_agent',
        'browser',
        'device',
        'platform',
        'referrer',
        'download_file_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function downloadFile(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'download_file_id');
    }
}
