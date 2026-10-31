<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VerificationHistory extends Model
{
    protected $table = 'queue_verification_history';

    protected $fillable = [
        'application_id',
        'user_id',
        'from_status',
        'to_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = [
        'from_status_label',
        'to_status_label',
        'action_title',
    ];

    public static function statusLabel(?string $status): string
    {
        if (!$status) {
            return '-';
        }

        return [
            'DRAFT' => 'Draf',
            'SUBMITTED' => 'Menunggu Verifikasi',
            'UNDER_REVIEW' => 'Sedang Diverifikasi',
            'REVISION_REQUIRED' => 'Perlu Perbaikan Dokumen',
            'RESUBMITTED' => 'Perbaikan Dikirim Ulang',
            'PRE_VERIFIED' => 'Lolos Pra-Verifikasi',
            'APPROVED' => 'Lolos Pra-Verifikasi',
            'QUEUE_BOOKED' => 'Nomor Antrean Terbit',
            'SCHEDULED' => 'Jadwal Ditetapkan',
            'ARRIVED' => 'Hadir di Loket',
            'CHECKED_IN' => 'Hadir di Loket',
            'PHYSICAL_VERIFICATION' => 'Pemeriksaan Fisik Dokumen',
            'APPROVED_FOR_SERVICE' => 'Disetujui untuk Dilayani',
            'PROCESSING' => 'Sedang Diproses (Rekam/Cetak)',
            'IN_SERVICE' => 'Sedang Dilayani',
            'READY_FOR_PICKUP' => 'KTP Siap Diambil',
            'COMPLETED' => 'Pelayanan Selesai',
            'CANCELLED' => 'Dibatalkan',
            'REJECTED' => 'Pengajuan Ditolak',
            'EXPIRED' => 'Kedaluwarsa',
        ][$status] ?? $status;
    }

    public function getFromStatusLabelAttribute(): string
    {
        return self::statusLabel($this->from_status);
    }

    public function getToStatusLabelAttribute(): string
    {
        return self::statusLabel($this->to_status);
    }

    public function getActionTitleAttribute(): string
    {
        if ($this->from_status && $this->from_status !== $this->to_status) {
            return self::statusLabel($this->from_status) . ' → ' . self::statusLabel($this->to_status);
        }
        return self::statusLabel($this->to_status);
    }

    public function application()
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
