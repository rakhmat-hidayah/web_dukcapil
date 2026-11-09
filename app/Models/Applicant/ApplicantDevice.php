<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantDevice extends Model
{
    use HasFactory;

    protected $table = 'applicant_devices';

    protected $fillable = [
        'applicant_account_id',
        'device_token_hash',
        'device_label',
        'ip_address',
        'user_agent',
        'registered_at',
        'last_seen_at',
        'revoked_at',
        'revocation_reason',
        'revoked_by_type',
        'revoked_by_id',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'last_seen_at'  => 'datetime',
            'revoked_at'    => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ApplicantAccount::class, 'applicant_account_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    /**
     * Revoke this active device.
     */
    public function revoke(string $reason, ?string $actorType = 'system', ?int $actorId = null): void
    {
        $this->update([
            'revoked_at'        => now(),
            'revocation_reason' => $reason,
            'revoked_by_type'   => $actorType,
            'revoked_by_id'     => $actorId,
        ]);
    }
}
