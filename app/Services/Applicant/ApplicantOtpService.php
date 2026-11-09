<?php

namespace App\Services\Applicant;

use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantEmailOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ApplicantOtpService
{
    const OTP_EXPIRY_MINUTES = 10;
    const MAX_ATTEMPTS = 3;
    const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Generate and dispatch a secure 6-digit email OTP.
     */
    public function generateAndSend(
        string $email,
        string $purpose = 'registration',
        ?ApplicantAccount $account = null
    ): ApplicantEmailOtp {
        $email = strtolower(trim($email));

        // Enforce resend rate limit based on email and IP
        $limiterKey = 'applicant-otp-send:' . sha1($email . '|' . request()->ip());
        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            throw ValidationException::withMessages([
                'otp' => "Terlalu banyak permintaan kode OTP. Silakan tunggu {$seconds} detik lagi.",
            ]);
        }
        RateLimiter::hit($limiterKey, 300); // 5 attempts per 5 minutes

        // Invalidate any existing unconsumed OTPs for this email and purpose
        ApplicantEmailOtp::where('email_normalized', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        // Generate cryptographically secure 6-digit numeric OTP
        $plainOtp = sprintf('%06d', random_int(100000, 999999));
        $otpHashed = Hash::make($plainOtp);

        $otpRecord = ApplicantEmailOtp::create([
            'applicant_account_id' => $account?->id,
            'email_normalized'     => $email,
            'otp_hash'             => $otpHashed,
            'purpose'              => $purpose,
            'expires_at'           => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts'             => 0,
            'max_attempts'         => self::MAX_ATTEMPTS,
            'resend_count'         => 0,
        ]);

        // Send email with official Disdukcapil layout
        $this->sendEmail($email, $plainOtp, $purpose, $account?->name);

        return $otpRecord;
    }

    /**
     * Verify an entered OTP.
     */
    public function verify(
        string $email,
        string $enteredOtp,
        string $purpose = 'registration'
    ): ApplicantEmailOtp {
        $email = strtolower(trim($email));
        $enteredOtp = trim($enteredOtp);

        $limiterKey = 'applicant-otp-verify:' . sha1($email . '|' . request()->ip());
        if (RateLimiter::tooManyAttempts($limiterKey, 6)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            throw ValidationException::withMessages([
                'otp' => "Terlalu banyak percobaan verifikasi yang salah. Silakan tunggu {$seconds} detik.",
            ]);
        }

        $otpRecord = ApplicantEmailOtp::where('email_normalized', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            RateLimiter::hit($limiterKey, 60);
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP tidak ditemukan atau telah kedaluwarsa. Silakan kirim ulang kode OTP.',
            ]);
        }

        if ($otpRecord->isExpired()) {
            $otpRecord->consume();
            RateLimiter::hit($limiterKey, 60);
            throw ValidationException::withMessages([
                'otp' => 'Kode OTP telah kedaluwarsa. Silakan minta kode OTP baru.',
            ]);
        }

        if ($otpRecord->hasExceededAttempts()) {
            $otpRecord->consume();
            RateLimiter::hit($limiterKey, 300);
            throw ValidationException::withMessages([
                'otp' => 'Batas maksimal percobaan verifikasi telah terlampaui. Silakan minta kode OTP baru.',
            ]);
        }

        if (!$otpRecord->verify($enteredOtp)) {
            RateLimiter::hit($limiterKey, 60);
            $remaining = max(0, $otpRecord->max_attempts - $otpRecord->attempts);
            throw ValidationException::withMessages([
                'otp' => "Kode OTP salah. Sisa kesempatan mencoba: {$remaining} kali.",
            ]);
        }

        // Successfully verified: consume OTP
        $otpRecord->consume();
        RateLimiter::clear($limiterKey);

        return $otpRecord;
    }

    /**
     * Dispatch email safely.
     */
    protected function sendEmail(string $recipientEmail, string $otp, string $purpose, ?string $applicantName): void
    {
        $subject = match ($purpose) {
            'device_recovery' => 'Kode Pemulihan Perangkat - Disdukcapil Kabupaten Dompu',
            'password_reset'  => 'Kode Reset Password - Disdukcapil Kabupaten Dompu',
            default           => 'Kode Verifikasi Pendaftaran Akun - Disdukcapil Kabupaten Dompu',
        };

        try {
            Mail::send([], [], function ($message) use ($recipientEmail, $otp, $subject, $applicantName, $purpose) {
                $displayName = $applicantName ?: 'Warga / Pemohon';
                $bodyHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$subject}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px;">
  <div style="max-width: 540px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div style="background-color: #1e3a8a; padding: 24px; text-align: center; color: #ffffff;">
      <h2 style="margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">DISDUKCAPIL KABUPATEN DOMPU</h2>
      <p style="margin: 4px 0 0; font-size: 13px; color: #bfdbfe;">Pelayanan Administrasi Kependudukan Online</p>
    </div>
    <div style="padding: 32px 24px; color: #1e293b; line-height: 1.6;">
      <p style="margin-top: 0; font-size: 14px;">Halo <strong>{$displayName}</strong>,</p>
      <p style="font-size: 13px; color: #475569;">Gunakan kode OTP berikut untuk melanjutkan proses verifikasi di portal layanan online Disdukcapil Kabupaten Dompu:</p>
      <div style="margin: 28px 0; text-align: center;">
        <span style="display: inline-block; background-color: #f1f5f9; border: 2px dashed #2563eb; padding: 14px 28px; font-size: 32px; font-weight: 900; letter-spacing: 6px; color: #1e40af; border-radius: 12px; font-family: monospace;">{$otp}</span>
      </div>
      <p style="font-size: 12px; color: #64748b; margin-bottom: 4px;"><strong>Penting:</strong></p>
      <ul style="font-size: 12px; color: #64748b; margin-top: 4px; padding-left: 18px;">
        <li>Kode ini berlaku selama <strong>10 menit</strong>.</li>
        <li>Hanya dapat digunakan sebanyak 1 kali.</li>
        <li>Jangan bagikan kode ini kepada siapa pun, termasuk petugas Disdukcapil.</li>
      </ul>
      <p style="font-size: 12px; color: #94a3b8; margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 16px;">Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.</p>
    </div>
    <div style="background-color: #f8fafc; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
      Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu<br>
      Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211
    </div>
  </div>
</body>
</html>
HTML;
                $message->to($recipientEmail)
                    ->subject($subject)
                    ->html($bodyHtml);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send applicant OTP email: ' . $e->getMessage(), [
                'email'   => $recipientEmail,
                'purpose' => $purpose,
            ]);
        }
    }
}
