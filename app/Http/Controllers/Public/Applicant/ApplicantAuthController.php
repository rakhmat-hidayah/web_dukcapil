<?php

namespace App\Http\Controllers\Public\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDeviceAuditLog;
use App\Models\Queue\QueueSetting;
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

class ApplicantAuthController extends Controller
{
    public function __construct(
        protected ApplicantOtpService $otpService,
        protected ApplicantDeviceService $deviceService
    ) {}

    /**
     * Show registration page.
     */
    public function showRegister(): Response|RedirectResponse
    {
        if (Auth::guard('applicant')->check()) {
            return redirect()->route('applicant.dashboard');
        }

        if (in_array((string) QueueSetting::get('queue.maintenance_mode', '0'), ['1', 'true'], true)) {
            return redirect()->route('applicant.login')
                ->with('warning', 'Pendaftaran akun pemohon baru sedang ditutup sementara karena layanan online sedang dalam pemeliharaan sistem.');
        }

        return Inertia::render('Public/Applicant/Register');
    }

    /**
     * Handle applicant account registration.
     */
    public function register(Request $request): RedirectResponse
    {
        if (in_array((string) QueueSetting::get('queue.maintenance_mode', '0'), ['1', 'true'], true)) {
            return redirect()->route('applicant.login')
                ->with('error', 'Pendaftaran akun pemohon baru sedang ditutup sementara karena layanan online sedang dalam pemeliharaan sistem.');
        }

        // 1. Sanitize demographic and email inputs
        $rawNik = preg_replace('/\D/', '', (string) $request->input('nik'));
        $rawEmail = strtolower(trim((string) $request->input('email')));
        $rawName = mb_strtoupper(trim((string) $request->input('name')));

        $request->merge([
            'nik'   => $rawNik,
            'email' => $rawEmail,
            'name'  => $rawName,
        ]);

        $request->validate([
            'nik' => [
                'required',
                'string',
                'regex:/^[0-9]{16}$/',
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:60',
                'regex:/^[A-Z\s\'\-]+$/',
            ],
            'email' => [
                'required',
                'email',
                'max:100',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'nik.required'       => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.regex'          => 'NIK wajib tepat 16 digit angka numerik.',
            'name.required'      => 'Nama lengkap wajib diisi sesuai KTP/KK.',
            'name.regex'         => 'Nama hanya boleh memuat huruf kapital, spasi, tanda petik (\'), atau tanda hubung (-).',
            'email.required'     => 'Alamat email aktif wajib diisi untuk verifikasi OTP.',
            'email.email'        => 'Format alamat email tidak valid.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // 2. Check NIK uniqueness using keyed blind index HMAC
        $nikHash = ApplicantAccount::computeNikHash($rawNik);
        if (ApplicantAccount::where('nik_hash', $nikHash)->exists()) {
            throw ValidationException::withMessages([
                'nik' => 'NIK ini telah terdaftar dalam sistem. Silakan masuk atau gunakan fitur Pemulihan Akun.',
            ]);
        }

        // 3. Check normalized email uniqueness
        if (ApplicantAccount::where('email_normalized', $rawEmail)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Alamat email ini telah digunakan oleh akun lain. Gunakan alamat email aktif lainnya.',
            ]);
        }

        // 4. Create applicant account with EMAIL_PENDING status
        $account = new ApplicantAccount();
        $account->name = $rawName;
        $account->email_normalized = $rawEmail;
        $account->nik = $rawNik; // Mutator automatically encrypts and sets blind index
        $account->password = Hash::make($request->password);
        $account->identity_status = ApplicantAccount::IDENTITY_PENDING;
        $account->account_status = ApplicantAccount::STATUS_EMAIL_PENDING;
        $account->save();

        // 5. Generate and dispatch email OTP
        $this->otpService->generateAndSend($rawEmail, 'registration', $account);

        // 6. Record audit log
        ApplicantDeviceAuditLog::logEvent(
            applicantAccountId: $account->id,
            eventType: 'registered',
            reason: 'Pendaftaran akun pemohon baru'
        );

        session(['applicant_verification_email' => $rawEmail]);

        return redirect()->route('applicant.verify-otp')
            ->with('success', 'Pendaftaran berhasil. Kode OTP verifikasi telah dikirimkan ke alamat email Anda.');
    }

    /**
     * Show email OTP verification page.
     */
    public function showVerifyOtp(Request $request): Response|RedirectResponse
    {
        $email = session('applicant_verification_email', (string) $request->query('email', ''));

        if (!$email) {
            return redirect()->route('applicant.register')
                ->with('warning', 'Silakan lakukan pendaftaran terlebih dahulu.');
        }

        return Inertia::render('Public/Applicant/VerifyOtp', [
            'email' => $email,
        ]);
    }

    /**
     * Handle email OTP verification.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size'     => 'Kode OTP terdiri dari 6 digit angka.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $otp = trim((string) $request->input('otp'));

        // Verify OTP code
        $this->otpService->verify($email, $otp, 'registration');

        $account = ApplicantAccount::where('email_normalized', $email)->firstOrFail();

        // Per business rule: OTP email success only verifies email, status becomes IDENTITY_PENDING
        $account->update([
            'email_verified_at' => now(),
            'account_status'    => ApplicantAccount::STATUS_IDENTITY_PENDING,
        ]);

        ApplicantDeviceAuditLog::logEvent(
            applicantAccountId: $account->id,
            eventType: 'email_verified',
            reason: 'Verifikasi email OTP berhasil'
        );

        session()->forget('applicant_verification_email');

        $rawNik = $account->nik;

        return redirect()->route('applicant.login', $rawNik ? ['nik' => $rawNik] : [])
            ->with('success', 'Pendaftaran akun Anda berhasil dan email telah terverifikasi! Silakan masuk menggunakan NIK dan Password yang telah Anda daftarkan.');
    }

    /**
     * Resend registration email OTP.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $email = strtolower(trim((string) $request->input('email', session('applicant_verification_email', ''))));

        if (!$email) {
            return redirect()->route('applicant.register');
        }

        $account = ApplicantAccount::where('email_normalized', $email)->first();
        if (!$account) {
            return back()->withErrors(['email' => 'Akun tidak ditemukan.']);
        }

        if ($account->isEmailVerified()) {
            return redirect()->route('applicant.login')->with('info', 'Email Anda sudah terverifikasi. Silakan masuk.');
        }

        $this->otpService->generateAndSend($email, 'registration', $account);

        return back()->with('success', 'Kode OTP baru telah dikirimkan ke email Anda.');
    }

    /**
     * Show applicant login page.
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::guard('applicant')->check()) {
            return redirect()->route('applicant.dashboard');
        }

        $isMaintenance = in_array((string) QueueSetting::get('queue.maintenance_mode', '0'), ['1', 'true'], true);

        return Inertia::render('Public/Applicant/Login', [
            'maintenance' => [
                'is_active'     => $isMaintenance,
                'title'         => QueueSetting::get('queue.maintenance_title', 'Layanan Pendaftaran Online KTP-el Sedang Dalam Pemeliharaan Sistem'),
                'message'       => QueueSetting::get('queue.maintenance_message', 'Mohon maaf, saat ini pendaftaran dan antrian online KTP-el Dinas Dukcapil Kabupaten Dompu sedang dinonaktifkan sementara untuk pemeliharaan sistem.'),
                'estimated_end' => QueueSetting::get('queue.maintenance_estimated_end', ''),
            ],
        ]);
    }

    /**
     * Handle applicant login with NIK + Password and strict single device binding.
     */
    public function login(Request $request): RedirectResponse
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

        // Rate limit login attempts based on IP and NIK hash to prevent brute force & enumeration
        $nikHash = ApplicantAccount::computeNikHash($rawNik);
        $limiterKey = 'applicant-login:' . sha1($request->ip() . '|' . $nikHash);

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            throw ValidationException::withMessages([
                'nik' => "Terlalu banyak percobaan masuk yang salah. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Look up applicant by HMAC blind index
        $account = ApplicantAccount::where('nik_hash', $nikHash)->first();

        // Generic error message if user not found or password incorrect
        if (!$account || !Hash::check($password, $account->password)) {
            RateLimiter::hit($limiterKey, 300);

            if ($account) {
                ApplicantDeviceAuditLog::logEvent(
                    applicantAccountId: $account->id,
                    eventType: 'login_failed_bad_password',
                    reason: 'Percobaan login gagal: password salah'
                );
            }

            throw ValidationException::withMessages([
                'nik' => 'Nomor Induk Kependudukan (NIK) atau password yang Anda masukkan salah.',
            ]);
        }

        // Check if email has been verified
        if (!$account->isEmailVerified()) {
            session(['applicant_verification_email' => $account->email_normalized]);
            return redirect()->route('applicant.verify-otp')
                ->with('warning', 'Alamat email Anda belum diverifikasi. Masukkan kode OTP yang telah dikirimkan.');
        }

        // Check account suspension or lock
        if ($account->isSuspended() || $account->isLocked()) {
            $msg = $account->isSuspended()
                ? 'Akun Anda sedang ditangguhkan oleh administrator. Hubungi Disdukcapil Kabupaten Dompu untuk informasi lebih lanjut.'
                : 'Akun Anda sedang dikunci untuk keamanan sistem.';
            throw ValidationException::withMessages(['nik' => $msg]);
        }

        // Enforce ONE ACCOUNT ONE ACTIVE BROWSER with DB transaction & row locking
        [$device, $rawToken, $cookie] = $this->deviceService->bindOrValidateOnLogin($account, $request);

        RateLimiter::clear($limiterKey);

        // Regenerate session ID to prevent session fixation attacks
        $request->session()->regenerate();

        // Log into dedicated applicant guard
        Auth::guard('applicant')->login($account);

        $intended = session()->pull('url.intended');
        if ($intended && !str_contains($intended, '/layanan/pemohon/masuk')) {
            return redirect()->to($intended)
                ->withCookie($cookie)
                ->with('success', "Selamat datang, {$account->name}!");
        }

        return redirect()->route('applicant.dashboard')
            ->withCookie($cookie)
            ->with('success', "Selamat datang, {$account->name}!");
    }
}
