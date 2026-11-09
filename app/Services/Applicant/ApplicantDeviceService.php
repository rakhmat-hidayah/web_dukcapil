<?php

namespace App\Services\Applicant;

use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDevice;
use App\Models\Applicant\ApplicantDeviceAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class ApplicantDeviceService
{
    const COOKIE_NAME = 'applicant_device_token';
    const COOKIE_LIFETIME_MINUTES = 525600; // 1 year (persistent)

    /**
     * Compute SHA-256 hash of a raw 256-bit device token.
     */
    public static function hashToken(string $rawToken): string
    {
        return hash('sha256', trim($rawToken));
    }

    /**
     * Parse User-Agent into a human-readable device label.
     */
    public static function parseDeviceLabel(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Perangkat Tidak Dikenal';
        }

        $os = 'Perangkat Lain';
        if (preg_match('/android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $os = 'iOS (iPhone/iPad)';
        } elseif (preg_match('/windows nt 10/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        return "{$browser} di {$os}";
    }

    /**
     * Bind or validate active device during login using strict database row locking.
     * Guarantees that ONE ACCOUNT has at most ONE active device, even under concurrent login races.
     *
     * @return array [ApplicantDevice $device, string $rawToken, SymfonyCookie $cookie]
     * @throws ValidationException
     */
    public function bindOrValidateOnLogin(ApplicantAccount $account, Request $request): array
    {
        $existingCookieToken = $request->cookie(self::COOKIE_NAME);

        return DB::transaction(function () use ($account, $request, $existingCookieToken) {
            // Lock applicant row to prevent race conditions during concurrent logins
            $lockedAccount = ApplicantAccount::where('id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if there is an active device registered for this account
            $activeDevice = ApplicantDevice::where('applicant_account_id', $lockedAccount->id)
                ->whereNull('revoked_at')
                ->lockForUpdate()
                ->first();

            $currentIp = $request->ip();
            $currentUserAgent = $request->userAgent();

            // Scenario A: An active device is already registered on server
            if ($activeDevice) {
                // Check if current browser presents the matching device token
                if ($existingCookieToken && hash_equals($activeDevice->device_token_hash, self::hashToken($existingCookieToken))) {
                    // Same browser returning: update last seen & IP
                    $activeDevice->update([
                        'last_seen_at' => now(),
                        'ip_address'   => $currentIp,
                        'user_agent'   => $currentUserAgent,
                    ]);

                    ApplicantDeviceAuditLog::logEvent(
                        applicantAccountId: $lockedAccount->id,
                        eventType: 'login_success_existing_device',
                        deviceId: $activeDevice->id,
                        reason: 'Login berhasil pada perangkat terdaftar'
                    );

                    $cookie = $this->createPersistentCookie($existingCookieToken);
                    return [$activeDevice, $existingCookieToken, $cookie];
                }

                // Different browser or missing/mismatched cookie token: REJECT LOGIN
                ApplicantDeviceAuditLog::logEvent(
                    applicantAccountId: $lockedAccount->id,
                    eventType: 'login_rejected_device_conflict',
                    deviceId: $activeDevice->id,
                    reason: 'Login ditolak: Akun telah terikat pada perangkat/browser aktif lain'
                );

                throw ValidationException::withMessages([
                    'nik' => 'Akun ini sedang aktif pada perangkat/browser lain. Kebijakan Disdukcapil membatasi 1 akun hanya dapat aktif pada 1 perangkat. Jika perangkat Anda hilang, rusak, atau berganti HP, gunakan fitur Pemulihan Perangkat.',
                ]);
            }

            // Scenario B: No active device exists on server (first login or previous device was revoked)
            $newRawToken = bin2hex(random_bytes(32)); // 256-bit cryptographically secure token
            $newHash = self::hashToken($newRawToken);

            $deviceLabel = self::parseDeviceLabel($currentUserAgent);

            $newDevice = ApplicantDevice::create([
                'applicant_account_id' => $lockedAccount->id,
                'device_token_hash'    => $newHash,
                'device_label'         => $deviceLabel,
                'ip_address'           => $currentIp,
                'user_agent'           => $currentUserAgent,
                'registered_at'        => now(),
                'last_seen_at'         => now(),
                'revoked_at'           => null,
            ]);

            ApplicantDeviceAuditLog::logEvent(
                applicantAccountId: $lockedAccount->id,
                eventType: 'device_bound',
                deviceId: $newDevice->id,
                reason: "Perangkat baru berhasil diikat ({$deviceLabel})"
            );

            $cookie = $this->createPersistentCookie($newRawToken);
            return [$newDevice, $newRawToken, $cookie];
        });
    }

    /**
     * Validate current request device token against server active device.
     */
    public function validateRequestDevice(ApplicantAccount $account, Request $request): ?ApplicantDevice
    {
        $cookieToken = $request->cookie(self::COOKIE_NAME);
        if (!$cookieToken) {
            return null;
        }

        $activeDevice = ApplicantDevice::where('applicant_account_id', $account->id)
            ->whereNull('revoked_at')
            ->first();

        if (!$activeDevice) {
            return null;
        }

        if (hash_equals($activeDevice->device_token_hash, self::hashToken($cookieToken))) {
            return $activeDevice;
        }

        return null;
    }

    /**
     * Revoke active device for an account.
     */
    public function revokeActiveDevice(
        ApplicantAccount $account,
        string $reason,
        string $actorType = 'applicant',
        ?int $actorId = null
    ): void {
        DB::transaction(function () use ($account, $reason, $actorType, $actorId) {
            $activeDevice = ApplicantDevice::where('applicant_account_id', $account->id)
                ->whereNull('revoked_at')
                ->lockForUpdate()
                ->first();

            if ($activeDevice) {
                $activeDevice->revoke($reason, $actorType, $actorId);

                ApplicantDeviceAuditLog::logEvent(
                    applicantAccountId: $account->id,
                    eventType: 'device_revoked',
                    deviceId: $activeDevice->id,
                    actorType: $actorType,
                    actorId: $actorId,
                    reason: $reason
                );
            }
        });
    }

    /**
     * Recover account onto a new device after successful identity/OTP verification.
     * Revokes old device and binds the new device.
     */
    public function recoverDevice(
        ApplicantAccount $account,
        Request $request,
        string $recoveryMethod = 'email_otp'
    ): array {
        return DB::transaction(function () use ($account, $request, $recoveryMethod) {
            $lockedAccount = ApplicantAccount::where('id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Revoke any existing active device
            $oldDevices = ApplicantDevice::where('applicant_account_id', $lockedAccount->id)
                ->whereNull('revoked_at')
                ->lockForUpdate()
                ->get();

            foreach ($oldDevices as $oldDevice) {
                $oldDevice->revoke("Pelepasan perangkat via pemulihan akun ({$recoveryMethod})", 'applicant');
            }

            // 2. Generate new 256-bit token
            $newRawToken = bin2hex(random_bytes(32));
            $newHash = self::hashToken($newRawToken);
            $deviceLabel = self::parseDeviceLabel($request->userAgent());

            $newDevice = ApplicantDevice::create([
                'applicant_account_id' => $lockedAccount->id,
                'device_token_hash'    => $newHash,
                'device_label'         => $deviceLabel,
                'ip_address'           => $request->ip(),
                'user_agent'           => $request->userAgent(),
                'registered_at'        => now(),
                'last_seen_at'         => now(),
                'revoked_at'           => null,
            ]);

            ApplicantDeviceAuditLog::logEvent(
                applicantAccountId: $lockedAccount->id,
                eventType: 'device_recovered',
                deviceId: $newDevice->id,
                reason: "Perangkat berhasil dipulihkan ({$deviceLabel}, metode: {$recoveryMethod})"
            );

            $cookie = $this->createPersistentCookie($newRawToken);
            return [$newDevice, $newRawToken, $cookie];
        });
    }

    /**
     * Create Symfony persistent cookie with strong security attributes.
     */
    public function createPersistentCookie(string $rawToken): SymfonyCookie
    {
        $isSecure = (bool) config('session.secure', false) || request()->isSecure();

        return Cookie::make(
            name: self::COOKIE_NAME,
            value: $rawToken,
            minutes: self::COOKIE_LIFETIME_MINUTES,
            path: '/',
            domain: config('session.domain'),
            secure: $isSecure,
            httpOnly: true,
            raw: false,
            sameSite: 'Lax'
        );
    }

    /**
     * Create expired cookie to clear device token on client.
     */
    public function createExpiredCookie(): SymfonyCookie
    {
        return Cookie::forget(
            name: self::COOKIE_NAME,
            path: '/',
            domain: config('session.domain')
        );
    }
}
