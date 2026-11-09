<?php

namespace App\Http\Controllers\Public\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDeviceAuditLog;
use App\Services\Applicant\ApplicantDeviceService;
use App\Services\Applicant\ApplicantOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantRecoveryController extends Controller
{
    public function __construct(
        protected ApplicantOtpService $otpService,
        protected ApplicantDeviceService $deviceService
    ) {}

    /**
     * Show device recovery / account recovery form.
     */
    public function showRecover(): Response
    {
        return Inertia::render('Public/Applicant/RecoverDevice');
    }

    /**
     * Step 1: Request recovery OTP using NIK and Password.
     */
    public function requestRecoveryOtp(Request $request): RedirectResponse
    {
        $rawNik = preg_replace('/\D/', '', (string) $request->input('nik'));
        $password = (string) $request->input('password');

        $request->merge(['nik' => $rawNik]);

        $request->validate([
            'nik'      => 'required|string|regex:/^[0-9]{16}$/',
            'password' => 'required|string',
        ], [
            'nik.required'      => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.regex'         => 'NIK wajib tepat 16 digit angka.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $nikHash = ApplicantAccount::computeNikHash($rawNik);
        $limiterKey = 'applicant-recovery-req:' . sha1($request->ip() . '|' . $nikHash);

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            throw ValidationException::withMessages([
                'nik' => "Terlalu banyak permintaan pemulihan. Silakan tunggu {$seconds} detik.",
            ]);
        }

        $account = ApplicantAccount::where('nik_hash', $nikHash)->first();

        if (!$account || !Hash::check($password, $account->password)) {
            RateLimiter::hit($limiterKey, 300);
            throw ValidationException::withMessages([
                'nik' => 'Nomor Induk Kependudukan (NIK) atau password yang Anda masukkan salah.',
            ]);
        }

        if ($account->isSuspended() || $account->isLocked()) {
            throw ValidationException::withMessages([
                'nik' => 'Akun Anda sedang ditangguhkan. Pemulihan mandiri tidak dapat dilakukan. Hubungi loket Disdukcapil.',
            ]);
        }

        // Send OTP to the account's registered email
        $this->otpService->generateAndSend($account->email_normalized, 'device_recovery', $account);

        session([
            'applicant_recovery_id'    => $account->id,
            'applicant_recovery_email' => $account->email_normalized,
        ]);

        RateLimiter::clear($limiterKey);

        return back()->with([
            'otpSent'     => true,
            'maskedEmail' => $account->masked_email,
            'message'     => 'Kode OTP pemulihan telah dikirimkan ke alamat email terdaftar Anda.',
        ]);
    }

    /**
     * Step 2: Confirm recovery OTP, revoke previous device, bind new device, and log in.
     */
    public function completeRecovery(Request $request): RedirectResponse
    {
        $accountId = session('applicant_recovery_id');
        $email = session('applicant_recovery_email');

        if (!$accountId || !$email) {
            return redirect()->route('applicant.recover')
                ->with('error', 'Sesi pemulihan telah berakhir. Silakan ulangi langkah pemulihan.');
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP pemulihan wajib diisi.',
            'otp.size'     => 'Kode OTP terdiri dari 6 digit angka.',
        ]);

        $otp = trim((string) $request->input('otp'));

        // Verify recovery OTP
        $this->otpService->verify($email, $otp, 'device_recovery');

        $account = ApplicantAccount::findOrFail($accountId);

        // Revoke old device and bind current browser
        [$newDevice, $rawToken, $cookie] = $this->deviceService->recoverDevice(
            account: $account,
            request: $request,
            recoveryMethod: 'email_otp'
        );

        session()->forget(['applicant_recovery_id', 'applicant_recovery_email']);

        // Regenerate session and log in
        $request->session()->regenerate();
        Auth::guard('applicant')->login($account);

        return redirect()->route('applicant.dashboard')
            ->withCookie($cookie)
            ->with('success', 'Perangkat lama berhasil dilepaskan dan browser ini telah diikat sebagai perangkat aktif Anda.');
    }
}
