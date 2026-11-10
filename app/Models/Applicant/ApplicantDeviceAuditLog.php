<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantDeviceAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'applicant_device_audit_logs';

    protected $fillable = [
        'applicant_account_id',
        'device_id',
        'event_type',
        'actor_type',
        'actor_id',
        'ip_address',
        'user_agent',
        'reason',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ApplicantAccount::class, 'applicant_account_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(ApplicantDevice::class, 'device_id');
    }

    /**
     * Record a security event safely without logging sensitive plaintext tokens, passwords, or full NIKs.
     */
    public static function logEvent(
        int $applicantAccountId,
        string $eventType,
        ?int $deviceId = null,
        string $actorType = 'applicant',
        ?int $actorId = null,
        ?string $reason = null,
        ?array $metadata = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'applicant_account_id' => $applicantAccountId,
            'device_id'            => $deviceId,
            'event_type'           => $eventType,
            'actor_type'           => $actorType,
            'actor_id'             => $actorId,
            'ip_address'           => $ipAddress ?? request()->ip(),
            'user_agent'           => $userAgent ?? request()->userAgent(),
            'reason'               => $reason ? mb_substr($reason, 0, 255) : null,
            'metadata'             => $metadata,
            'created_at'           => now(),
        ]);
    }
}
