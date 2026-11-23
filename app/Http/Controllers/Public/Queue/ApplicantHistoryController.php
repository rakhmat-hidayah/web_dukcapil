<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Queue\QueueApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantHistoryController extends Controller
{
    /**
     * Display applicant history page.
     * Shows all past and active applications if verified; otherwise shows secure identification challenge.
     */
    public function index(Request $request): Response
    {
        $verifiedNik = $this->resolveVerifiedNik($request);

        if (!$verifiedNik) {
            return Inertia::render('Public/Queue/History', [
                'isVerified'   => false,
                'initialNik'   => (string) $request->query('nik', ''),
                'applications' => [],
            ]);
        }

        $applications = QueueApplication::where('applicant_nik', $verifiedNik)
            ->with(['serviceType', 'slot.schedule'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($app) {
                $schedDate = $app->slot?->schedule?->schedule_date;
                $schedDateFormatted = null;
                if ($schedDate) {
                    $schedDateFormatted = $schedDate instanceof \Carbon\Carbon
                        ? $schedDate->translatedFormat('l, d F Y')
                        : \Carbon\Carbon::parse($schedDate)->translatedFormat('l, d F Y');
                }

                $schedTime = null;
                if ($app->slot?->schedule) {
                    $open = substr($app->slot->schedule->open_time, 0, 5);
                    $close = substr($app->slot->schedule->close_time, 0, 5);
                    $schedTime = "{$open} - {$close} WITA";
                }

                return [
                    'id'                      => $app->id,
                    'reg_number'              => $app->reg_number,
                    'access_token'            => $app->access_token,
                    'service_name'            => $app->serviceType?->name ?? 'Pelayanan KTP-el',
                    'service_color'           => $app->serviceType?->color ?? '#2563eb',
                    'created_at'              => $app->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted'    => $app->created_at->translatedFormat('d M Y, H:i') . ' WITA',
                    'submitted_at'            => $app->submitted_at?->translatedFormat('d M Y, H:i') . ' WITA',
                    'status'                  => $app->status,
                    'status_label'            => $app->status_label,
                    'status_color'            => $app->status_color,
                    'queue_number'            => $app->queue_number,
                    'schedule_date'           => $schedDate instanceof \Carbon\Carbon ? $schedDate->format('Y-m-d') : (string) $schedDate,
                    'schedule_date_formatted' => $schedDateFormatted,
                    'schedule_time'           => $schedTime,
                    'session_name'            => $app->slot?->schedule?->session_name,
                    'detail_url'              => route('queue.status', ['token' => $app->access_token]),
                    'is_represented'          => $app->is_represented,
                    'attendance_label'        => $app->attendance_label,
                    'applicant_name'          => $app->applicant_name,
                ];
            });

        $nikHash = ApplicantAccount::computeNikHash($verifiedNik);
        $account = ApplicantAccount::where('nik_hash', $nikHash)->first();

        $firstApp = $applications->first();
        $applicantName = $account?->name ?: ($firstApp ? $firstApp['applicant_name'] : 'Pemohon');

        $maskedNik = strlen($verifiedNik) >= 16
            ? substr($verifiedNik, 0, 6) . '******' . substr($verifiedNik, 12)
            : substr($verifiedNik, 0, 4) . '****';

        return Inertia::render('Public/Queue/History', [
            'isVerified'    => true,
            'maskedNik'     => $maskedNik,
            'applicantName' => $applicantName,
            'applications'  => $applications,
        ]);
    }

    /**
     * Verify applicant using NIK (16 digits).
     */
    public function verify(Request $request): RedirectResponse
    {
        $limiterKey = 'queue-history-verify:' . $request->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 6)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            return back()->withErrors([
                'nik' => "Terlalu banyak percobaan identifikasi. Harap tunggu {$seconds} detik sebelum mencoba kembali.",
            ])->withInput();
        }

        $validated = $request->validate([
            'nik'      => 'required|digits:16',
            'remember' => 'nullable|boolean',
        ], [
            'nik.required'    => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.digits'      => 'NIK harus berjumlah tepat 16 digit angka.',
        ]);

        $cleanNik = preg_replace('/[^0-9]/', '', $validated['nik']);

        // 1. Cek status akun pemohon jika terdaftar dalam sistem
        $nikHash = ApplicantAccount::computeNikHash($cleanNik);
        $account = ApplicantAccount::where('nik_hash', $nikHash)->first();

        if ($account) {
            if ($account->isSuspended()) {
                RateLimiter::hit($limiterKey, 60);
                return back()->withErrors([
                    'nik' => 'Akun pemohon dengan NIK ini sedang ditangguhkan oleh administrator. Riwayat permohonan tidak dapat diakses.',
                ])->withInput();
            }

            if ($account->isLocked()) {
                RateLimiter::hit($limiterKey, 60);
                return back()->withErrors([
                    'nik' => 'Akun pemohon dengan NIK ini sedang dikunci untuk keamanan sistem.',
                ])->withInput();
            }

            if (!$account->isActive() || $account->identity_status !== ApplicantAccount::IDENTITY_VERIFIED) {
                RateLimiter::hit($limiterKey, 60);
                return back()->withErrors([
                    'nik' => 'Akun pemohon dengan NIK ini belum aktif atau masih dalam proses verifikasi identitas oleh petugas Disdukcapil.',
                ])->withInput();
            }
        }

        // 2. Cek apakah ada riwayat permohonan antrian
        $candidates = QueueApplication::where('applicant_nik', $cleanNik)->get();

        if ($candidates->isEmpty()) {
            RateLimiter::hit($limiterKey, 60);

            $errorMsg = $account
                ? 'Tidak ditemukan riwayat permohonan dengan NIK ini. Anda dapat mengajukan permohonan baru melalui Portal Pemohon.'
                : 'Nomor Induk Kependudukan (NIK) belum terdaftar sebagai akun pemohon. Silakan buat akun terlebih dahulu.';

            return back()->withErrors([
                'nik' => $errorMsg,
            ])->withInput();
        }

        RateLimiter::clear($limiterKey);

        // Store session
        $request->session()->put('applicant_verified_nik', $cleanNik);
        $request->session()->put('applicant_verified_at', now()->timestamp);

        // If requested to remember device, store encrypted cookie valid for 30 days
        if ($request->boolean('remember')) {
            $cookiePayload = encrypt([
                'nik'        => $cleanNik,
                'expires_at' => now()->addDays(30)->timestamp,
            ]);
            Cookie::queue('applicant_device_auth', $cookiePayload, 60 * 24 * 30);
        }

        return redirect()->route('queue.history')->with('success', 'Identitas pemohon terverifikasi. Seluruh riwayat permohonan Anda berhasil dimuat.');
    }

    /**
     * Terminate the applicant history viewing session (logout / forget device).
     */
    public function forget(Request $request): RedirectResponse
    {
        $request->session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
        Cookie::queue(Cookie::forget('applicant_device_auth'));

        return redirect()->route('queue.history')->with('info', 'Sesi riwayat permohonan telah ditutup demi keamanan data pribadi Anda.');
    }

    /**
     * Resolve verified NIK from active session or encrypted 30-day cookie.
     * Enforces that if an ApplicantAccount exists, it must be ACTIVE and VERIFIED.
     * If account is SUSPENDED, LOCKED, or NOT ACTIVE, session/cookie is revoked.
     */
    protected function resolveVerifiedNik(Request $request): ?string
    {
        // 1. Check direct session
        $sessionNik = $request->session()->get('applicant_verified_nik');
        if (!empty($sessionNik) && strlen($sessionNik) === 16) {
            return $this->validateAccountStatusForNik($request, $sessionNik);
        }

        // 2. Check encrypted remember cookie
        $cookieValue = $request->cookie('applicant_device_auth');
        if ($cookieValue) {
            try {
                $payload = decrypt($cookieValue);
                if (is_array($payload) && !empty($payload['nik']) && isset($payload['expires_at'])) {
                    if (now()->timestamp <= $payload['expires_at']) {
                        return $this->validateAccountStatusForNik($request, $payload['nik']);
                    }
                }
            } catch (\Throwable $e) {
                // Invalid or tampered cookie, ignore safely
            }
        }

        return null;
    }

    /**
     * Validate that if an applicant account exists for the given NIK,
     * it must be active and not suspended/locked. If inactive or suspended,
     * immediately flush the session and cookie.
     */
    protected function validateAccountStatusForNik(Request $request, string $nik): ?string
    {
        $nikHash = ApplicantAccount::computeNikHash($nik);
        $account = ApplicantAccount::where('nik_hash', $nikHash)->first();

        if ($account) {
            // Jika akun ada tapi tidak aktif (SUSPENDED, LOCKED, belum diverifikasi identitas, atau status bukan ACTIVE)
            if (
                !$account->isActive()
                || $account->isSuspended()
                || $account->isLocked()
                || $account->identity_status !== ApplicantAccount::IDENTITY_VERIFIED
            ) {
                $request->session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
                Cookie::queue(Cookie::forget('applicant_device_auth'));
                return null;
            }
        }

        // Jika tidak ada akun terdaftar DAN juga tidak ada permohonan antrian (sesi yatim / draf usang), bersihkan sesi
        $hasApplications = QueueApplication::where('applicant_nik', $nik)->exists();
        if (!$account && !$hasApplications) {
            $request->session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            Cookie::queue(Cookie::forget('applicant_device_auth'));
            return null;
        }

        // Pastikan tersimpan di session
        $request->session()->put('applicant_verified_nik', $nik);
        $request->session()->put('applicant_verified_at', now()->timestamp);

        return $nik;
    }
}
