<?php

namespace App\Http\Controllers\Admin\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDeviceAuditLog;
use App\Services\Applicant\ApplicantDeviceService;
use App\Services\Applicant\ApplicantOtpService;
use App\Services\Queue\QueueDocumentStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantManagementController extends Controller
{
    public function __construct(
        protected ApplicantDeviceService $deviceService,
        protected ApplicantOtpService $otpService
    ) {}

    /**
     * Display a listing of applicant accounts with masked PII and active device indicators.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $accountStatus = $request->query('account_status');
        $identityStatus = $request->query('identity_status');

        $query = ApplicantAccount::query()->with('activeDevice');

        if ($search !== '') {
            $digits = preg_replace('/\D/', '', $search);
            $query->where(function ($q) use ($search, $digits) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email_normalized', 'like', "%" . strtolower($search) . "%");

                if ($digits !== '') {
                    $q->orWhere('phone', 'like', "%{$digits}%");
                }

                if (strlen($digits) === 16) {
                    $nikHash = ApplicantAccount::computeNikHash($digits);
                    $q->orWhere('nik_hash', $nikHash);
                }
            });
        }

        if ($accountStatus) {
            $query->where('account_status', $accountStatus);
        }

        if ($identityStatus) {
            $query->where('identity_status', $identityStatus);
        }

        $accounts = $query->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(function (ApplicantAccount $acc) {
                $hasSelfie = !empty($acc->selfie_path) && Storage::disk(QueueDocumentStorage::DISK)->exists($acc->selfie_path);
                $isDestroyed = !empty($acc->selfie_deleted_at) || (!$hasSelfie && in_array($acc->identity_status, [ApplicantAccount::IDENTITY_VERIFIED, ApplicantAccount::IDENTITY_REJECTED]));

                return [
                    'id'                    => $acc->id,
                    'name'                  => $acc->name,
                    'nik'                   => $acc->nik,
                    'masked_nik'            => $acc->masked_nik,
                    'email'                 => $acc->email_normalized,
                    'masked_email'          => $acc->masked_email,
                    'phone'                 => $acc->phone,
                    'formatted_phone'       => $acc->formatted_phone,
                    'masked_phone'          => $acc->masked_phone,
                    'has_selfie'            => $hasSelfie,
                    'selfie_url'            => $hasSelfie ? route('admin.queue.applicants.selfie', $acc->id) : null,
                    'selfie_deleted_at'     => $acc->selfie_deleted_at?->translatedFormat('d M Y, H:i'),
                    'is_selfie_destroyed'   => $isDestroyed,
                    'identity_status'       => $acc->identity_status,
                    'identity_status_label' => self::translateIdentityStatus($acc->identity_status),
                    'account_status'        => $acc->account_status,
                    'account_status_label'  => self::translateAccountStatus($acc->account_status),
                    'active_device_label'   => $acc->activeDevice?->device_label,
                    'active_device_seen'    => $acc->activeDevice?->last_seen_at?->translatedFormat('d M Y, H:i'),
                    'created_at'            => $acc->created_at?->translatedFormat('d M Y, H:i'),
                ];
            });

        $stats = [
            'total'            => ApplicantAccount::count(),
            'active'           => ApplicantAccount::where('account_status', ApplicantAccount::STATUS_ACTIVE)->count(),
            'identity_pending' => ApplicantAccount::where('identity_status', ApplicantAccount::IDENTITY_PENDING)->count(),
            'suspended'        => ApplicantAccount::where('account_status', ApplicantAccount::STATUS_SUSPENDED)->count(),
        ];

        return Inertia::render('Admin/Applicants/Index', [
            'accounts' => $accounts,
            'filters'  => [
                'search'          => $search,
                'account_status'  => $accountStatus,
                'identity_status' => $identityStatus,
            ],
            'stats'    => $stats,
        ]);
    }

    /**
     * Show applicant account details, device history, and security audit log.
     */
    public function show(ApplicantAccount $account): Response
    {
        $account->load([
            'devices' => fn($q) => $q->latest('registered_at'),
            'auditLogs' => fn($q) => $q->latest('created_at')->take(30),
            'applications' => fn($q) => $q->latest('created_at')->with('serviceType'),
            'verifiedByAdmin',
        ]);

        $hasSelfie = !empty($account->selfie_path) && Storage::disk(QueueDocumentStorage::DISK)->exists($account->selfie_path);
        $isDestroyed = !empty($account->selfie_deleted_at) || (!$hasSelfie && in_array($account->identity_status, [ApplicantAccount::IDENTITY_VERIFIED, ApplicantAccount::IDENTITY_REJECTED]));

        return Inertia::render('Admin/Applicants/Show', [
            'account' => [
                'id'                   => $account->id,
                'name'                 => $account->name,
                'nik'                  => $account->nik,
                'masked_nik'           => $account->masked_nik,
                'email'                => $account->email_normalized,
                'masked_email'         => $account->masked_email,
                'phone'                => $account->phone,
                'formatted_phone'      => $account->formatted_phone,
                'has_selfie'           => $hasSelfie,
                'selfie_url'           => $hasSelfie ? route('admin.queue.applicants.selfie', $account->id) : null,
                'selfie_deleted_at'    => $account->selfie_deleted_at?->translatedFormat('d M Y, H:i'),
                'is_selfie_destroyed'  => $isDestroyed,
                'identity_status'       => $account->identity_status,
                'identity_status_label' => self::translateIdentityStatus($account->identity_status),
                'account_status'        => $account->account_status,
                'account_status_label'  => self::translateAccountStatus($account->account_status),
                'status_reason'        => $account->status_reason,
                'email_verified_at'    => $account->email_verified_at?->translatedFormat('d M Y, H:i'),
                'identity_verified_at' => $account->identity_verified_at?->translatedFormat('d M Y, H:i'),
                'identity_verified_by' => $account->verifiedByAdmin?->name,
                'created_at'           => $account->created_at?->translatedFormat('d M Y, H:i'),
            ],
            'devices' => $account->devices->map(fn($d) => [
                'id'                => $d->id,
                'device_label'      => $d->device_label,
                'ip_address'        => $d->ip_address,
                'is_active'         => $d->isActive(),
                'registered_at'     => $d->registered_at->translatedFormat('d M Y, H:i'),
                'last_seen_at'      => $d->last_seen_at->translatedFormat('d M Y, H:i'),
                'revoked_at'        => $d->revoked_at?->translatedFormat('d M Y, H:i'),
                'revocation_reason' => $d->revocation_reason,
                'revoked_by_type'   => $d->revoked_by_type,
            ]),
            'auditLogs' => $account->auditLogs->map(fn($l) => [
                'id'          => $l->id,
                'event_type'  => $l->event_type,
                'event_label' => self::translateEventType($l->event_type),
                'actor_type'  => $l->actor_type,
                'actor_label' => self::translateActorType($l->actor_type),
                'reason'      => $l->reason,
                'ip_address'  => $l->ip_address,
                'created_at'  => $l->created_at->translatedFormat('d M Y, H:i'),
            ]),
            'applications' => $account->applications->map(fn($a) => [
                'id'           => $a->id,
                'reg_number'   => $a->reg_number,
                'service_name' => $a->serviceType?->name ?? 'Layanan KTP-el',
                'status'       => $a->status,
                'status_label' => $a->status_label,
                'created_at'   => $a->created_at->translatedFormat('d M Y, H:i'),
            ]),
        ]);
    }

    /**
     * Revoke active device for an applicant.
     */
    public function revokeDevice(Request $request, ApplicantAccount $account): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:255',
        ], [
            'reason.required' => 'Alasan pencabutan perangkat wajib dicantumkan.',
        ]);

        $reason = trim((string) $request->input('reason'));

        $this->deviceService->revokeActiveDevice(
            account: $account,
            reason: $reason,
            actorType: 'admin',
            actorId: Auth::id()
        );

        return back()->with('success', 'Perangkat aktif pemohon berhasil dicabut.');
    }

    /**
     * Update account status (Activate / Suspend / Lock).
     */
    public function updateStatus(Request $request, ApplicantAccount $account): RedirectResponse
    {
        $request->validate([
            'account_status' => 'required|in:ACTIVE,IDENTITY_PENDING,SUSPENDED,LOCKED',
            'reason'         => 'required|string|min:3|max:255',
        ]);

        $status = $request->input('account_status');
        $reason = trim((string) $request->input('reason'));

        $account->update([
            'account_status' => $status,
            'status_reason'  => $reason,
        ]);

        // If suspending or locking account, revoke active device immediately
        if (in_array($status, [ApplicantAccount::STATUS_SUSPENDED, ApplicantAccount::STATUS_LOCKED])) {
            $this->deviceService->revokeActiveDevice(
                account: $account,
                reason: "Pencabutan otomatis karena status akun: {$status} ({$reason})",
                actorType: 'admin',
                actorId: Auth::id()
            );
        }

        ApplicantDeviceAuditLog::logEvent(
            applicantAccountId: $account->id,
            eventType: 'account_status_changed',
            actorType: 'admin',
            actorId: Auth::id(),
            reason: "Status akun diubah menjadi {$status}: {$reason}"
        );

        return back()->with('success', "Status akun berhasil diperbarui menjadi {$status}.");
    }

    /**
     * Stream applicant selfie photo securely from private storage disk.
     */
    public function selfie(ApplicantAccount $account): StreamedResponse
    {
        if (empty($account->selfie_path) || !Storage::disk(QueueDocumentStorage::DISK)->exists($account->selfie_path)) {
            abort(404, 'Foto selfie pemohon tidak ditemukan atau telah dimusnahkan pasca verifikasi demi pelindungan privasi.');
        }

        $path = $account->selfie_path;
        $filename = 'selfie_' . ($account->nik ?: $account->id) . '.jpg';

        return Storage::disk(QueueDocumentStorage::DISK)->response($path, $filename, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-cache, private, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Verify or reject applicant demographic identity.
     */
    public function verifyIdentity(Request $request, ApplicantAccount $account): RedirectResponse
    {
        $request->validate([
            'decision' => 'required|in:VERIFIED,REJECTED',
            'notes'    => 'nullable|string|max:500',
        ]);

        $decision = $request->input('decision');
        $notes = trim((string) $request->input('notes', ''));

        $updateData = [
            'identity_status'      => $decision,
            'identity_verified_at' => now(),
            'identity_verified_by' => Auth::id(),
        ];

        if ($decision === ApplicantAccount::IDENTITY_VERIFIED) {
            $updateData['account_status'] = ApplicantAccount::STATUS_ACTIVE;
        }

        // Automatic permanent physical destruction of selfie file upon verification completion
        $selfieDestroyed = false;
        if (!empty($account->selfie_path)) {
            try {
                if (Storage::disk(QueueDocumentStorage::DISK)->exists($account->selfie_path)) {
                    Storage::disk(QueueDocumentStorage::DISK)->delete($account->selfie_path);
                    $selfieDestroyed = true;
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal memusnahkan fisik foto selfie akun {$account->id}: " . $e->getMessage());
            }

            $updateData['selfie_path'] = null;
            $updateData['selfie_deleted_at'] = now();
        }

        $account->update($updateData);

        $auditReason = "Verifikasi identitas pemohon: {$decision}. Catatan: {$notes}";
        if ($selfieDestroyed) {
            $auditReason .= " [Foto selfie biometrik pendaftaran otomatis dimusnahkan]";
        }

        ApplicantDeviceAuditLog::logEvent(
            applicantAccountId: $account->id,
            eventType: 'identity_verification_' . strtolower($decision),
            actorType: 'admin',
            actorId: Auth::id(),
            reason: mb_substr($auditReason, 0, 255)
        );

        // Dispatch notification email to applicant's registered email
        $this->otpService->sendVerificationResultEmail($account, $decision, $notes);

        $successMsg = "Identitas pemohon berhasil diverifikasi ({$decision})";
        if ($selfieDestroyed) {
            $successMsg .= " dan berkas foto selfie telah otomatis dimusnahkan secara aman dari server storage.";
        } else {
            $successMsg .= " dan pemberitahuan telah dikirimkan ke email pemohon.";
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Delete an applicant account permanently.
     */
    public function destroy(Request $request, ApplicantAccount $account): RedirectResponse
    {
        $applicantName = $account->name;
        $maskedNik = $account->masked_nik;

        // Revoke active device if any
        $this->deviceService->revokeActiveDevice(
            account: $account,
            reason: 'Akun pemohon dihapus oleh administrator',
            actorType: 'admin',
            actorId: Auth::id()
        );

        // Delete physical selfie photo if still present
        if (!empty($account->selfie_path)) {
            try {
                if (Storage::disk(QueueDocumentStorage::DISK)->exists($account->selfie_path)) {
                    Storage::disk(QueueDocumentStorage::DISK)->delete($account->selfie_path);
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal menghapus berkas selfie saat akun {$account->id} dihapus: " . $e->getMessage());
            }
        }

        // Permanently force delete to release NIK & email unique indexes
        $account->forceDelete();

        return redirect()->route('admin.queue.applicants.index')
            ->with('success', "Akun pemohon {$applicantName} ({$maskedNik}) berhasil dihapus dari sistem.");
    }

    public static function translateEventType(string $event): string
    {
        return match ($event) {
            'registered'                     => 'Pendaftaran Akun',
            'email_verified'                 => 'Email Berhasil Diverifikasi',
            'identity_verification_verified' => 'Identitas Diverifikasi (Sah)',
            'identity_verification_rejected' => 'Identitas Ditolak',
            'account_status_changed'         => 'Status Akun Diubah',
            'device_bound'                   => 'Perangkat Baru Terikat',
            'device_revoked'                 => 'Akses Perangkat Dicabut',
            'device_recovered'               => 'Perangkat Berhasil Dipulihkan',
            'login_success_existing_device'  => 'Login Berhasil (Perangkat Terdaftar)',
            'login_rejected_device_conflict' => 'Login Ditolak (Konflik Perangkat Lain)',
            'login_failed_bad_password'      => 'Gagal Login (Password Salah)',
            'otp_generated'                  => 'Kode OTP Dikirim',
            'otp_verified'                   => 'OTP Berhasil Diverifikasi',
            default                          => ucwords(str_replace('_', ' ', $event)),
        };
    }

    public static function translateActorType(?string $actor): string
    {
        return match ($actor) {
            'applicant' => 'Pemohon',
            'admin'     => 'Petugas / Admin',
            'system'    => 'Sistem',
            default     => $actor ?? 'Sistem',
        };
    }

    public static function translateAccountStatus(string $status): string
    {
        return match ($status) {
            ApplicantAccount::STATUS_ACTIVE           => 'Aktif',
            ApplicantAccount::STATUS_IDENTITY_PENDING => 'Menunggu Verifikasi Identitas',
            ApplicantAccount::STATUS_EMAIL_PENDING    => 'Menunggu Verifikasi Email',
            ApplicantAccount::STATUS_SUSPENDED        => 'Ditangguhkan',
            ApplicantAccount::STATUS_LOCKED           => 'Terkunci',
            default                                   => $status,
        };
    }

    public static function translateIdentityStatus(string $status): string
    {
        return match ($status) {
            ApplicantAccount::IDENTITY_VERIFIED => 'Terverifikasi (Sah)',
            ApplicantAccount::IDENTITY_PENDING  => 'Menunggu Verifikasi',
            ApplicantAccount::IDENTITY_REJECTED => 'Ditolak',
            default                             => $status,
        };
    }
}
