<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSetting;
use App\Services\ConfigurationService;
use App\Services\Queue\QueueApplicationService;
use App\Services\Queue\QueueDocumentStorage;
use App\Services\Queue\QueueVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantDashboardController extends Controller
{
    public function __construct(
        protected QueueApplicationService $applicationService,
        protected QueueVerificationService $verificationService
    ) {}

    /**
     * Show applicant live tracking dashboard.
     */
    public function show(Request $request, string $token): Response|RedirectResponse
    {
        $application = QueueApplication::where('access_token', $token)
            ->with([
                'serviceType.documents' => fn($q) => $q->orderBy('sort_order'),
                'documents.serviceDocument',
                'slot.schedule',
                'verificationHistory.user',
            ])
            ->firstOrFail();

        // Otomatis arahkan pemohon langsung ke pemilihan jadwal jika berkas sudah lolos pra-verifikasi dan belum memiliki nomor antrean
        if (
            in_array($application->status, [QueueApplication::STATUS_PRE_VERIFIED, QueueApplication::STATUS_APPROVED])
            && !$application->queue_number
            && !$request->has('view_dashboard')
            && !$request->has('stay')
        ) {
            return redirect()->route('queue.booking', ['token' => $token])
                ->with('info', 'Dokumen online Anda telah lolos pra-verifikasi. Silakan pilih tanggal dan sesi jadwal kedatangan Anda.');
        }

        // Establish verified session for this applicant's NIK so they can access their history seamlessly
        session([
            'applicant_verified_nik' => $application->applicant_nik,
            'applicant_verified_at'  => now()->timestamp,
        ]);

        // Sinkronkan status dokumen jika permohonan sudah lolos pra-verifikasi atau dalam tahap pelayanan
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
                    'reviewed_at' => now(),
                ]);
                $application->load('documents.serviceDocument');
            }
        }

        $officeAddress = ConfigurationService::get('office_address')
            ?: QueueSetting::get('queue.office_address', 'Jl. Soekarno Hatta No. 9, Dompu - NTB 84211');
        $helpdeskPhone = ConfigurationService::get('whatsapp_number')
            ?: QueueSetting::get('queue.helpdesk_phone', '6282124824114');

        return Inertia::render('Public/Queue/Status/Dashboard', [
            'application' => $application,
            'officeAddress' => $officeAddress,
            'helpdeskPhone' => $helpdeskPhone,
        ]);
    }

    /**
     * Show booking calendar interface when application is PRE_VERIFIED or rescheduling.
     */
    public function showBooking(Request $request, string $token): Response|RedirectResponse
    {
        $application = QueueApplication::where('access_token', $token)
            ->with(['serviceType', 'slot.schedule'])
            ->firstOrFail();

        // 1. Dokumen yang telah selesai atau telah diproses di loket TIDAK BOLEH dijadwalkan ulang
        $completedOrLockedStatuses = [
            QueueApplication::STATUS_COMPLETED,
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_PROCESSING,
            QueueApplication::STATUS_IN_SERVICE,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PHYSICAL_VERIFIED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION_REJECTED,
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_CHECKED_IN,
            QueueApplication::STATUS_REJECTED,
            QueueApplication::STATUS_CANCELLED,
            QueueApplication::STATUS_DRAFT,
        ];

        if (in_array($application->status, $completedOrLockedStatuses)) {
            $isCompleted = in_array($application->status, [
                QueueApplication::STATUS_COMPLETED,
                QueueApplication::STATUS_READY_FOR_PICKUP,
                QueueApplication::STATUS_PROCESSING,
                QueueApplication::STATUS_IN_SERVICE,
            ]);

            $message = $isCompleted
                ? 'Permohonan dokumen Anda telah selesai atau sedang dalam proses loket. Jadwal kedatangan tidak dapat diubah.'
                : 'Pemesanan atau perubahan jadwal antrean tidak dapat dilakukan untuk status permohonan saat ini.';

            return redirect()->route('queue.status', ['token' => $token])
                ->with('info', $message);
        }

        // 2. Hanya permohonan aktif pra-kehadiran fisik yang diizinkan untuk memilih / mengubah jadwal
        $allowedStatuses = [
            QueueApplication::STATUS_SUBMITTED,
            QueueApplication::STATUS_UNDER_REVIEW,
            QueueApplication::STATUS_RESUBMITTED,
            QueueApplication::STATUS_PRE_VERIFIED,
            QueueApplication::STATUS_APPROVED,
            QueueApplication::STATUS_QUEUE_BOOKED,
            QueueApplication::STATUS_SCHEDULED,
        ];

        if (!in_array($application->status, $allowedStatuses)) {
            return redirect()->route('queue.status', ['token' => $token])
                ->with('info', 'Pemesanan jadwal antrean hanya dapat dilakukan untuk permohonan yang aktif.');
        }

        $isReschedule = (bool) $request->has('reschedule') || (bool) $request->boolean('reschedule');

        // If queue number is already issued and not explicitly rescheduling, redirect directly to status
        if ($application->queue_number && !$isReschedule) {
            return redirect()->route('queue.status', ['token' => $token])
                ->with('info', "Nomor antrean resmi Anda telah diterbitkan: {$application->queue_number}.");
        }

        $availableSchedules = QueueSchedule::where('is_open', true)
            ->where('is_holiday', false)
            ->where('schedule_date', '>=', now()->toDateString())
            ->whereHas('slots', function ($q) use ($application) {
                $q->where('service_type_id', $application->service_type_id)
                  ->whereRaw('booked_count < quota');
            })
            ->with(['slots' => function ($q) use ($application) {
                $q->where('service_type_id', $application->service_type_id);
            }])
            ->orderBy('schedule_date')
            ->orderBy('open_time')
            ->get();

        return Inertia::render('Public/Queue/Status/Booking', [
            'application' => $application,
            'schedules'   => $availableSchedules,
            'isReschedule' => $isReschedule,
        ]);
    }

    /**
     * Store chosen queue slot booking or rescheduling.
     */
    public function storeBooking(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'slot_id' => 'required|exists:queue_slots,id',
        ], [
            'slot_id.required' => 'Pilih salah satu jadwal antrean yang tersedia.',
            'slot_id.exists'   => 'Jadwal yang dipilih tidak valid atau sudah penuh.',
        ]);

        $application = QueueApplication::where('access_token', $token)
            ->with(['serviceType', 'slot.schedule'])
            ->firstOrFail();

        $completedOrLockedStatuses = [
            QueueApplication::STATUS_COMPLETED,
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_PROCESSING,
            QueueApplication::STATUS_IN_SERVICE,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PHYSICAL_VERIFIED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION_REJECTED,
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_CHECKED_IN,
            QueueApplication::STATUS_REJECTED,
            QueueApplication::STATUS_CANCELLED,
            QueueApplication::STATUS_DRAFT,
        ];

        if (in_array($application->status, $completedOrLockedStatuses)) {
            return redirect()->route('queue.status', ['token' => $token])
                ->with('error', 'Perubahan jadwal tidak dapat dilakukan karena dokumen telah selesai atau sedang dalam proses loket.');
        }

        $allowedStatuses = [
            QueueApplication::STATUS_SUBMITTED,
            QueueApplication::STATUS_UNDER_REVIEW,
            QueueApplication::STATUS_RESUBMITTED,
            QueueApplication::STATUS_PRE_VERIFIED,
            QueueApplication::STATUS_APPROVED,
            QueueApplication::STATUS_QUEUE_BOOKED,
            QueueApplication::STATUS_SCHEDULED,
        ];

        if (!in_array($application->status, $allowedStatuses)) {
            return redirect()->route('queue.status', ['token' => $token])
                ->with('error', 'Pemesanan jadwal antrean tidak dapat dilakukan untuk status permohonan saat ini.');
        }

        $isReschedule = $request->boolean('reschedule') || $request->input('reschedule') == 1 || $request->has('reschedule');

        // If queue number is already issued and not rescheduling, prevent double-booking
        if ($application->queue_number && !$isReschedule) {
            return redirect()->route('queue.status', ['token' => $token])
                ->with('info', "Nomor antrean resmi Anda telah diterbitkan: {$application->queue_number}.");
        }

        try {
            $this->slotService->bookSlot((int) $request->input('slot_id'), $application);
            $application->update([
                'scheduled_at' => now(),
            ]);

            $freshApp = $application->fresh(['slot.schedule']);

            if ($isReschedule) {
                $schedDate = $freshApp->slot?->schedule?->schedule_date ? $freshApp->slot->schedule->schedule_date->format('d/m/Y') : '';
                \App\Models\Queue\VerificationHistory::create([
                    'application_id' => $application->id,
                    'user_id'        => null,
                    'from_status'    => $application->status,
                    'to_status'      => $application->status,
                    'notes'          => "Pemohon berhasil mengubah tanggal kedatangan ke jadwal {$schedDate}. Nomor antrean: {$freshApp->queue_number}.",
                    'metadata'       => [
                        'ip'           => $request->ip(),
                        'rescheduled'  => true,
                        'queue_number' => $freshApp->queue_number,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $freshApp = $application->fresh();
        $msg = $isReschedule
            ? "Jadwal kedatangan berhasil diubah! Nomor antrean resmi Anda: {$freshApp->queue_number}."
            : "Jadwal antrean berhasil ditetapkan! Nomor antrean resmi Anda: {$freshApp->queue_number}.";

        return redirect()->route('queue.status', ['token' => $token])
            ->with('success', $msg);
    }

    /**
     * Handle document re-upload when application requires revision.
     */
    public function resubmit(Request $request, string $token): RedirectResponse
    {
        $application = QueueApplication::where('access_token', $token)->firstOrFail();

        if ($application->status !== QueueApplication::STATUS_REVISION_REQUIRED) {
            return back()->with('error', 'Status pengajuan saat ini tidak membutuhkan perbaikan dokumen.');
        }

        $files = $request->file('documents', []);

        if (empty($files) || !is_array($files)) {
            return back()->withErrors(['documents' => 'Pilih setidaknya satu file dokumen untuk diperbaiki.']);
        }

        $this->applicationService->resubmit($application, $files);

        return back()->with('success', 'Dokumen perbaikan berhasil dikirimkan kembali ke petugas verifikasi.');
    }

    /**
     * Cancel application by applicant.
     */
    public function cancel(Request $request, string $token): RedirectResponse
    {
        $application = QueueApplication::where('access_token', $token)->firstOrFail();

        $reason = $request->input('reason', 'Dibatalkan langsung oleh pemohon melalui portal.');
        $this->applicationService->cancel($application, $reason);

        return back()->with('info', 'Permohonan antrian Anda telah berhasil dibatalkan.');
    }

    /**
     * Securely stream private document file specifically to the applicant holding the matching token.
     * Prevents IDOR vulnerabilities.
     */
    public function serveDocument(string $token, int $documentId): StreamedResponse
    {
        $application = QueueApplication::where('access_token', $token)->firstOrFail();

        $doc = ApplicationDocument::where('id', $documentId)
            ->where('application_id', $application->id)
            ->firstOrFail();

        return QueueDocumentStorage::serve($doc, true);
    }
}
