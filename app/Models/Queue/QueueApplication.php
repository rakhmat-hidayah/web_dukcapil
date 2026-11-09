<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class QueueApplication extends Model
{
    use SoftDeletes;

    protected $table = 'queue_applications';

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const STATUS_REVISION_REQUIRED = 'REVISION_REQUIRED';
    const STATUS_RESUBMITTED = 'RESUBMITTED';
    const STATUS_PRE_VERIFIED = 'PRE_VERIFIED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_QUEUE_BOOKED = 'QUEUE_BOOKED';
    const STATUS_ARRIVED = 'ARRIVED';
    const STATUS_PHYSICAL_VERIFICATION = 'PHYSICAL_VERIFICATION';
    const STATUS_APPROVED_FOR_SERVICE = 'APPROVED_FOR_SERVICE';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_READY_FOR_PICKUP = 'READY_FOR_PICKUP';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_EXPIRED = 'EXPIRED';

    // Legacy aliases for backward compatibility
    const STATUS_APPROVED = 'PRE_VERIFIED';
    const STATUS_SCHEDULED = 'QUEUE_BOOKED';
    const STATUS_CHECKED_IN = 'ARRIVED';
    const STATUS_IN_SERVICE = 'PROCESSING';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_REVISION_REQUIRED,
        self::STATUS_RESUBMITTED,
        self::STATUS_PRE_VERIFIED,
        self::STATUS_REJECTED,
        self::STATUS_QUEUE_BOOKED,
        self::STATUS_ARRIVED,
        self::STATUS_PHYSICAL_VERIFICATION,
        self::STATUS_APPROVED_FOR_SERVICE,
        self::STATUS_PROCESSING,
        self::STATUS_READY_FOR_PICKUP,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
    ];

    protected $fillable = [
        'applicant_account_id',
        'reg_number',
        'access_token',
        'service_type_id',
        'queue_slot_id',
        'queue_number',
        'status',
        'applicant_name',
        'applicant_nik',
        'applicant_email',
        'attendance_type',
        'representative_nik',
        'representative_name',
        'representative_relationship',
        'representative_kk_status',
        'absence_reason',
        'statement_agreed',
        'statement_agreed_at',
        'notes',
        'rejection_reason',
        'revision_notes',
        'submitted_at',
        'approved_at',
        'scheduled_at',
        'checked_in_at',
        'checked_in_by',
        'physical_verified_at',
        'physical_verified_by',
        'physical_verification_decision',
        'physical_verification_notes',
        'physical_match_identity',
        'physical_match_documents',
        'physical_match_authorization',
        'attended_person_type',
        'attended_person_name',
        'attended_person_nik',
        'handover_at',
        'handover_by',
        'received_by_type',
        'receiver_name',
        'receiver_nik',
        'receiver_phone',
        'handover_notes',
        'completed_at',
    ];

    protected $casts = [
        'statement_agreed' => 'boolean',
        'statement_agreed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'physical_verified_at' => 'datetime',
        'physical_match_identity' => 'boolean',
        'physical_match_documents' => 'boolean',
        'physical_match_authorization' => 'boolean',
        'handover_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'nik',
        'status_label',
        'status_color',
        'masked_nik',
        'is_represented',
        'attendance_label',
        'handover_recipient_name',
        'handover_recipient_nik',
        'physical_verified_status',
        'physical_verified_notes',
        'selfie_document',
        'selfie_verification',
        'is_selfie_verified',
        'selfie_url',
        'selfie_thumbnail_url',
        'tracking_url',
        'qr_code_svg',
        'verification_urgency',
        'verification_urgency_label',
        'waiting_time_human',
        'draft_remaining_seconds',
        'draft_remaining_minutes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (empty($m->access_token)) {
                $m->access_token = Str::random(48);
            }
        });
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function slot()
    {
        return $this->belongsTo(QueueSlot::class, 'queue_slot_id');
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function verificationHistory()
    {
        return $this->hasMany(VerificationHistory::class, 'application_id')->orderBy('created_at', 'desc');
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class, 'application_id')->orderBy('created_at', 'desc');
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_REVISION_REQUIRED]);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION_REQUIRED]);
    }

    public function isExpiredDraft(?int $minutes = null): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        if ($minutes === null) {
            $minutes = (int) QueueSetting::get('queue.draft_timeout_minutes', 15);
        }

        return $this->created_at ? $this->created_at->lt(now()->subMinutes($minutes)) : false;
    }

    public function getDraftRemainingSecondsAttribute(): int
    {
        if ($this->status !== self::STATUS_DRAFT || !$this->created_at) {
            return 0;
        }

        $timeoutMinutes = (int) QueueSetting::get('queue.draft_timeout_minutes', 15);
        $expiresAt = $this->created_at->copy()->addMinutes($timeoutMinutes);

        return max(0, (int) now()->diffInSeconds($expiresAt, false));
    }

    public function getDraftRemainingMinutesAttribute(): int
    {
        if ($this->status !== self::STATUS_DRAFT || !$this->created_at) {
            return 0;
        }

        $timeoutMinutes = (int) QueueSetting::get('queue.draft_timeout_minutes', 15);
        $expiresAt = $this->created_at->copy()->addMinutes($timeoutMinutes);

        return max(0, (int) ceil(now()->diffInSeconds($expiresAt, false) / 60));
    }

    public function getMaskedNikAttribute(): string
    {
        $nik = (string) $this->applicant_nik;
        if (strlen($nik) >= 16) {
            return substr($nik, 0, 6) . '******' . substr($nik, 12);
        }
        return substr($nik, 0, 4) . '****';
    }

    public function applicantAccount()
    {
        return $this->belongsTo(\App\Models\Applicant\ApplicantAccount::class, 'applicant_account_id');
    }

    public function checkedInUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'checked_in_by');
    }

    public function physicalVerifiedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'physical_verified_by');
    }

    public function handoverUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'handover_by');
    }

    public function handover(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(KtpHandover::class, 'application_id');
    }

    public function selfieVerification(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(QueueSelfieVerification::class, 'application_id');
    }

    public function getSelfieVerificationAttribute(): ?QueueSelfieVerification
    {
        return $this->relationLoaded('selfieVerification')
            ? $this->getRelation('selfieVerification')
            : $this->selfieVerification()->first();
    }

    public function getIsSelfieVerifiedAttribute(): bool
    {
        return $this->selfie_verification !== null;
    }

    public function getNikAttribute(): ?string
    {
        return $this->applicant_nik;
    }

    public function getIsRepresentedAttribute(): bool
    {
        return in_array($this->attendance_type, ['family_in_kk', 'family_out_kk', 'other']);
    }

    public function getAttendanceLabelAttribute(): string
    {
        return [
            'self' => 'Datang Sendiri',
            'family_in_kk' => 'Diwakilkan Keluarga (1 KK)',
            'family_out_kk' => 'Diwakilkan Keluarga (Luar KK)',
            'other' => 'Diwakilkan Pihak Lain / Kuasa',
        ][$this->attendance_type] ?? 'Datang Sendiri';
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Terkirim / Antri Verifikasi',
            self::STATUS_UNDER_REVIEW => 'Sedang Diverifikasi',
            self::STATUS_REVISION_REQUIRED => 'Perlu Perbaikan Dokumen',
            self::STATUS_RESUBMITTED => 'Perbaikan Dikirim Ulang',
            self::STATUS_PRE_VERIFIED => 'Terverifikasi Online (Siap Pilih Jadwal)',
            self::STATUS_REJECTED => 'Pengajuan Ditolak',
            self::STATUS_QUEUE_BOOKED => 'Jadwal Antrean Terbit',
            self::STATUS_ARRIVED => 'Sudah Hadir di Kantor (Check-In)',
            self::STATUS_PHYSICAL_VERIFICATION => 'Pemeriksaan Fisik Dokumen',
            self::STATUS_APPROVED_FOR_SERVICE => 'Fisik Cocok (Disetujui Layanan)',
            self::STATUS_PROCESSING => 'Sedang Diproses (Rekam/Cetak)',
            self::STATUS_READY_FOR_PICKUP => 'KTP Siap Diambil',
            self::STATUS_COMPLETED => 'Pelayanan Selesai (KTP Diserahkan)',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_EXPIRED => 'Kedaluwarsa / Tidak Hadir',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return [
            self::STATUS_DRAFT => 'slate',
            self::STATUS_SUBMITTED => 'blue',
            self::STATUS_UNDER_REVIEW => 'amber',
            self::STATUS_REVISION_REQUIRED => 'orange',
            self::STATUS_RESUBMITTED => 'sky',
            self::STATUS_PRE_VERIFIED => 'emerald',
            self::STATUS_REJECTED => 'rose',
            self::STATUS_QUEUE_BOOKED => 'indigo',
            self::STATUS_ARRIVED => 'teal',
            self::STATUS_PHYSICAL_VERIFICATION => 'cyan',
            self::STATUS_APPROVED_FOR_SERVICE => 'emerald',
            self::STATUS_PROCESSING => 'purple',
            self::STATUS_READY_FOR_PICKUP => 'violet',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'zinc',
            self::STATUS_EXPIRED => 'rose',
        ][$this->status] ?? 'slate';
    }

    public function getHandoverRecipientNameAttribute(): ?string
    {
        return $this->receiver_name;
    }

    public function getHandoverRecipientNikAttribute(): ?string
    {
        return $this->receiver_nik;
    }

    public function getPhysicalVerifiedStatusAttribute(): ?string
    {
        return $this->physical_verification_decision;
    }

    public function getPhysicalVerifiedNotesAttribute(): ?string
    {
        return $this->physical_verification_notes;
    }

    public function getSelfieDocumentAttribute(): ?ApplicationDocument
    {
        $docs = $this->relationLoaded('documents') 
            ? $this->documents 
            : $this->documents()->with('serviceDocument')->get();

        if (!$docs || $docs->isEmpty()) {
            return null;
        }

        // 1. Primary match: service document with user capture mode or label indicating photo/selfie
        $matched = $docs->first(function ($doc) {
            $serviceDoc = $doc->serviceDocument;
            if ($serviceDoc) {
                $label = strtolower($serviceDoc->label ?? '');
                if ($serviceDoc->capture_mode === 'user'
                    || str_contains($label, 'selfie')
                    || str_contains($label, 'foto wajah')
                    || str_contains($label, 'foto pemohon')
                    || str_contains($label, 'pas foto')
                    || str_contains($label, 'foto diri')) {
                    return true;
                }
            }
            $filename = strtolower($doc->original_filename ?? '');
            return str_contains($filename, 'selfie')
                || str_contains($filename, 'foto_wajah')
                || str_contains($filename, 'foto-wajah')
                || str_contains($filename, 'pas_foto')
                || str_contains($filename, 'foto_pemohon');
        });

        if ($matched) {
            return $matched;
        }

        // 2. Secondary fallback: first uploaded image document if no specific selfie is configured
        return $docs->first(function ($doc) {
            $mime = strtolower($doc->mime_type ?? '');
            return str_starts_with($mime, 'image/');
        });
    }

    public function isSelfieDocument(ApplicationDocument $doc): bool
    {
        $serviceDoc = $doc->serviceDocument ?: ($doc->relationLoaded('serviceDocument') ? $doc->serviceDocument : null);
        if ($serviceDoc) {
            $label = strtolower($serviceDoc->label ?? '');
            if ($serviceDoc->capture_mode === 'user'
                || str_contains($label, 'selfie')
                || str_contains($label, 'foto wajah')
                || str_contains($label, 'foto pemohon')
                || str_contains($label, 'pas foto')
                || str_contains($label, 'foto diri')) {
                return true;
            }
        }
        $filename = strtolower($doc->original_filename ?? '');
        if (str_contains($filename, 'selfie')
            || str_contains($filename, 'foto_wajah')
            || str_contains($filename, 'foto-wajah')
            || str_contains($filename, 'pas_foto')
            || str_contains($filename, 'foto_pemohon')) {
            return true;
        }

        $selfie = $this->selfie_document;
        return $selfie && $selfie->id === $doc->id;
    }

    public function getSelfieUrlAttribute(): ?string
    {
        $selfie = $this->selfie_document;
        if (!$selfie) {
            return null;
        }

        // If marked destroyed or path is empty, file is permanently destroyed
        if (!empty($selfie->is_destroyed) || empty($selfie->getRawOriginal('path'))) {
            return null;
        }

        $verification = $this->selfie_verification;
        if ($verification && $verification->deletion_status === QueueSelfieVerification::DELETION_DELETED) {
            return null;
        }

        // Selfie photos are biometric verification artifacts:
        // NEVER expose to public applicant or unauthenticated visitors!
        if (!auth()->check()) {
            return null;
        }

        return route('admin.queue.documents.serve', ['documentId' => $selfie->id]);
    }

    public function getSelfieThumbnailUrlAttribute(): ?string
    {
        return $this->selfie_url;
    }

    public function getTrackingUrlAttribute(): string
    {
        return !empty($this->reg_number) ? url('/lacak/' . $this->reg_number) : '';
    }

    public function getQrCodeSvgAttribute(): string
    {
        if (empty($this->reg_number)) {
            return '';
        }

        try {
            $targetUrl = $this->tracking_url ?: url('/lacak/' . $this->reg_number);
            $qr = Encoder::encode($targetUrl, ErrorCorrectionLevel::M());
            $matrix = $qr->getMatrix();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $margin = 2;
            $totalWidth = $width + ($margin * 2);
            $totalHeight = $height + ($margin * 2);

            $path = '';
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    if ($matrix->get($x, $y) === 1) {
                        $posX = $x + $margin;
                        $posY = $y + $margin;
                        $path .= "M{$posX},{$posY}h1v1h-1z ";
                    }
                }
            }

            return sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="100%%" height="100%%" shape-rendering="crispEdges">' .
                '<rect width="100%%" height="100%%" fill="#ffffff"/>' .
                '<path d="%s" fill="#000000"/>' .
                '</svg>',
                $totalWidth,
                $totalHeight,
                trim($path)
            );
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Determine urgency level for operator verification:
     * - 'overdue': Unverified for more than 24 hours (> 1x24 jam)
     * - 'new': Unverified for 24 hours or less (baru masuk)
     * - null: Already past initial online verification or in draft/cancelled state
     */
    public function getVerificationUrgencyAttribute(): ?string
    {
        if (!in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED, self::STATUS_UNDER_REVIEW])) {
            return null;
        }

        $referenceTime = $this->submitted_at ?? $this->created_at;
        if (!$referenceTime) {
            return 'new';
        }

        return $referenceTime->lte(now()->subHours(24)) ? 'overdue' : 'new';
    }

    /**
     * Get readable label for verification urgency.
     */
    public function getVerificationUrgencyLabelAttribute(): ?string
    {
        $urgency = $this->verification_urgency;
        if ($urgency === 'overdue') {
            return 'Belum Diverifikasi >24 Jam (Perlu Penanganan Segera)';
        }
        if ($urgency === 'new') {
            return 'Baru Masuk (Segera Diverifikasi)';
        }
        return null;
    }

    /**
     * Get human readable waiting time since submission.
     */
    public function getWaitingTimeHumanAttribute(): ?string
    {
        if (!$this->verification_urgency) {
            return null;
        }

        $referenceTime = $this->submitted_at ?? $this->created_at;
        if (!$referenceTime) {
            return null;
        }

        \Carbon\Carbon::setLocale('id');
        $duration = $referenceTime->diffForHumans(now(), [
            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
            'parts' => 2,
        ]);

        return 'Menunggu ' . $duration;
    }
}
