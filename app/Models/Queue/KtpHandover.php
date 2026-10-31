<?php

namespace App\Models\Queue;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KtpHandover extends Model
{
    use SoftDeletes;

    protected $table = 'ktp_handovers';

    public const STATUS_PENDING    = 'MENUNGGU_KETERSEDIAAN';
    public const STATUS_READY      = 'SIAP_DIAMBIL';
    public const STATUS_HANDED_OVER = 'SUDAH_DISERAHKAN';
    public const STATUS_EXCEPTION  = 'KENDALA_PENGAMBILAN';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_READY,
        self::STATUS_HANDED_OVER,
        self::STATUS_EXCEPTION,
    ];

    protected $fillable = [
        'application_id',
        'status',
        'ready_marked_at',
        'ready_marked_by',
        'ready_notes',
        'actual_receiver_type',
        'actual_receiver_name',
        'actual_receiver_nik',
        'actual_receiver_phone',
        'receiver_relationship',
        'handover_at',
        'handover_by',
        'handover_notes',
        'photo_path',
        'photo_mime',
        'photo_size_bytes',
        'photo_hash',
        'has_exception',
        'exception_reason',
        'exception_reported_at',
        'exception_reported_by',
        'exception_resolved_at',
        'exception_resolved_by',
        'exception_resolution_notes',
    ];

    protected $casts = [
        'ready_marked_at'       => 'datetime',
        'handover_at'           => 'datetime',
        'exception_reported_at' => 'datetime',
        'exception_resolved_at' => 'datetime',
        'has_exception'         => 'boolean',
        'photo_size_bytes'      => 'integer',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'ready_marked_at_formatted',
        'handover_at_formatted',
        'exception_reported_at_formatted',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }

    public function readyMarkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ready_marked_by');
    }

    public function handoverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handover_by');
    }

    public function exceptionReportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exception_reported_by');
    }

    public function exceptionResolvedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exception_resolved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(KtpHandoverLog::class, 'handover_id')->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_READY);
    }

    public function scopeHandedOver(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_HANDED_OVER);
    }

    public function scopeException(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXCEPTION);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'Menunggu Ketersediaan KTP-el',
            self::STATUS_READY       => 'Siap Diambil',
            self::STATUS_HANDED_OVER => 'Sudah Diserahkan',
            self::STATUS_EXCEPTION   => 'Kendala Pengambilan',
            default                  => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING     => 'amber',
            self::STATUS_READY       => 'blue',
            self::STATUS_HANDED_OVER => 'emerald',
            self::STATUS_EXCEPTION   => 'rose',
            default                  => 'slate',
        };
    }

    public function getReadyMarkedAtFormattedAttribute(): ?string
    {
        if (!$this->ready_marked_at) return null;
        return Carbon::parse($this->ready_marked_at)->timezone('Asia/Makassar')->translatedFormat('d M Y, H:i') . ' WITA';
    }

    public function getHandoverAtFormattedAttribute(): ?string
    {
        if (!$this->handover_at) return null;
        return Carbon::parse($this->handover_at)->timezone('Asia/Makassar')->translatedFormat('d M Y, H:i') . ' WITA';
    }

    public function getExceptionReportedAtFormattedAttribute(): ?string
    {
        if (!$this->exception_reported_at) return null;
        return Carbon::parse($this->exception_reported_at)->timezone('Asia/Makassar')->translatedFormat('d M Y, H:i') . ' WITA';
    }
}
