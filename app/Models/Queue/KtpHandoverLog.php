<?php

namespace App\Models\Queue;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KtpHandoverLog extends Model
{
    public $timestamps = false;

    protected $table = 'ktp_handover_logs';

    protected $fillable = [
        'handover_id',
        'application_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'notes',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'created_at_formatted',
        'action_label',
    ];

    public function handover(): BelongsTo
    {
        return $this->belongsTo(KtpHandover::class, 'handover_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return Carbon::parse($this->created_at)->timezone('Asia/Makassar')->translatedFormat('d M Y, H:i:s') . ' WITA';
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'MIGRATION_INIT'     => 'Inisialisasi Sistem',
            'CREATED'            => 'Pendaftaran KTP-el',
            'MARKED_READY'       => 'Ditandai Siap Diambil',
            'HANDOVER_COMPLETED' => 'Penyerahan Selesai',
            'EXCEPTION_REPORTED' => 'Kendala Dilaporkan',
            'EXCEPTION_RESOLVED' => 'Kendala Diselesaikan',
            default              => $this->action,
        };
    }
}
