<?php

namespace App\Http\Controllers\Admin\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDeviceAuditLog;
use App\Services\Applicant\ApplicantDeviceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantManagementController extends Controller
{
    public function __construct(
        protected ApplicantDeviceService $deviceService
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
            ->through(fn(ApplicantAccount $acc) => [
                'id'                  => $acc->id,
                'name'                => $acc->name,
                'masked_nik'          => $acc->masked_nik,
                'masked_email'        => $acc->masked_email,
                'identity_status'     => $acc->identity_status,
                'account_status'      => $acc->account_status,
                'active_device_label' => $acc->activeDevice?->device_label,
                'active_device_seen'  => $acc->activeDevice?->last_seen_at?->translatedFormat('d M Y, H:i'),
                'created_at'          => $acc->created_at?->translatedFormat('d M Y, H:i'),
            ]);

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
        ]);

        return Inertia::render('Admin/Applicants/Show', [
            'account' => [
                'id'                   => $account->id,
                'name'                 => $account->name,
                'masked_nik'           => $account->masked_nik,
                'masked_email'         => $account->masked_email,
                'identity_status'      => $account->identity_status,
                'account_status'       => $account->account_status,
                'status_reason'        => $account->status_reason,
                'email_verified_at'    => $account->email_verified_at?->translatedFormat('d M Y, H:i'),
                'identity_verified_at' => $account->identity_verified_at?->translatedFormat('d M Y, H:i'),
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
                'id'         => $l->id,
                'event_type' => $l->event_type,
                'actor_type' => $l->actor_type,
                'reason'     => $l->reason,
                'ip_address' => $l->ip_address,
                'created_at' => $l->created_at->translatedFormat('d M Y, H:i'),
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
     * Verify or reject applicant demographic identity.
     */
    public function verifyIdentity(Request $request, ApplicantAccount $account): RedirectResponse
    {
        $request->validate([
            'decision' => 'required|in:VERIFIED,REJECTED',
            'notes'    => 'nullable|string|max:255',
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

        $account->update($updateData);

        ApplicantDeviceAuditLog::logEvent(
            applicantAccountId: $account->id,
            eventType: 'identity_verification_' . strtolower($decision),
            actorType: 'admin',
            actorId: Auth::id(),
            reason: "Verifikasi identitas pemohon: {$decision}. Catatan: {$notes}"
        );

        return back()->with('success', "Identitas pemohon berhasil diverifikasi ({$decision}).");
    }
}
