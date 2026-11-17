<?php

namespace App\Models\BlankStock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class KtpBlankUsageReport extends Model
{
    use SoftDeletes;

    protected $table = 'ktp_blank_usage_reports';

    protected $fillable = [
        'report_number',
        'usage_date',
        'operator_id',
        'session_name',
        'quantity_used',
        'quantity_damaged',
        'total_quantity',
        'damage_reason',
        'damage_proof_path',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'reversal_reason',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'usage_date'       => 'date:Y-m-d',
        'quantity_used'    => 'integer',
        'quantity_damaged' => 'integer',
        'total_quantity'   => 'integer',
        'balance_before'   => 'integer',
        'balance_after'    => 'integer',
        'approved_at'      => 'datetime',
    ];

    public const STATUS_DRAFT              = 'DRAFT';
    public const STATUS_SUBMITTED          = 'SUBMITTED';
    public const STATUS_APPROVED           = 'APPROVED';
    public const STATUS_REVISION_REQUESTED = 'REVISION_REQUESTED';
    public const STATUS_REVERSED           = 'REVERSED';

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
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
            self::STATUS_DRAFT              => 'Draf',
            self::STATUS_SUBMITTED          => 'Diajukan',
            self::STATUS_APPROVED           => 'Disahkan',
            self::STATUS_REVISION_REQUESTED => 'Perlu Revisi',
            self::STATUS_REVERSED           => 'Dibatalkan / Koreksi',
            default                         => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT              => 'slate',
            self::STATUS_SUBMITTED          => 'blue',
            self::STATUS_APPROVED           => 'emerald',
            self::STATUS_REVISION_REQUESTED => 'amber',
            self::STATUS_REVERSED           => 'rose',
            default                         => 'slate',
        };
    }
}
