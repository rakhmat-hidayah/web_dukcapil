<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
        // Set window to 1 (tolerate +-30 seconds)
        $this->google2fa->setWindow(config('two-factor.window', 1));
    }

    /**
     * Generate a new 16-character Base32 TOTP secret key.
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey(16);
    }

    /**
     * Generate standard otpauth:// URI.
     */
    public function getOtpAuthUrl(User $user, string $secret): string
    {
        $issuer   = config('two-factor.issuer', 'Disdukcapil Kabupaten Dompu');
        $username = $user->username ?: $user->email;

        return $this->google2fa->getQRCodeUrl($issuer, $username, $secret);
    }

    /**
     * Generate inline SVG QR Code string for offline scanning (no third party API).
     */
    public function getQrCodeSvg(User $user, string $secret): string
    {
        $otpauthUrl = $this->getOtpAuthUrl($user, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(220, 2),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($otpauthUrl);
    }

    /**
     * Verify a 6-digit TOTP code against a secret key.
     */
    public function verifyOtp(string $secret, string $otp): bool
    {
        $cleanOtp = preg_replace('/\s+/', '', $otp);
        if (strlen($cleanOtp) !== 6 || !ctype_digit($cleanOtp)) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $cleanOtp, config('two-factor.window', 1));
    }

    /**
     * Confirm and enable 2FA for a user.
     */
    public function enableTwoFactor(User $user, string $secret, string $otp): array
    {
        if (!$this->verifyOtp($secret, $otp)) {
            throw ValidationException::withMessages([
                'otp' => 'Kode verifikasi 6 digit tidak cocok. Silakan periksa aplikasi Authenticator Anda.',
            ]);
        }

        $user->setTwoFactorSecretEncrypted($secret);
        $user->two_factor_enabled      = true;
        $user->two_factor_confirmed_at = now();
        $user->two_factor_last_used_at = now();
        $user->save();

        // Generate 10 recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();

        ActivityLogger::log(
            "Mengaktifkan Autentikasi Dua Faktor (2FA TOTP)",
            $user,
            '2fa_enabled',
            ['username' => $user->username]
        );

        return $recoveryCodes;
    }

    /**
     * Disable 2FA with current password & OTP verification.
     */
    public function disableTwoFactor(User $user, string $password, string $otp): void
    {
        // 1. Verify current password
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Kata sandi saat ini tidak valid.',
            ]);
        }

        // 2. Verify current OTP or recovery code
        $secret = $user->getTwoFactorSecretDecrypted();
        $otpValid = $secret && $this->verifyOtp($secret, $otp);
        $recoveryValid = !$otpValid && $user->useRecoveryCode($otp);

        if (!$otpValid && !$recoveryValid) {
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP atau Kode Pemulihan tidak valid.',
            ]);
        }

        // 3. Clear 2FA
        $user->two_factor_enabled       = false;
        $user->two_factor_secret        = null;
        $user->two_factor_confirmed_at  = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        ActivityLogger::log(
            "Merapikan & Dinonaktifkan Autentikasi Dua Faktor (2FA TOTP)",
            $user,
            '2fa_disabled',
            ['username' => $user->username]
        );
    }

    /**
     * Admin reset 2FA for another user.
     * Requires Admin's own password + Admin's own OTP + Reason.
     */
    public function adminResetTwoFactor(
        User $adminUser,
        User $targetUser,
        string $adminPassword,
        string $adminOtp,
        string $reason
    ): void {
        // 1. Verify admin password
        if (!Hash::check($adminPassword, $adminUser->password)) {
            throw ValidationException::withMessages([
                'admin_password' => 'Kata sandi Anda tidak valid.',
            ]);
        }

        // 2. If admin has 2FA enabled, verify admin OTP
        if ($adminUser->hasTwoFactorEnabled()) {
            $adminSecret = $adminUser->getTwoFactorSecretDecrypted();
            if (!$adminSecret || !$this->verifyOtp($adminSecret, $adminOtp)) {
                throw ValidationException::withMessages([
                    'admin_otp' => 'Kode OTP verifikasi Anda tidak valid.',
                ]);
            }
        }

        // 3. Reset target user 2FA
        $targetUser->two_factor_enabled        = false;
        $targetUser->two_factor_secret         = null;
        $targetUser->two_factor_confirmed_at   = null;
        $targetUser->two_factor_recovery_codes  = null;
        $targetUser->save();

        ActivityLogger::log(
            "Super Admin me-reset 2FA pengguna: {$targetUser->username} — Alasan: {$reason}",
            $targetUser,
            '2fa_admin_reset',
            [
                'admin_username'  => $adminUser->username,
                'target_username' => $targetUser->username,
                'reason'          => $reason,
            ]
        );
    }
}
