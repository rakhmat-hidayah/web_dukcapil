<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\KtpHandover;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceType;
use App\Services\Queue\QueueSlotService;
use App\Services\Queue\QueueVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationVerificationController extends Controller
{
    public function __construct(
        protected QueueVerificationService $verificationService,
        protected QueueSlotService $slotService
    ) {}

    /**
     * Display a filtered, searchable list of applications.
     */
    public function index(Request $request): Response
    {
        // Automatically purge any abandoned drafts older than 24 hours so CMS table stays clean
        try {
            app(\App\Services\Queue\QueueApplicationService::class)->purgeExpiredDrafts(24);
        } catch (\Throwable $e) {
            \Log::warning('Auto purge expired drafts warning: ' . $e->getMessage());
        }

        $status = $request->query('status');
        $urgency = $request->query('urgency');
        $serviceId = $request->query('service_id');
        $search = $request->query('search');
        if ($search && preg_match('/(REG-[A-Za-z0-9-]+)/i', $search, $matches)) {
            $search = strtoupper($matches[1]);
        }

        $unverifiedStatuses = [
            QueueApplication::STATUS_SUBMITTED,
            QueueApplication::STATUS_RESUBMITTED,
            QueueApplication::STATUS_UNDER_REVIEW,
        ];
        $cutoff24h = now()->subHours(24);

        $query = QueueApplication::with(['serviceType', 'slot.schedule', 'documents.serviceDocument']);

        if ($urgency === 'overdue') {
            $query->whereIn('status', $unverifiedStatuses)
                ->where(function ($q) use ($cutoff24h) {
                    $q->where('submitted_at', '<=', $cutoff24h)
                      ->orWhere(function ($sub) use ($cutoff24h) {
                          $sub->whereNull('submitted_at')->where('created_at', '<=', $cutoff24h);
                      });
                })
                ->orderBy('submitted_at', 'asc');
        } elseif ($urgency === 'new') {
            $query->whereIn('status', $unverifiedStatuses)
                ->where(function ($q) use ($cutoff24h) {
                    $q->where('submitted_at', '>', $cutoff24h)
                      ->orWhere(function ($sub) use ($cutoff24h) {
                          $sub->whereNull('submitted_at')->where('created_at', '>', $cutoff24h);
                      });
                })
                ->latest('submitted_at');
        } elseif ($urgency === 'unverified') {
            $query->whereIn('status', $unverifiedStatuses)
                ->latest('submitted_at');
        } else {
            $query->latest('submitted_at');
        }

        if ($status && empty($urgency)) {
            if ($status === 'SUBMITTED') {
                $query->whereIn('status', [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED]);
            } elseif (in_array($status, ['PRE_VERIFIED', 'APPROVED'])) {
                $query->whereIn('status', [QueueApplication::STATUS_PRE_VERIFIED, 'APPROVED']);
            } elseif (in_array($status, ['QUEUE_BOOKED', 'SCHEDULED'])) {
                $query->whereIn('status', [QueueApplication::STATUS_QUEUE_BOOKED, 'SCHEDULED']);
            } elseif (in_array($status, ['ARRIVED', 'CHECKED_IN'])) {
                $query->whereIn('status', [QueueApplication::STATUS_ARRIVED, 'CHECKED_IN']);
            } elseif (in_array($status, ['PHYSICAL_VERIFICATION', 'APPROVED_FOR_SERVICE'])) {
                $query->whereIn('status', [QueueApplication::STATUS_PHYSICAL_VERIFICATION, QueueApplication::STATUS_APPROVED_FOR_SERVICE]);
            } elseif (in_array($status, ['PROCESSING', 'IN_SERVICE'])) {
                $query->whereIn('status', [QueueApplication::STATUS_PROCESSING, 'IN_SERVICE']);
            } elseif (in_array($status, ['EXPIRED', 'CANCELLED'])) {
                $query->whereIn('status', [QueueApplication::STATUS_EXPIRED, QueueApplication::STATUS_CANCELLED]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($serviceId) {
            $query->where('service_type_id', $serviceId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reg_number', 'like', "%{$search}%")
                  ->orWhere('queue_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_nik', 'like', "%{$search}%")
                  ->orWhere('representative_name', 'like', "%{$search}%")
                  ->orWhere('representative_nik', 'like', "%{$search}%")
                  ->orWhere('applicant_phone', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(15)->withQueryString();

        // Real aggregated counts for badge tabs
        $statusCounts = [
            'all'                   => QueueApplication::count(),
            'SUBMITTED'             => QueueApplication::whereIn('status', [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED])->count(),
            'UNDER_REVIEW'          => QueueApplication::where('status', QueueApplication::STATUS_UNDER_REVIEW)->count(),
            'REVISION_REQUIRED'     => QueueApplication::where('status', QueueApplication::STATUS_REVISION_REQUIRED)->count(),
            'PRE_VERIFIED'          => QueueApplication::whereIn('status', [QueueApplication::STATUS_PRE_VERIFIED, 'APPROVED'])->count(),
            'QUEUE_BOOKED'          => QueueApplication::whereIn('status', [QueueApplication::STATUS_QUEUE_BOOKED, 'SCHEDULED'])->count(),
            'ARRIVED'               => QueueApplication::whereIn('status', [QueueApplication::STATUS_ARRIVED, 'CHECKED_IN'])->count(),
            'PHYSICAL_VERIFICATION' => QueueApplication::whereIn('status', [QueueApplication::STATUS_PHYSICAL_VERIFICATION, QueueApplication::STATUS_APPROVED_FOR_SERVICE])->count(),
            'PROCESSING'            => QueueApplication::whereIn('status', [QueueApplication::STATUS_PROCESSING, 'IN_SERVICE'])->count(),
            'READY_FOR_PICKUP'      => QueueApplication::where('status', QueueApplication::STATUS_READY_FOR_PICKUP)->count(),
            'COMPLETED'             => QueueApplication::where('status', QueueApplication::STATUS_COMPLETED)->count(),
            'REJECTED'              => QueueApplication::where('status', QueueApplication::STATUS_REJECTED)->count(),
            'EXPIRED'               => QueueApplication::whereIn('status', [QueueApplication::STATUS_EXPIRED, QueueApplication::STATUS_CANCELLED])->count(),
        ];

        // Specific urgent counts for operator verification reminders
        $overdueCount = QueueApplication::whereIn('status', $unverifiedStatuses)
            ->where(function ($q) use ($cutoff24h) {
                $q->where('submitted_at', '<=', $cutoff24h)
                  ->orWhere(function ($sub) use ($cutoff24h) {
                      $sub->whereNull('submitted_at')->where('created_at', '<=', $cutoff24h);
                  });
            })->count();

        $newCount = QueueApplication::whereIn('status', $unverifiedStatuses)
            ->where(function ($q) use ($cutoff24h) {
                $q->where('submitted_at', '>', $cutoff24h)
                  ->orWhere(function ($sub) use ($cutoff24h) {
                      $sub->whereNull('submitted_at')->where('created_at', '>', $cutoff24h);
                  });
            })->count();

        $urgencyCounts = [
            'overdue'          => $overdueCount,
            'new'              => $newCount,
            'total_unverified' => $overdueCount + $newCount,
        ];

        $services = ServiceType::active()->get(['id', 'name', 'color']);

        return Inertia::render('Admin/Queue/Applications/Index', [
            'applications'  => $applications,
            'statusCounts'  => $statusCounts,
            'urgencyCounts' => $urgencyCounts,
            'services'      => $services,
            'can_delete'    => Auth::user()?->isSuperAdmin() ?? false,
            'filters'       => [
                'status'     => $status,
                'urgency'    => $urgency,
                'service_id' => $serviceId,
                'search'     => $search,
            ],
        ]);
    }

    /**
     * Show detailed verification screen for an application.
     */
    public function show(int $id): Response
    {
        $application = QueueApplication::with([
            'serviceType.documents' => fn($q) => $q->orderBy('sort_order'),
            'documents.serviceDocument',
            'slot.schedule',
            'verificationHistory.user',
            'checkedInUser',
            'physicalVerifiedUser',
            'handoverUser',
            'handover',
        ])->findOrFail($id);

        $officer = Auth::user();

        // Automatically start review if status is SUBMITTED or RESUBMITTED
        if (in_array($application->status, [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED])) {
            $this->verificationService->startReview($application, $officer);
            $application->load([
                'serviceType.documents' => fn($q) => $q->orderBy('sort_order'),
                'documents.serviceDocument',
                'verificationHistory.user',
            ]);
        }

        // Ensure relations are fully loaded and not lost
        $application->loadMissing([
            'serviceType.documents' => fn($q) => $q->orderBy('sort_order'),
            'documents.serviceDocument',
        ]);

        // Auto-sync any pending documents to APPROVED if application is already pre-verified or further
        if (in_array($application->status, [
            QueueApplication::STATUS_PRE_VERIFIED,
            QueueApplication::STATUS_QUEUE_BOOKED,
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PROCESSING,
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_COMPLETED,
        ])) {
            $hasPending = $application->documents->where('status', ApplicationDocument::STATUS_PENDING)->isNotEmpty();
            if ($hasPending) {
                $application->documents()->where('status', ApplicationDocument::STATUS_PENDING)->update([
                    'status'      => ApplicationDocument::STATUS_APPROVED,
                    'reviewed_by' => $officer?->id,
                    'reviewed_at' => now(),
                ]);
                $application->load('documents.serviceDocument');
            }
        }

        // Ensure KtpHandover record exists and matches current application stage
        if (in_array($application->status, [
            QueueApplication::STATUS_PRE_VERIFIED,
            QueueApplication::STATUS_QUEUE_BOOKED,
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PROCESSING,
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_COMPLETED,
        ])) {
            if (!$application->handover) {
                $initialHandoverStatus = match ($application->status) {
                    QueueApplication::STATUS_READY_FOR_PICKUP => \App\Models\Queue\KtpHandover::STATUS_READY,
                    QueueApplication::STATUS_COMPLETED        => \App\Models\Queue\KtpHandover::STATUS_HANDED_OVER,
                    default                                   => \App\Models\Queue\KtpHandover::STATUS_PENDING,
                };

                \App\Models\Queue\KtpHandover::firstOrCreate(
                    ['application_id' => $application->id],
                    [
                        'status'          => $initialHandoverStatus,
                        'ready_marked_at' => ($initialHandoverStatus === \App\Models\Queue\KtpHandover::STATUS_READY) ? ($application->updated_at ?: now()) : null,
                    ]
                );
                $application->load('handover');
            }
        }

        // Available upcoming slots for approval assignment
        $availableSlots = $this->slotService->getAvailableSlots(
            $application->service_type_id,
            now(),
            14
        );

        return Inertia::render('Admin/Queue/Applications/Show', [
            'application'    => $application,
            'availableSlots' => $availableSlots,
            'can_delete'     => Auth::user()?->isSuperAdmin() ?? false,
        ]);
    }

    /**
     * Delete an application permanently (Super Administrator only).
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Super Administrator.');
        }

        $application = QueueApplication::withTrashed()->findOrFail($id);
        $regNumber = $application->reg_number;
        $name = $application->applicant_name;

        app(\App\Services\Queue\QueueApplicationService::class)->deleteApplicationByAdmin($application, $user);

        if (str_contains(url()->previous(), "/admin/queue/applications/{$id}")) {
            return redirect()->route('admin.queue.applications.index')
                ->with('success', "Permohonan {$regNumber} ({$name}) berhasil dihapus permanen oleh Super Admin.");
        }

        return redirect()->back()->with('success', "Permohonan {$regNumber} ({$name}) berhasil dihapus permanen oleh Super Admin.");
    }

    /**
     * Pre-verify online documents. Marks application as PRE_VERIFIED.
     */
    public function preVerify(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);
        $notes = $request->input('notes');

        $this->verificationService->preVerify($application, Auth::user(), $notes);

        return back()->with('success', "Pra-verifikasi berhasil disetujui! Pemohon kini dapat memilih jadwal antrean kedatangan di portal.");
    }

    /**
     * Direct approve with slot assignment (legacy/admin quick assign).
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        $request->validate([
            'queue_slot_id' => 'required|exists:queue_slots,id',
            'notes'         => 'nullable|string|max:500',
        ], [
            'queue_slot_id.required' => 'Pilih tanggal jadwal pelayanan untuk menetapkan nomor antrean.',
        ]);

        $this->verificationService->approve(
            $application,
            (int) $request->input('queue_slot_id'),
            Auth::user(),
            $request->input('notes')
        );

        return back()->with('success', "Pengajuan berhasil disetujui! Nomor antrean: {$application->fresh()->queue_number}");
    }

    /**
     * Request document revision from applicant.
     */
    public function requestRevision(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        $request->validate([
            'notes'             => 'required|string|min:5|max:1000',
            'document_feedback' => 'nullable|array',
        ], [
            'notes.required' => 'Tuliskan catatan alasan perbaikan dokumen secara jelas untuk pemohon.',
        ]);

        $this->verificationService->requestRevision(
            $application,
            $request->input('notes'),
            $request->input('document_feedback', []),
            Auth::user()
        );

        return back()->with('success', 'Permintaan perbaikan dokumen berhasil dikirim ke pemohon.');
    }

    /**
     * Reject application permanently.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        if (in_array($application->status, [
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_COMPLETED,
            QueueApplication::STATUS_CANCELLED,
            QueueApplication::STATUS_EXPIRED,
        ]) || in_array($application->handover?->status, [
            KtpHandover::STATUS_READY,
            KtpHandover::STATUS_HANDED_OVER,
        ])) {
            return back()->with('error', 'Pengajuan tidak dapat ditolak karena fisik KTP-el telah selesai dicetak dan siap di loket penerimaan / telah diserahkan.');
        }

        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ], [
            'reason.required' => 'Tuliskan alasan penolakan secara jelas.',
        ]);

        $this->verificationService->reject(
            $application,
            $request->input('reason'),
            Auth::user()
        );

        return back()->with('success', 'Pengajuan telah ditolak.');
    }

    /**
     * Mark applicant as checked in at counter.
     */
    public function checkIn(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);
        $this->verificationService->checkIn($application, $request->all(), Auth::user());

        return back()->with('success', "Pemohon {$application->applicant_name} ({$application->queue_number}) telah check-in.");
    }

    /**
     * Process physical verification.
     */
    public function physicalVerification(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);
        $this->verificationService->physicalVerification($application, $request->all(), Auth::user());

        return back()->with('success', "Verifikasi fisik selesai dicatat.");
    }

    /**
     * Mark application as in service / processing.
     */
    public function startProcessing(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        if ($application->status === QueueApplication::STATUS_REJECTED) {
            return back()->with('error', 'Permohonan telah ditolak. Tindakan loket dan cetak kartu dinonaktifkan.');
        }

        if (in_array($application->status, [
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_COMPLETED,
            QueueApplication::STATUS_CANCELLED,
            QueueApplication::STATUS_EXPIRED,
        ]) || in_array($application->handover?->status, [
            KtpHandover::STATUS_READY,
            KtpHandover::STATUS_HANDED_OVER,
        ])) {
            return back()->with('error', 'KTP-el fisik telah selesai dicetak dan siap di loket penerimaan sehingga tidak dapat dikembalikan ke status proses cetak.');
        }

        if (empty($application->queue_number)) {
            return back()->with('error', 'Tindakan loket dan cetak kartu hanya dapat dilakukan setelah jadwal kedatangan ditetapkan dan nomor antrean terbit.');
        }

        if (!in_array($application->status, [
            QueueApplication::STATUS_QUEUE_BOOKED,
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PROCESSING,
        ])) {
            return back()->with('error', 'Tindakan loket dan status cetak hanya aktif setelah permohonan memiliki jadwal kedatangan dan nomor antrean.');
        }

        $this->verificationService->startProcessing($application, Auth::user(), $request->input('notes'));

        return back()->with('success', "Status pelayanan diubah: Sedang Diproses.");
    }

    /**
     * Mark ready for pickup.
     */
    public function markReadyForPickup(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        if ($application->status === QueueApplication::STATUS_REJECTED) {
            return back()->with('error', 'Permohonan telah ditolak. Tindakan loket dan cetak kartu dinonaktifkan.');
        }

        if (empty($application->queue_number)) {
            return back()->with('error', 'Tindakan loket dan cetak kartu hanya dapat dilakukan setelah jadwal kedatangan ditetapkan dan nomor antrean terbit.');
        }

        if (!in_array($application->status, [
            QueueApplication::STATUS_QUEUE_BOOKED,
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PROCESSING,
            QueueApplication::STATUS_READY_FOR_PICKUP,
        ])) {
            return back()->with('error', 'Tindakan loket dan status cetak hanya aktif setelah permohonan memiliki jadwal kedatangan dan nomor antrean.');
        }

        $this->verificationService->markReadyForPickup($application, Auth::user(), $request->input('notes'));

        return back()->with('success', "KTP-el fisik telah selesai dicetak dan siap diambil.");
    }

    /**
     * Mark service as completed via handover.
     */
    public function complete(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);
        $this->verificationService->completeHandover($application, $request->all(), Auth::user());

        return back()->with('success', "Pelayanan untuk {$application->applicant_name} ({$application->queue_number}) telah selesai.");
    }
}
