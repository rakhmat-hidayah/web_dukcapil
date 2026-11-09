<?php

namespace App\Models\Applicant;

use App\Models\Queue\QueueApplication;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class ApplicantAccount extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    const IDENTITY_PENDING = 'PENDING';
    const IDENTITY_VERIFIED = 'VERIFIED';
    const IDENTITY_REJECTED = 'REJECTED';

    const STATUS_EMAIL_PENDING = 'EMAIL_PENDING';
    const STATUS_IDENTITY_PENDING = 'IDENTITY_PENDING';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_SUSPENDED = 'SUSPENDED';
    const STATUS_LOCKED = 'LOCKED';

    protected $table = 'applicant_accounts';

    protected $fillable = [
        'nik_encrypted',
        'nik_hash',
        'name',
        'email_normalized',
        'email_verified_at',
        'password',
        'identity_status',
        'account_status',
        'status_reason',
        'identity_verified_at',
        'identity_verified_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'nik_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'identity_verified_at' => 'datetime',
            'password'             => 'hashed',
        ];
    }

    /**
     * Compute a deterministic HMAC blind index for an applicant's NIK.
     * Prevents database enumeration without the secret server APP_KEY while allowing unique indexing.
     */
    public static function computeNikHash(string $nik): string
    {
        $normalized = preg_replace('/\D/', '', trim($nik));
        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }

    /**
     * Set the applicant's NIK, encrypting the plaintext and setting the keyed HMAC blind index.
     */
    public function setNikAttribute(string $nik): void
    {
        $digits = preg_replace('/\D/', '', trim($nik));
        $this->attributes['nik_encrypted'] = Crypt::encryptString($digits);
        $this->attributes['nik_hash'] = self::computeNikHash($digits);
    }

    /**
     * Decrypt the applicant's NIK on demand.
     */
    public function getNikAttribute(): ?string
    {
        if (empty($this->nik_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->nik_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get masked NIK for safe display (e.g. 520501******0001).
     */
    public function getMaskedNikAttribute(): string
    {
        $nik = $this->nik;
        if (!$nik || strlen($nik) < 16) {
            return '****************';
        }

        return substr($nik, 0, 6) . '******' . substr($nik, 12, 4);
    }

    /**
     * Get masked email for safe display (e.g. jo***@gmail.com).
     */
    public function getMaskedEmailAttribute(): string
    {
        $email = $this->email_normalized;
        if (!$email || !str_contains($email, '@')) {
            return '***@***';
        }

        [$name, $domain] = explode('@', $email, 2);
        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(1, $len - 3)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }

    // ------------------------------------------------------------------
    // STATUS HELPERS
    // ------------------------------------------------------------------

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isActive(): bool
    {
        return $this->account_status === self::STATUS_ACTIVE;
    }

    public function isIdentityVerified(): bool
    {
        return $this->identity_status === self::IDENTITY_VERIFIED;
    }

    public function isSuspended(): bool
    {
        return $this->account_status === self::STATUS_SUSPENDED;
    }

    public function isLocked(): bool
    {
        return $this->account_status === self::STATUS_LOCKED;
    }

    // ------------------------------------------------------------------
    // RELATIONSHIPS
    // ------------------------------------------------------------------

    public function devices(): HasMany
    {
        return $this->hasMany(ApplicantDevice::class, 'applicant_account_id');
    }

    public function activeDevice(): HasOne
    {
        return $this->hasOne(ApplicantDevice::class, 'applicant_account_id')
            ->whereNull('revoked_at')
            ->latest('registered_at');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ApplicantDeviceAuditLog::class, 'applicant_account_id');
    }

    public function otps(): HasMany
    {
        return $this->hasMany(ApplicantEmailOtp::class, 'applicant_account_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(QueueApplication::class, 'applicant_account_id');
    }

    public function verifiedByAdmin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'identity_verified_by');
    }
}
