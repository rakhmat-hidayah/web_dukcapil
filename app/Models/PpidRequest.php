<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PpidRequest extends Model
{
    protected $fillable = [
        'ticket_number', 'requester_name', 'requester_email',
        'requester_phone', 'requester_address', 'requester_id_number',
        'purpose', 'information_requested',
        'request_method', 'delivery_method',
        'status', 'response_notes', 'response_file',
        'responded_at', 'responded_by',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public static array $statusLabels = [
        'diterima'  => 'Diterima',
        'diproses'  => 'Diproses',
        'selesai'   => 'Selesai',
        'ditolak'   => 'Ditolak',
    ];

    public static array $statusColors = [
        'diterima'  => 'blue',
        'diproses'  => 'yellow',
        'selesai'   => 'green',
        'ditolak'   => 'red',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->ticket_number)) {
                $year   = now()->format('Y');
                $seq    = str_pad(static::whereYear('created_at', $year)->count() + 1, 6, '0', STR_PAD_LEFT);
                $model->ticket_number = "PPID-{$year}-{$seq}";
            }
        });
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }
}
