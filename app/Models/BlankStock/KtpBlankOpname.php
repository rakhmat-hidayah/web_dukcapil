<?php

namespace App\Models\BlankStock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KtpBlankOpname extends Model
{
    protected $table = 'ktp_blank_opnames';

    protected $fillable = [
        'opname_number',
        'opname_date',
        'system_stock',
        'physical_stock',
        'difference',
        'checked_by',
        'approved_by',
        'approved_at',
        'status',
        'notes',
        'evidence_path',
    ];

    protected $casts = [
        'opname_date'    => 'date:Y-m-d',
        'system_stock'   => 'integer',
        'physical_stock' => 'integer',
        'difference'     => 'integer',
        'approved_at'    => 'datetime',
    ];

    public const STATUS_DRAFT            = 'DRAFT';
    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    public const STATUS_APPROVED         = 'APPROVED';
    public const STATUS_REJECTED         = 'REJECTED';

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(KtpBlankAdjustment::class, 'opname_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT            => 'Draf Pemeriksaan',
            self::STATUS_PENDING_APPROVAL => 'Menunggu Persetujuan',
            self::STATUS_APPROVED         => 'Disahkan Pimpinan',
            self::STATUS_REJECTED         => 'Ditolak',
            default                       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT            => 'slate',
            self::STATUS_PENDING_APPROVAL => 'amber',
            self::STATUS_APPROVED         => 'emerald',
            self::STATUS_REJECTED         => 'rose',
            default                       => 'slate',
        };
    }
}
