<?php

namespace App\Http\Controllers\Public\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Queue\QueueApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApplicantPortalController extends Controller
{
    /**
     * Show applicant portal dashboard.
     */
    public function index(Request $request): Response
    {
        /** @var ApplicantAccount $account */
        $account = Auth::guard('applicant')->user();
        $account->load('activeDevice');

        $rawNik = $account->nik;

        // Fetch applications belonging to this applicant account or matching their NIK
        $applications = QueueApplication::query()
            ->where(function ($q) use ($account, $rawNik) {
                $q->where('applicant_account_id', $account->id);
                if ($rawNik) {
                    $q->orWhere('applicant_nik', $rawNik);
                }
            })
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
                    'status'                  => $app->status,
                    'status_label'            => $app->status_label,
                    'status_color'            => $app->status_color,
                    'queue_number'            => $app->queue_number,
                    'schedule_date_formatted' => $schedDateFormatted,
                    'schedule_time'           => $schedTime,
                    'created_at_formatted'    => $app->created_at->translatedFormat('d M Y, H:i') . ' WITA',
                    'detail_url'              => route('queue.status', ['token' => $app->access_token]),
                ];
            });

        return Inertia::render('Public/Applicant/Dashboard', [
            'applicant' => [
                'id'                  => $account->id,
                'name'                => $account->name,
                'masked_nik'          => $account->masked_nik,
                'masked_email'        => $account->masked_email,
                'identity_status'     => $account->identity_status,
                'account_status'      => $account->account_status,
                'email_verified_at'   => $account->email_verified_at?->translatedFormat('d M Y, H:i'),
                'is_active'           => $account->isActive(),
                'is_verified'         => $account->isIdentityVerified(),
                'active_device'       => $account->activeDevice ? [
                    'device_label'    => $account->activeDevice->device_label,
                    'registered_at'   => $account->activeDevice->registered_at->translatedFormat('d M Y, H:i'),
                    'last_seen_at'    => $account->activeDevice->last_seen_at->translatedFormat('d M Y, H:i'),
                ] : null,
            ],
            'applications' => $applications,
        ]);
    }
}
