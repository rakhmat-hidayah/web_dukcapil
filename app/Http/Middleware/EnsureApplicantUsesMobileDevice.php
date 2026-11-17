<?php

namespace App\Http\Middleware;

use App\Services\Applicant\ApplicantDeviceDetector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicantUsesMobileDevice
{
    /**
     * Handle an incoming applicant request.
     * Enforces the Mobile-Only Applicant Authentication Policy.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $detection = ApplicantDeviceDetector::detect($request);

        // 1. Mobile smartphone is strictly permitted
        if ($detection['is_mobile']) {
            return $next($request);
        }

        // 2. Log rejected non-mobile access attempts (Security Monitoring - RFC Compliance)
        // Privacy rule: Never log passwords, OTPs, auth tokens, or full NIKs.
        $this->logRejectedAccess($request, $detection);

        // 3. GET on the applicant login page is permitted with informational banner
        if ($request->isMethod('GET') && $request->routeIs('applicant.login')) {
            return $next($request);
        }

        $friendlyMessage = 'LOGIN KHUSUS PERANGKAT MOBILE: Untuk menjaga keamanan dan memastikan proses pengajuan layanan berjalan optimal, akses autentikasi akun pemohon hanya dapat dilakukan melalui smartphone (HP). Akses melalui komputer atau laptop tidak diperbolehkan.';

        // 4. For programmatic API clients expecting JSON (e.g., cURL, Postman) on GET requests:
        if ($request->isMethod('GET') && $request->expectsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'status'           => 'error',
                'code'             => 403,
                'message'          => $friendlyMessage,
                'device_category'  => $detection['category'],
                'rejection_reason' => $detection['rejection_reason'],
            ], 403);
        }

        // 5. For page navigation (standard browser GET and Inertia SPA <Link> navigations):
        // Redirect smoothly to applicant login where friendly desktop notice is presented
        if ($request->isMethod('GET')) {
            return redirect()->route('applicant.login')
                ->with('desktop_restriction', true)
                ->with('warning', 'Layanan pemohon hanya dapat diakses melalui smartphone. Silakan gunakan HP Anda untuk login atau mendaftar.');
        }

        // 6. For POST / PUT / DELETE mutation requests (login submission, registration, OTP):
        // Strictly reject desktop mutations with HTTP 403
        if ($request->header('X-Inertia')) {
            abort(403, $friendlyMessage);
        }

        return response()->json([
            'status'           => 'error',
            'code'             => 403,
            'message'          => $friendlyMessage,
            'device_category'  => $detection['category'],
            'rejection_reason' => $detection['rejection_reason'],
        ], 403);
    }

    /**
     * Log non-mobile rejection event for security audit.
     * Omits any sensitive applicant data.
     */
    protected function logRejectedAccess(Request $request, array $detection): void
    {
        try {
            Log::warning('[SECURITY][MOBILE_ONLY_POLICY_REJECTED] Non-mobile access attempt to applicant endpoint', [
                'timestamp'        => now()->toIso8601String(),
                'ip_address'       => $request->ip() ?? '127.0.0.1',
                'method'           => $request->method(),
                'path'             => $request->path(),
                'device_category'  => $detection['category'],
                'device_label'     => $detection['device_label'],
                'rejection_reason' => $detection['rejection_reason'],
                'user_agent'       => substr((string) ($request->userAgent() ?? ''), 0, 300),
                'status'           => 'BLOCKED_403',
            ]);
        } catch (\Throwable $e) {
            // Fail silently to prevent logging failures from interrupting application flow
        }
    }
}
