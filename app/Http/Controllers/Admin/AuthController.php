<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\ActivityLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AuthController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show the login view.
     */
    public function showLogin(): InertiaResponse
    {
        if (Auth::check()) {
            return redirect()->intended('/admin/dashboard');
        }

        return Inertia::render('Admin/Auth/Login', [
            'captchaImage' => CaptchaService::generateImage(),
        ]);
    }

    /**
     * Step 1: Username & Password authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'regex:/^[a-z0-9_]{4,30}$/'],
            'password' => 'required|string',
            'captcha'  => 'required|string',
            'honeypot' => 'nullable|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.regex'    => 'Format username tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'captcha.required'  => 'Kode verifikasi wajib diisi.',
        ]);

        // 1. Honeypot check (anti-bot)
        if ($request->filled('honeypot')) {
            throw ValidationException::withMessages([
                'username' => 'Terjadi kesalahan sistem.',
            ]);
        }

        // 2. IP Rate Limiting
        $throttleKey = 'login-attempt:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            ActivityLogger::log('Gagal login: Terlalu banyak percobaan dari IP', null, 'login_failed', [
                'ip'       => $request->ip(),
                'username' => $request->username,
            ]);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // 3. CAPTCHA Validation
        if (!CaptchaService::validateImage($request->captcha)) {
            throw ValidationException::withMessages([
                'captcha' => 'Kode CAPTCHA tidak cocok. Silakan coba lagi.',
            ]);
        }

        // 4. Find user by username
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($user) {
                $user->recordFailedLogin();

                if ($user->isLocked()) {
                    $minutes = (int) ceil($user->lockRemainingSeconds() / 60);

                    ActivityLogger::log("Akun dikunci setelah 5 kali gagal login: {$user->username}", $user, 'account_locked', [
                        'ip'          => $request->ip(),
                        'locked_until' => $user->locked_until->toDateTimeString(),
                    ]);

                    throw ValidationException::withMessages([
                        'username' => "Akun dikunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam {$minutes} menit.",
                    ]);
                }
            }

            RateLimiter::hit($throttleKey, 300);

            ActivityLogger::log('Gagal login: username atau password salah', null, 'login_failed', [
                'ip'       => $request->ip(),
                'username' => $request->username,
            ]);

            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi tidak valid.',
            ]);
        }

        // 5. Check active status
        if (!$user->is_active) {
            ActivityLogger::log("Gagal login: akun dinonaktifkan — {$user->username}", $user, 'login_failed', [
                'ip'       => $request->ip(),
                'username' => $user->username,
            ]);

            throw ValidationException::withMessages([
                'username' => 'Akun dinonaktifkan. Silakan hubungi Administrator.',
            ]);
        }

        // 6. Check locked status
        if ($user->isLocked()) {
            $minutes = (int) ceil($user->lockRemainingSeconds() / 60);

            throw ValidationException::withMessages([
                'username' => "Akun dikunci sementara. Silakan coba lagi dalam {$minutes} menit.",
            ]);
        }

        // 7. TWO-FACTOR AUTHENTICATION CHECK
        $requires2fa = $user->hasTwoFactorEnabled() || $user->requiresTwoFactorByRole();

        if ($requires2fa && config('two-factor.enabled', true)) {
            // Save pending login state in session
            session([
                'pending_2fa_user_id'  => $user->id,
                'pending_2fa_remember' => $request->boolean('remember'),
            ]);

            ActivityLogger::log("Username & Password valid, melanjutkan ke verifikasi 2FA — {$user->username}", $user, '2fa_challenge_prompt', [
                'ip'       => $request->ip(),
                'username' => $user->username,
            ]);

            return redirect()->route('admin.login.two-factor');
        }

        // 8. Normal login (No 2FA required for this user)
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);

        $user->recordSuccessfulLogin($request->ip());

        ActivityLogger::log("Berhasil login ke CMS — {$user->username}", $user, 'login', [
            'ip'       => $request->ip(),
            'username' => $user->username,
            'email'    => $user->email,
        ]);

        return redirect()->intended('/admin/dashboard');
    }

    /**
     * Show Step 2: Two-Factor Authentication Challenge Form.
     */
    public function showTwoFactorChallenge(Request $request): InertiaResponse|RedirectResponse
    {
        $userId = session('pending_2fa_user_id');

        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget(['pending_2fa_user_id', 'pending_2fa_remember']);
            return redirect()->route('admin.login');
        }

        return Inertia::render('Admin/Auth/TwoFactorChallenge', [
            'username' => $user->username,
            'name'     => $user->name,
        ]);
    }

    /**
     * Step 2: Verify 6-digit TOTP Code or Recovery Code.
     */
    public function verifyTwoFactorChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Kode verifikasi OTP atau Kode Pemulihan wajib diisi.',
        ]);

        $userId = session('pending_2fa_user_id');

        if (!$userId) {
            return redirect()->route('admin.login')->withErrors([
                'username' => 'Sesi autentikasi telah kedaluwarsa. Silakan masuk kembali.',
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget(['pending_2fa_user_id', 'pending_2fa_remember']);
            return redirect()->route('admin.login');
        }

        // 1. Lockout check for 2FA OTP attempts (5 max attempts per 10 minutes)
        $throttleKey = 'otp-attempt:' . $user->id;
        $maxAttempts    = config('two-factor.max_attempts', 5);
        $lockoutMinutes = config('two-factor.lockout_minutes', 10);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = (int) ceil($seconds / 60);

            ActivityLogger::log("Percobaan 2FA terkunci karena 5 kali salah OTP — {$user->username}", $user, '2fa_locked', [
                'ip'       => $request->ip(),
                'username' => $user->username,
            ]);

            throw ValidationException::withMessages([
                'code' => "Terlalu banyak percobaan 2FA yang salah. Akses terkunci selama {$minutes} menit.",
            ]);
        }

        $inputCode = trim($request->code);
        $secret    = $user->getTwoFactorSecretDecrypted();

        $isOtpValid      = $secret && $this->twoFactorService->verifyOtp($secret, $inputCode);
        $isRecoveryValid = !$isOtpValid && $user->useRecoveryCode($inputCode);

        // 2. Failed verification
        if (!$isOtpValid && !$isRecoveryValid) {
            RateLimiter::hit($throttleKey, $lockoutMinutes * 60);

            ActivityLogger::log("Gagal verifikasi 2FA TOTP / Kode Pemulihan — {$user->username}", $user, '2fa_failed', [
                'ip'       => $request->ip(),
                'username' => $user->username,
            ]);

            throw ValidationException::withMessages([
                'code' => 'Kode OTP 6 digit atau Kode Pemulihan tidak cocok.',
            ]);
        }

        // 3. Successful verification
        RateLimiter::clear($throttleKey);
        $remember = session('pending_2fa_remember', false);
        session()->forget(['pending_2fa_user_id', 'pending_2fa_remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->two_factor_last_used_at = now();
        $user->recordSuccessfulLogin($request->ip());

        $methodUsed = $isOtpValid ? 'TOTP Authenticator App' : 'Recovery Code';

        ActivityLogger::log("Berhasil verifikasi 2FA ({$methodUsed}) & masuk ke CMS — {$user->username}", $user, '2fa_success', [
            'ip'          => $request->ip(),
            'username'    => $user->username,
            'method_used' => $methodUsed,
        ]);

        return redirect()->intended('/admin/dashboard');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogger::log("Melakukan logout dari CMS — {$user->username}", $user, 'logout', [
                'username' => $user->username,
                'email'    => $user->email,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
