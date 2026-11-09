<?php

namespace App\Models\Queue;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueSelfieVerification extends Model
{
    protected $table = 'queue_selfie_verifications';

    const DECISION_MATCH = 'MATCH';
    const DECISION_MISMATCH = 'MISMATCH';

    const DELETION_PENDING = 'PENDING_DELETION';
    const DELETION_DELETED = 'DELETED';
    const DELETION_FAILED = 'DELETION_FAILED';

    protected $fillable = [
        'application_id',
        'document_id',
        'decision',
        'verified_by',
        'verified_by_name',
        'verified_at',
        'statement_version',
        'statement_agreed',
        'deletion_status',
        'deleted_at',
        'deletion_error',
        'retry_count',
    ];

    protected $casts = [
        'statement_agreed' => 'boolean',
        'verified_at' => 'datetime',
        'deleted_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    protected $appends = [
        'decision_label',
        'deletion_status_label',
        'is_match',
        'is_deleted',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ApplicationDocument::class, 'document_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getIsMatchAttribute(): bool
    {
        return $this->decision === self::DECISION_MATCH;
    }

    public function getIsDeletedAttribute(): bool
    {
        return $this->deletion_status === self::DELETION_DELETED;
    }

    public function getDecisionLabelAttribute(): string
    {
        return match ($this->decision) {
            self::DECISION_MATCH => 'Foto Sesuai Pemohon (MATCH)',
            self::DECISION_MISMATCH => 'Foto Bukan Pemohon (MISMATCH)',
            default => $this->decision,
        };
    }

    public function getDeletionStatusLabelAttribute(): string
    {
        return match ($this->deletion_status) {
            self::DELETION_DELETED => 'Foto Berhasil Dimusnahkan',
            self::DELETION_PENDING => 'Menunggu Penghapusan',
            self::DELETION_FAILED => 'Gagal Dihapus (Perlu Retry)',
            default => $this->deletion_status,
        };
    }
}
