<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
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

        $firstApp = $applications->first();
        $applicantName = $firstApp ? $firstApp['applicant_name'] : 'Pemohon';

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
     * Verify applicant using Dual Verification Factors:
     * 1. NIK (16 digits)
     * 2. Registered WhatsApp / Phone Number
     */
    public function verify(Request $request): RedirectResponse
    {
        $limiterKey = 'queue-history-verify:' . $request->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 6)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            return back()->withErrors([
                'phone' => "Terlalu banyak percobaan identifikasi. Harap tunggu {$seconds} detik sebelum mencoba kembali.",
            ])->withInput();
        }

        $validated = $request->validate([
            'nik'      => 'required|digits:16',
            'phone'    => 'required|string|min:8|max:20',
            'remember' => 'nullable|boolean',
        ], [
            'nik.required'    => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.digits'      => 'NIK harus berjumlah tepat 16 digit angka.',
            'phone.required'  => 'Nomor WhatsApp / HP yang didaftarkan wajib diisi.',
            'phone.min'       => 'Nomor telepon minimal 8 digit.',
        ]);

        $cleanNik = preg_replace('/[^0-9]/', '', $validated['nik']);
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $normalizedInputPhone = ltrim(ltrim($cleanPhone, '0'), '62');

        $candidates = QueueApplication::where('applicant_nik', $cleanNik)->get();

        if ($candidates->isEmpty()) {
            RateLimiter::hit($limiterKey, 60);
            return back()->withErrors([
                'nik' => 'Tidak ditemukan permohonan dengan NIK ini. Periksa kembali NIK yang Anda masukkan.',
            ])->withInput();
        }

        // Match against applicant_phone or representative_phone
        $matched = $candidates->first(function ($app) use ($normalizedInputPhone) {
            $appPhone = preg_replace('/[^0-9]/', '', (string) $app->applicant_phone);
            $normApp = ltrim(ltrim($appPhone, '0'), '62');

            $repPhone = preg_replace('/[^0-9]/', '', (string) $app->representative_phone);
            $normRep = ltrim(ltrim($repPhone, '0'), '62');

            $matchApp = !empty($normApp) && (str_ends_with($normApp, $normalizedInputPhone) || str_ends_with($normalizedInputPhone, $normApp));
            $matchRep = !empty($normRep) && (str_ends_with($normRep, $normalizedInputPhone) || str_ends_with($normalizedInputPhone, $normRep));

            return $matchApp || $matchRep;
        });

        if (!$matched) {
            RateLimiter::hit($limiterKey, 60);
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp / HP tidak cocok dengan data pendaftaran untuk NIK ini.',
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
     */
    protected function resolveVerifiedNik(Request $request): ?string
    {
        // 1. Check direct session
        $sessionNik = $request->session()->get('applicant_verified_nik');
        if (!empty($sessionNik) && strlen($sessionNik) === 16) {
            return $sessionNik;
        }

        // 2. Check encrypted remember cookie
        $cookieValue = $request->cookie('applicant_device_auth');
        if ($cookieValue) {
            try {
                $payload = decrypt($cookieValue);
                if (is_array($payload) && !empty($payload['nik']) && isset($payload['expires_at'])) {
                    if (now()->timestamp <= $payload['expires_at']) {
                        // Restore into session
                        $request->session()->put('applicant_verified_nik', $payload['nik']);
                        $request->session()->put('applicant_verified_at', now()->timestamp);
                        return $payload['nik'];
                    }
                }
            } catch (\Throwable $e) {
                // Invalid or tampered cookie, ignore safely
            }
        }

        return null;
    }
}
