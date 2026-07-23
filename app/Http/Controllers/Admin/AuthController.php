<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AuthController extends Controller
{
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
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'captcha' => 'required|string',
            'honeypot' => 'nullable|string', // Honeypot field for anti-bot
        ]);

        // 1. Honeypot check (anti-bot)
        if ($request->filled('honeypot')) {
            // Fail silently or throw validation
            throw ValidationException::withMessages([
                'email' => 'Terjadi kesalahan sistem.',
            ]);
        }

        // 2. Rate Limiting Check (max 5 attempts per IP per 5 minutes)
        $throttleKey = 'login-attempt:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            ActivityLogger::log("Gagal login: Terlalu banyak percobaan", null, 'login_failed', [
                'ip' => $request->ip(),
                'email' => $request->email,
            ]);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // 3. CAPTCHA Validation
        if (!CaptchaService::validateImage($request->captcha)) {
            RateLimiter::hit($throttleKey, 300); // 5 min block
            
            throw ValidationException::withMessages([
                'captcha' => 'Kode CAPTCHA salah atau sudah kedaluwarsa.',
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            // Log activity
            ActivityLogger::log("Berhasil login ke CMS", null, 'login', [
                'ip' => $request->ip(),
                'email' => $user->email,
            ]);

            // Check for suspicious login: e.g. login from non-standard IP ranges or just log it
            // For safety, let's notify the system about login
            // We can check if IP differs from last login or is outside Indonesia, but for testing, let's keep it basic
            // NotificationService::notifySuspiciousLogin($user, $request->ip());

            return redirect()->intended('/admin/dashboard');
        }

        // Log failed attempt
        RateLimiter::hit($throttleKey, 300);
        
        ActivityLogger::log("Gagal login: Password salah", null, 'login_failed', [
            'ip' => $request->ip(),
            'email' => $request->email,
        ]);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogger::log("Melakukan logout dari CMS", null, 'logout', [
                'email' => $user->email,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
