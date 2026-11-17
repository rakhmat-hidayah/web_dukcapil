<?php

namespace App\Http\Middleware;

use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDevice;
use App\Services\Applicant\ApplicantDeviceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicantDeviceValid
{
    public function __construct(
        protected ApplicantDeviceService $deviceService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('applicant');

        // 0. Enforce Mobile-Only Policy: Even if cookie/session exists, desktop access is strictly rejected
        if (!\App\Services\Applicant\ApplicantDeviceDetector::isMobileSmartphone($request)) {
            if ($guard->check()) {
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // For GET navigations (web browser & Inertia SPA):
            if ($request->isMethod('GET')) {
                if ($request->expectsJson() && !$request->header('X-Inertia')) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'LOGIN KHUSUS PERANGKAT MOBILE: Sesi akun pemohon hanya dapat digunakan melalui smartphone. Akses dari komputer/laptop ditolak demi keamanan sistem.',
                    ], 403);
                }

                return redirect()->route('applicant.login')
                    ->with('desktop_restriction', true)
                    ->with('warning', 'Layanan akun pemohon hanya dapat diakses melalui smartphone. Akses melalui komputer/laptop ditutup demi keamanan.');
            }

            if ($request->header('X-Inertia')) {
                abort(403, 'LOGIN KHUSUS PERANGKAT MOBILE: Sesi akun pemohon hanya dapat digunakan melalui smartphone (HP). Akses melalui komputer/laptop ditolak.');
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'LOGIN KHUSUS PERANGKAT MOBILE: Sesi akun pemohon hanya dapat digunakan melalui smartphone. Akses dari komputer/laptop ditolak demi keamanan sistem.',
            ], 403);
        }

        // 1. If not authenticated via session, check persistent device cookie for auto-login
        if (!$guard->check()) {
            $cookieToken = $request->cookie(ApplicantDeviceService::COOKIE_NAME);
            if ($cookieToken) {
                $tokenHash = ApplicantDeviceService::hashToken($cookieToken);
                $device = ApplicantDevice::where('device_token_hash', $tokenHash)
                    ->whereNull('revoked_at')
                    ->with('account')
                    ->first();

                if ($device && $device->account && in_array($device->account->account_status, [
                    ApplicantAccount::STATUS_ACTIVE,
                    ApplicantAccount::STATUS_IDENTITY_PENDING,
                ])) {
                    $guard->login($device->account);
                    $device->update(['last_seen_at' => now()]);
                }
            }
        }

        // 2. If still unauthenticated, redirect to applicant login
        if (!$guard->check()) {
            if ($request->expectsJson() && !$request->header('X-Inertia')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('applicant.login')
                ->with('error', 'Silakan masuk dengan NIK dan password Anda untuk mengakses layanan.');
        }

        /** @var ApplicantAccount $account */
        $account = $guard->user();

        // 3. Check account suspension or lock
        if ($account->isSuspended() || $account->isLocked()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $msg = $account->isSuspended()
                ? 'Akun Anda sedang ditangguhkan oleh administrator. Hubungi kantor Disdukcapil untuk informasi lebih lanjut.'
                : 'Akun Anda sedang dikunci untuk keamanan sistem.';

            return redirect()->route('applicant.login')
                ->with('error', $msg)
                ->withCookie($this->deviceService->createExpiredCookie());
        }

        // 4. Validate device token against the server's single active device
        $validDevice = $this->deviceService->validateRequestDevice($account, $request);

        if (!$validDevice) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('applicant.login')
                ->with('error', 'Sesi Anda telah berakhir atau akun telah aktif di perangkat browser lain. Sistem hanya mengizinkan 1 perangkat aktif per akun.')
                ->withCookie($this->deviceService->createExpiredCookie());
        }

        // 5. Update last seen timestamp of the active device
        $validDevice->update(['last_seen_at' => now()]);

        return $next($request);
    }
}
