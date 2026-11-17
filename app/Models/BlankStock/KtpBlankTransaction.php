<?php

namespace App\Models\BlankStock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class KtpBlankTransaction extends Model
{
    protected $table = 'ktp_blank_transactions';

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'qty_in',
        'qty_out_used',
        'qty_out_damaged',
        'balance_before',
        'balance_after',
        'user_id',
        'operator_id',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date:Y-m-d',
        'qty_in'           => 'integer',
        'qty_out_used'     => 'integer',
        'qty_out_damaged'  => 'integer',
        'balance_before'   => 'integer',
        'balance_after'    => 'integer',
    ];

    public const TYPE_RECEIPT        = 'RECEIPT';
    public const TYPE_USAGE          = 'USAGE';
    public const TYPE_DAMAGE         = 'DAMAGE';
    public const TYPE_ADJUSTMENT_ADD = 'ADJUSTMENT_ADD';
    public const TYPE_ADJUSTMENT_SUB = 'ADJUSTMENT_SUB';
    public const TYPE_REVERSAL       = 'REVERSAL';

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_RECEIPT        => 'Penerimaan Blangko',
            self::TYPE_USAGE          => 'Penggunaan Cetak KTP-el',
            self::TYPE_DAMAGE         => 'Blangko Rusak / Reject',
            self::TYPE_ADJUSTMENT_ADD => 'Penyesuaian (Tambah)',
            self::TYPE_ADJUSTMENT_SUB => 'Penyesuaian (Kurang)',
            self::TYPE_REVERSAL       => 'Pembatalan / Reversal',
            default                   => $this->transaction_type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_RECEIPT, self::TYPE_ADJUSTMENT_ADD => 'emerald',
            self::TYPE_USAGE                               => 'blue',
            self::TYPE_DAMAGE                              => 'rose',
            self::TYPE_ADJUSTMENT_SUB, self::TYPE_REVERSAL => 'amber',
            default                                        => 'slate',
        };
    }
}
