<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'queue_notification_logs';

    protected $fillable = [
        'application_id',
        'channel',
        'recipient',
        'event_type',
        'status',
        'error_message',
    ];

    public function application()
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }
}
