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
            if ($request->expectsJson()) {
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
                ->with('error', 'Sesi perangkat Anda tidak valid atau telah dicabut oleh administrator / proses pemulihan akun.')
                ->withCookie($this->deviceService->createExpiredCookie());
        }

        // 5. Throttled update of last_seen_at (max once every 5 minutes per session)
        $lastRecorded = session('applicant_device_last_seen', 0);
        if (now()->timestamp - $lastRecorded > 300) {
            $validDevice->update(['last_seen_at' => now()]);
            session(['applicant_device_last_seen' => now()->timestamp]);
        }

        return $next($request);
    }
}
