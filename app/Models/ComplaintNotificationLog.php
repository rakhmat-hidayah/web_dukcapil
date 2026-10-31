<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintNotificationLog extends Model
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SENDING = 'SENDING';
    public const STATUS_SENT    = 'SENT';
    public const STATUS_FAILED  = 'FAILED';

    protected $fillable = [
        'complaint_id',
        'channel',
        'recipient',
        'notification_type',
        'status',
        'message_id',
        'sent_at',
        'failed_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at'   => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
