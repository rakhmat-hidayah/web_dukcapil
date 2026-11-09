<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class ApplicantEmailOtp extends Model
{
    use HasFactory;

    protected $table = 'applicant_email_otps';

    protected $fillable = [
        'applicant_account_id',
        'email_normalized',
        'otp_hash',
        'purpose',
        'expires_at',
        'verified_at',
        'attempts',
        'max_attempts',
        'resend_count',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'   => 'datetime',
            'verified_at'  => 'datetime',
            'consumed_at'  => 'datetime',
            'attempts'     => 'integer',
            'max_attempts' => 'integer',
            'resend_count' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ApplicantAccount::class, 'applicant_account_id');
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Verify input OTP against stored hash with rate limit & single use enforcement.
     */
    public function verify(string $plainOtp): bool
    {
        if ($this->isExpired() || $this->isConsumed() || $this->hasExceededAttempts()) {
            return false;
        }

        $this->increment('attempts');

        if (Hash::check(trim($plainOtp), $this->otp_hash)) {
            $this->update([
                'verified_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Mark OTP as consumed (single-use).
     */
    public function consume(): void
    {
        $this->update([
            'consumed_at' => now(),
        ]);
    }
}
