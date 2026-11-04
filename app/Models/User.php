<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'failed_login_count',
        'locked_until',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
        'two_factor_last_used_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'is_active'               => 'boolean',
            'last_login_at'           => 'datetime',
            'locked_until'            => 'datetime',
            'failed_login_count'      => 'integer',
            'two_factor_enabled'      => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_last_used_at' => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // LOGIN LOCKOUT HELPERS
    // ------------------------------------------------------------------

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function lockRemainingSeconds(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }

        return (int) now()->diffInSeconds($this->locked_until);
    }

    public function recordSuccessfulLogin(string $ip): void
    {
        $this->update([
            'last_login_at'      => now(),
            'last_login_ip'      => $ip,
            'failed_login_count' => 0,
            'locked_until'       => null,
        ]);
    }

    public function recordFailedLogin(): void
    {
        $count = $this->failed_login_count + 1;
        $update = ['failed_login_count' => $count];

        if ($count >= 5) {
            $update['locked_until'] = now()->addMinutes(15);
        }

        $this->update($update);
    }

    // ------------------------------------------------------------------
    // TWO-FACTOR AUTHENTICATION (TOTP / RFC-6238) HELPERS
    // ------------------------------------------------------------------

    /**
     * Check if user has active & confirmed 2FA.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Check if user's role requires 2FA based on config policy.
     */
    public function requiresTwoFactorByRole(): bool
    {
        if (!config('two-factor.enabled', true)) {
            return false;
        }

        if (!config('two-factor.required', false)) {
            return false;
        }

        $requiredRoles = config('two-factor.required_roles', []);
        if (empty($requiredRoles)) {
            return false;
        }

        return $this->hasAnyRole($requiredRoles);
    }

    /**
     * Get decrypted TOTP secret.
     */
    public function getTwoFactorSecretDecrypted(): ?string
    {
        if (empty($this->two_factor_secret)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->two_factor_secret);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Set encrypted TOTP secret.
     */
    public function setTwoFactorSecretEncrypted(?string $secret): void
    {
        $this->two_factor_secret = $secret ? Crypt::encryptString($secret) : null;
    }

    /**
     * Get decrypted recovery codes as array.
     */
    public function getRecoveryCodesDecrypted(): array
    {
        if (empty($this->two_factor_recovery_codes)) {
            return [];
        }

        try {
            $json = Crypt::decryptString($this->two_factor_recovery_codes);
            return json_decode($json, true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Set encrypted recovery codes array.
     */
    public function setRecoveryCodesEncrypted(array $codes): void
    {
        $this->two_factor_recovery_codes = !empty($codes)
            ? Crypt::encryptString(json_encode($codes))
            : null;
    }

    /**
     * Generate 10 new random recovery codes in format DKCP-XXXX-XXXX.
     */
    public function generateRecoveryCodes(): array
    {
        $count  = config('two-factor.recovery_codes_count', 10);
        $prefix = config('two-factor.recovery_code_prefix', 'DKCP-');

        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(Str::random(4));
            $part2 = strtoupper(Str::random(4));
            $codes[] = "{$prefix}{$part1}-{$part2}";
        }

        $this->setRecoveryCodesEncrypted($codes);
        $this->save();

        return $codes;
    }

    /**
     * Check and consume a single-use recovery code.
     */
    public function useRecoveryCode(string $inputCode): bool
    {
        $inputClean = strtoupper(trim($inputCode));
        $codes = $this->getRecoveryCodesDecrypted();

        if (empty($codes)) {
            return false;
        }

        foreach ($codes as $idx => $code) {
            if (hash_equals(strtoupper($code), $inputClean)) {
                // Remove used code
                unset($codes[$idx]);
                $this->setRecoveryCodesEncrypted(array_values($codes));
                $this->two_factor_last_used_at = now();
                $this->save();
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is a Super Administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->username === 'superadmin'
            || $this->hasAnyRole(['Administrator Utama', 'Super Administrator']);
    }
}
