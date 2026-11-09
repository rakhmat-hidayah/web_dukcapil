<?php

namespace App\Http\Middleware;

use App\Models\Applicant\ApplicantAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicantIdentityVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('applicant');

        if (!$guard->check()) {
            return redirect()->route('applicant.login');
        }

        /** @var ApplicantAccount $account */
        $account = $guard->user();

        // Only ACTIVE and VERIFIED accounts may access full application submission features
        if (!$account->isActive() || !$account->isIdentityVerified()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun Anda berstatus IDENTITY_PENDING. Pengajuan layanan online memerlukan verifikasi identitas resmi oleh petugas Disdukcapil.',
                ], 403);
            }

            return redirect()->route('applicant.dashboard')
                ->with('warning', 'Akun Anda berstatus IDENTITY_PENDING (Menunggu Verifikasi Identitas Petugas). Anda dapat memantau status Anda di sini, namun pengajuan layanan baru memerlukan verifikasi resmi terlebih dahulu.');
        }

        return $next($request);
    }
}
