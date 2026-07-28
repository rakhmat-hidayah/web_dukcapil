<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintReply extends Model
{
    protected $fillable = [
        'complaint_id', 'user_id', 'message', 'type',
        'old_status', 'new_status', 'is_visible_to_submitter',
    ];

    protected $casts = [
        'is_visible_to_submitter' => 'boolean',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
