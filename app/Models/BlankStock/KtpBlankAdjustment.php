<?php

namespace App\Models\BlankStock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class KtpBlankAdjustment extends Model
{
    protected $table = 'ktp_blank_adjustments';

    protected $fillable = [
        'adjustment_number',
        'adjustment_date',
        'type',
        'quantity',
        'reason',
        'opname_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'status',
        'attachment_path',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'adjustment_date' => 'date:Y-m-d',
        'quantity'        => 'integer',
        'balance_before'  => 'integer',
        'balance_after'   => 'integer',
        'approved_at'     => 'datetime',
    ];

    public const TYPE_ADDITION  = 'ADDITION';
    public const TYPE_DEDUCTION = 'DEDUCTION';

    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    public const STATUS_APPROVED         = 'APPROVED';
    public const STATUS_REJECTED         = 'REJECTED';

    public function opname(): BelongsTo
    {
        return $this->belongsTo(KtpBlankOpname::class, 'opname_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(KtpBlankTransaction::class, 'reference');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_APPROVAL => 'Menunggu Persetujuan',
            self::STATUS_APPROVED         => 'Disetujui',
            self::STATUS_REJECTED         => 'Ditolak',
            default                       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_APPROVAL => 'amber',
            self::STATUS_APPROVED         => 'emerald',
            self::STATUS_REJECTED         => 'rose',
            default                       => 'slate',
        };
    }
}
