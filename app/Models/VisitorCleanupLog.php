<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorCleanupLog extends Model
{
    protected $table = 'visitor_cleanup_logs';

    protected $fillable = [
        'started_at',
        'finished_at',
        'deleted_records',
        'deleted_size_mb',
        'archived_records',
        'status',
        'duration_seconds',
        'retention_days',
        'error_message',
        'triggered_by',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];
}
