<?php

namespace App\Models\BlankStock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KtpBlankStock extends Model
{
    protected $table = 'ktp_blank_stocks';

    protected $fillable = [
        'current_stock',
        'total_received',
        'total_used',
        'total_damaged',
        'total_adjusted',
        'min_stock_alert',
        'critical_stock_alert',
        'last_transaction_id',
        'last_transaction_at',
        'updated_by',
    ];

    protected $casts = [
        'current_stock'        => 'integer',
        'total_received'       => 'integer',
        'total_used'           => 'integer',
        'total_damaged'        => 'integer',
        'total_adjusted'       => 'integer',
        'min_stock_alert'      => 'integer',
        'critical_stock_alert' => 'integer',
        'last_transaction_at'  => 'datetime',
    ];

    public const STATUS_AVAILABLE = 'TERSEDIA';
    public const STATUS_LIMITED   = 'PERSEDIAAN TERBATAS';
    public const STATUS_EMPTY     = 'STOK KOSONG';

    /**
     * Get or initialize the authoritative singleton stock record.
     */
    public static function getSingleton(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'current_stock'        => 0,
                'total_received'       => 0,
                'total_used'           => 0,
                'total_damaged'        => 0,
                'total_adjusted'       => 0,
                'min_stock_alert'      => 500,
                'critical_stock_alert' => 100,
            ]
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function lastTransaction(): BelongsTo
    {
        return $this->belongsTo(KtpBlankTransaction::class, 'last_transaction_id');
    }

    /**
     * Determine public / internal availability status string.
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return self::STATUS_EMPTY;
        }

        if ($this->current_stock <= $this->min_stock_alert) {
            return self::STATUS_LIMITED;
        }

        return self::STATUS_AVAILABLE;
    }

    /**
     * Get status color badge for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->availability_status) {
            self::STATUS_AVAILABLE => 'emerald',
            self::STATUS_LIMITED   => 'amber',
            self::STATUS_EMPTY     => 'rose',
            default                => 'slate',
        };
    }
}
