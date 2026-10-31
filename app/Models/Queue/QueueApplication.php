<?php

namespace App\Models\Queue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
        'reg_number',
        'access_token',
        'service_type_id',
        'queue_slot_id',
        'queue_number',
        'status',
        'applicant_name',
        'applicant_nik',
        'applicant_birth_place',
        'applicant_birth_date',
        'applicant_phone',
        'applicant_email',
        'applicant_address',
        'applicant_village',
        'applicant_district',
        'attendance_type',
        'representative_nik',
        'representative_name',
        'representative_phone',
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
        'applicant_birth_date' => 'date',
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
        'status_label',
        'status_color',
        'masked_nik',
        'is_represented',
        'attendance_label',
        'handover_recipient_name',
        'handover_recipient_nik',
        'physical_verified_status',
        'physical_verified_notes',
        'applicant_birth_date_formatted',
        'selfie_document',
        'selfie_url',
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

    public function getMaskedNikAttribute(): string
    {
        $nik = (string) $this->applicant_nik;
        if (strlen($nik) >= 16) {
            return substr($nik, 0, 6) . '******' . substr($nik, 12);
        }
        return substr($nik, 0, 4) . '****';
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

    public function getApplicantBirthDateFormattedAttribute(): ?string
    {
        $raw = $this->getRawOriginal('applicant_birth_date');
        if (!$raw) {
            return null;
        }
        return \Carbon\Carbon::parse($raw)->translatedFormat('d F Y');
    }

    public function getSelfieDocumentAttribute(): ?ApplicationDocument
    {
        $docs = $this->relationLoaded('documents') 
            ? $this->documents 
            : $this->documents()->with('serviceDocument')->get();

        return $docs->first(function ($doc) {
            $serviceDoc = $doc->serviceDocument;
            if (!$serviceDoc) {
                return false;
            }
            return $serviceDoc->capture_mode === 'user'
                || str_contains(strtolower($serviceDoc->label), 'selfie')
                || str_contains(strtolower($serviceDoc->label), 'foto wajah');
        });
    }

    public function getSelfieUrlAttribute(): ?string
    {
        $selfie = $this->selfie_document;
        if (!$selfie) {
            return null;
        }
        return route('admin.queue.documents.serve', ['documentId' => $selfie->id]);
    }
}
