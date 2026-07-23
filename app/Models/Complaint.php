<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Complaint extends Model
{
    protected $fillable = [
        'ticket_number', 'submitter_name', 'submitter_phone', 'submitter_email',
        'is_anonymous', 'complaint_category_id', 'subject', 'message',
        'attachment_path', 'attachment_name', 'status', 'captcha_token',
        'ip_address', 'user_agent', 'read_at', 'resolved_at', 'assigned_to',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'read_at'      => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    /** Human-readable status labels */
    public const STATUS_LABELS = [
        'pending'     => 'Menunggu Tindakan',
        'in_review'   => 'Sedang Dikaji',
        'in_progress' => 'Sedang Diproses',
        'resolved'    => 'Telah Diselesaikan',
        'rejected'    => 'Ditolak',
    ];

    /** Status badge color classes */
    public const STATUS_COLORS = [
        'pending'     => 'amber',
        'in_review'   => 'blue',
        'in_progress' => 'indigo',
        'resolved'    => 'emerald',
        'rejected'    => 'red',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'complaint_category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ComplaintReply::class)->orderBy('created_at');
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(ComplaintReply::class)
            ->where('is_visible_to_submitter', true)
            ->orderBy('created_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    /**
     * Generate a unique ticket number: DKP-YYYY-XXXXXX
     */
    public static function generateTicketNumber(): string
    {
        $year = now()->format('Y');
        do {
            $sequence = strtoupper(Str::random(6));
            $ticket   = "DKP-{$year}-{$sequence}";
        } while (self::where('ticket_number', $ticket)->exists());

        return $ticket;
    }

    protected static function booted(): void
    {
        static::creating(function (self $complaint) {
            if (empty($complaint->ticket_number)) {
                $complaint->ticket_number = self::generateTicketNumber();
            }
        });
    }
}
