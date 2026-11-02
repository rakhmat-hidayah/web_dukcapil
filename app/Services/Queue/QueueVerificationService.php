<?php

namespace App\Services\Queue;

use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\KtpHandover;
use App\Models\Queue\KtpHandoverLog;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\VerificationHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QueueVerificationService
{
    public function __construct(
        protected QueueSlotService $slotService,
        protected QueueNotificationService $notificationService
    ) {}

    /**
     * Mark an application as currently under review by an online verification officer.
     */
    public function startReview(QueueApplication $application, ?User $officer = null): void
    {
        if (in_array($application->status, [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED])) {
            $this->transition(
                $application,
                QueueApplication::STATUS_UNDER_REVIEW,
                $officer,
                'Petugas memulai proses verifikasi dokumen kependudukan secara online.'
            );
        }
    }

    /**
     * Pre-verify online documents. Marks application as PRE_VERIFIED so applicant can choose queue schedule.
     */
    public function preVerify(QueueApplication $application, ?User $officer = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($application, $officer, $notes) {
            $fromStatus = $application->status;

            $application->update([
                'status'       => QueueApplication::STATUS_PRE_VERIFIED,
                'approved_at'  => now(),
                'notes'        => $notes ?: $application->notes,
            ]);

            // Automatically approve any pending documents
            $application->documents()
                ->where('status', ApplicationDocument::STATUS_PENDING)
                ->update([
                    'status'      => ApplicationDocument::STATUS_APPROVED,
                    'reviewed_by' => $officer ? $officer->id : null,
                    'reviewed_at' => now(),
                ]);

            $this->recordHistory(
                $application,
                $fromStatus,
                QueueApplication::STATUS_PRE_VERIFIED,
                $officer,
                'Pra-verifikasi dokumen online berhasil disetujui. Pemohon dapat memilih jadwal antrean kedatangan.' . ($notes ? " Catatan: {$notes}" : '')
            );

            // Daftarkan otomatis ke antrean serah terima fisik (MENUNGGU_KETERSEDIAAN)
            $handover = \App\Models\Queue\KtpHandover::firstOrCreate(
                ['application_id' => $application->id],
                ['status' => \App\Models\Queue\KtpHandover::STATUS_PENDING]
            );

            if ($handover->wasRecentlyCreated) {
                \App\Models\Queue\KtpHandoverLog::create([
                    'handover_id'    => $handover->id,
                    'application_id' => $application->id,
                    'user_id'        => $officer?->id,
                    'action'         => 'CREATED',
                    'from_status'    => null,
                    'to_status'      => \App\Models\Queue\KtpHandover::STATUS_PENDING,
                    'notes'          => 'Permohonan memenuhi syarat verifikasi administrasi online dan masuk antrean ketersediaan fisik KTP-el.',
                ]);
            }
        });

        // Send notification email
        $this->notificationService->dispatch($application, 'pre_verified');
    }

    /**
     * Book a queue schedule slot after application is PRE_VERIFIED.
     */
    public function bookQueueSlot(QueueApplication $application, int $slotId): QueueApplication
    {
        // If already booked with a queue number, return idempotently to prevent duplicate issuance
        if ($application->status === QueueApplication::STATUS_QUEUE_BOOKED && $application->queue_number) {
            return $application->fresh(['serviceType', 'slot.schedule', 'documents']);
        }

        if (!in_array($application->status, [QueueApplication::STATUS_PRE_VERIFIED, QueueApplication::STATUS_APPROVED])) {
            throw new \InvalidArgumentException("Pemesanan antrean hanya dapat dilakukan setelah dokumen dinyatakan lolos pra-verifikasi online (PRE_VERIFIED).");
        }

        return DB::transaction(function () use ($application, $slotId) {
            // Book slot with pessimistic locking
            $slot = $this->slotService->bookSlot($slotId, $application);

            $fromStatus = $application->status;
            $application->update([
                'status'       => QueueApplication::STATUS_QUEUE_BOOKED,
                'scheduled_at' => now(),
            ]);

            $scheduleDate = $slot->schedule ? $slot->schedule->schedule_date : '-';
            $sessionName = $slot->schedule ? $slot->schedule->session_name : '-';

            $this->recordHistory(
                $application,
                $fromStatus,
                QueueApplication::STATUS_QUEUE_BOOKED,
                null,
                "Jadwal antrean berhasil dipesan: Tanggal {$scheduleDate} ({$sessionName}). Nomor Antrean: {$application->queue_number}."
            );

            // Send notification
            $this->notificationService->dispatch($application, 'queue_booked');

            return $application->fresh(['serviceType', 'slot.schedule', 'documents']);
        });
    }

    /**
     * Legacy / Direct approve: combines pre-verify and direct slot assignment.
     */
    public function approve(QueueApplication $application, int $slotId, ?User $officer = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($application, $slotId, $officer, $notes) {
            $slot = $this->slotService->bookSlot($slotId, $application);

            $application->update([
                'status'       => QueueApplication::STATUS_QUEUE_BOOKED,
                'approved_at'  => now(),
                'scheduled_at' => now(),
                'notes'        => $notes,
            ]);

            $application->documents()
                ->where('status', ApplicationDocument::STATUS_PENDING)
                ->update([
                    'status'      => ApplicationDocument::STATUS_APPROVED,
                    'reviewed_by' => $officer ? $officer->id : null,
                    'reviewed_at' => now(),
                ]);

            $this->recordHistory(
                $application,
                $application->getOriginal('status') ?: QueueApplication::STATUS_UNDER_REVIEW,
                QueueApplication::STATUS_QUEUE_BOOKED,
                $officer,
                "Pengajuan disetujui. Tiket nomor antrean diterbitkan: {$application->queue_number}" . ($notes ? " ({$notes})" : "")
            );
        });

        $this->notificationService->dispatch($application, 'approved');
    }

    /**
     * Request document revision from the applicant without creating a new application.
     */
    public function requestRevision(QueueApplication $application, string $notes, array $documentFeedback = [], ?User $officer = null): void
    {
        DB::transaction(function () use ($application, $notes, $documentFeedback, $officer) {
            foreach ($documentFeedback as $docId => $feedback) {
                if (is_array($feedback)) {
                    $status = $feedback['status'] ?? ApplicationDocument::STATUS_REVISION_REQUIRED;
                    $note = $feedback['note'] ?? null;
                } else {
                    $status = ApplicationDocument::STATUS_REVISION_REQUIRED;
                    $note = (string) $feedback;
                }

                ApplicationDocument::where('id', $docId)
                    ->where('application_id', $application->id)
                    ->update([
                        'status'        => $status,
                        'reviewer_note' => $note,
                        'reviewed_by'   => $officer ? $officer->id : null,
                        'reviewed_at'   => now(),
                    ]);
            }

            $fromStatus = $application->status;

            $application->update([
                'status'         => QueueApplication::STATUS_REVISION_REQUIRED,
                'revision_notes' => $notes,
            ]);

            $this->recordHistory(
                $application,
                $fromStatus,
                QueueApplication::STATUS_REVISION_REQUIRED,
                $officer,
                "Permintaan perbaikan dokumen dikirim: {$notes}"
            );
        });

        $this->notificationService->dispatch($application, 'revision');
    }

    /**
     * Reject application permanently with clear reason.
     */
    public function reject(QueueApplication $application, string $reason, ?User $officer = null): void
    {
        $fromStatus = $application->status;

        $application->update([
            'status'           => QueueApplication::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        $this->recordHistory(
            $application,
            $fromStatus,
            QueueApplication::STATUS_REJECTED,
            $officer,
            "Pengajuan ditolak. Alasan: {$reason}"
        );

        $this->notificationService->dispatch($application, 'rejected');
    }

    /**
     * Counter Check-in: Record physical arrival of applicant or representative at Disdukcapil office.
     */
    public function checkIn(QueueApplication $application, array $data, ?User $officer = null): void
    {
        $fromStatus = $application->status;

        $attendedPersonType = $data['attended_person_type'] ?? ($application->attendance_type === 'self' ? 'self' : 'representative');
        $attendedName = $data['attended_person_name'] ?? ($attendedPersonType === 'self' ? $application->applicant_name : $application->representative_name);
        $attendedNik = $data['attended_person_nik'] ?? ($attendedPersonType === 'self' ? $application->applicant_nik : $application->representative_nik);

        $application->update([
            'status'               => QueueApplication::STATUS_ARRIVED,
            'checked_in_at'        => now(),
            'checked_in_by'        => $officer ? $officer->id : null,
            'attended_person_type' => $attendedPersonType,
            'attended_person_name' => $attendedName,
            'attended_person_nik'  => $attendedNik,
        ]);

        $this->recordHistory(
            $application,
            $fromStatus,
            QueueApplication::STATUS_ARRIVED,
            $officer,
            "Check-in kehadiran di loket kantor. Dihadiri oleh: {$attendedName} ({$attendedPersonType})."
        );
    }

    /**
     * Counter Physical Verification: Officer inspects physical original documents and attendee authorization.
     */
    public function physicalVerification(QueueApplication $application, array $data, ?User $officer = null): void
    {
        $decision = $data['decision'] ?? 'APPROVED_FOR_SERVICE';
        $notes = $data['notes'] ?? null;
        $matchIdentity = (bool) ($data['match_identity'] ?? true);
        $matchDocs = (bool) ($data['match_documents'] ?? true);
        $matchAuth = isset($data['match_authorization']) ? (bool) $data['match_authorization'] : true;

        $fromStatus = $application->status;

        $newStatus = match ($decision) {
            'APPROVED_FOR_SERVICE' => QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            'REVISION_REQUIRED'    => QueueApplication::STATUS_REVISION_REQUIRED,
            'REJECTED'             => QueueApplication::STATUS_REJECTED,
            'RESCHEDULE'           => QueueApplication::STATUS_PRE_VERIFIED,
            'NO_SHOW'              => QueueApplication::STATUS_EXPIRED,
            default                => QueueApplication::STATUS_APPROVED_FOR_SERVICE,
        };

        // If rescheduled, release slot
        if ($decision === 'RESCHEDULE' && $application->queue_slot_id) {
            $this->slotService->releaseSlot($application);
            $application->queue_slot_id = null;
            $application->queue_number = null;
        }

        $application->update([
            'status'                         => $newStatus,
            'physical_verified_at'           => now(),
            'physical_verified_by'           => $officer ? $officer->id : null,
            'physical_verification_decision' => $decision,
            'physical_verification_notes'    => $notes,
            'physical_match_identity'        => $matchIdentity,
            'physical_match_documents'       => $matchDocs,
            'physical_match_authorization'   => $matchAuth,
        ]);

        $summary = "Pemeriksaan dokumen fisik di loket. Keputusan: {$decision}.";
        if ($notes) {
            $summary .= " Catatan: {$notes}.";
        }

        $this->recordHistory($application, $fromStatus, $newStatus, $officer, $summary);

        // If approved for service, ensure handover tracking is active in MENUNGGU_KETERSEDIAAN
        if ($newStatus === QueueApplication::STATUS_APPROVED_FOR_SERVICE) {
            $handover = KtpHandover::firstOrCreate(
                ['application_id' => $application->id],
                ['status' => KtpHandover::STATUS_PENDING]
            );

            if ($handover->status !== KtpHandover::STATUS_READY && $handover->status !== KtpHandover::STATUS_HANDED_OVER) {
                $handover->update([
                    'status'        => KtpHandover::STATUS_PENDING,
                    'ready_notes'   => $notes,
                    'has_exception' => false,
                ]);
            }

            KtpHandoverLog::create([
                'handover_id'    => $handover->id,
                'application_id' => $application->id,
                'user_id'        => $officer?->id,
                'action'         => 'PHYSICAL_VERIFIED',
                'from_status'    => $handover->status,
                'to_status'      => KtpHandover::STATUS_PENDING,
                'notes'          => 'Dokumen fisik asli pemohon telah diverifikasi di loket dan disetujui. Data masuk antrean ketersediaan fisik KTP-el.' . ($notes ? " Catatan: {$notes}" : ''),
            ]);
        }
    }

    /**
     * Start processing: Biometric recording or printing KTP-el.
     * Updates handover status to MENUNGGU_KETERSEDIAAN so handover officer knows it is being printed.
     */
    public function startProcessing(QueueApplication $application, ?User $officer = null, ?string $notes = null): void
    {
        $fromStatus = $application->status;

        $application->update([
            'status' => QueueApplication::STATUS_PROCESSING,
            'notes'  => $notes ?: $application->notes,
        ]);

        $this->recordHistory(
            $application,
            $fromStatus,
            QueueApplication::STATUS_PROCESSING,
            $officer,
            'Permohonan sedang diproses oleh petugas (perekaman biometrik / pencetakan fisik kartu KTP-el).' . ($notes ? " Catatan: {$notes}" : '')
        );

        // Sync with Serah Terima KTP-el module (MENUNGGU_KETERSEDIAAN)
        $handover = KtpHandover::firstOrCreate(
            ['application_id' => $application->id],
            ['status' => KtpHandover::STATUS_PENDING]
        );
        $fromHandoverStatus = $handover->status;
        $handover->update([
            'status'          => KtpHandover::STATUS_PENDING,
            'ready_marked_at' => null,
            'ready_marked_by' => null,
            'ready_notes'     => $notes,
            'has_exception'   => false,
        ]);

        KtpHandoverLog::create([
            'handover_id'    => $handover->id,
            'application_id' => $application->id,
            'user_id'        => $officer?->id,
            'action'         => 'PROCESSING',
            'from_status'    => $fromHandoverStatus,
            'to_status'      => KtpHandover::STATUS_PENDING,
            'notes'          => 'Operator verifikasi/cetak menginformasikan ke bagian penerimaan bahwa KTP-el sedang dalam proses cetak/rekam (belum selesai).' . ($notes ? " Catatan: {$notes}" : ''),
        ]);
    }

    /**
     * Mark physical card as printed and ready for pickup (SIAP_DIAMBIL).
     * Synchronizes directly with Serah Terima KTP-el module for handover officers.
     */
    public function markReadyForPickup(QueueApplication $application, ?User $officer = null, ?string $notes = null): void
    {
        $fromStatus = $application->status;

        $application->update([
            'status' => QueueApplication::STATUS_READY_FOR_PICKUP,
            'notes'  => $notes ?: $application->notes,
        ]);

        $this->recordHistory(
            $application,
            $fromStatus,
            QueueApplication::STATUS_READY_FOR_PICKUP,
            $officer,
            'Fisik KTP-el telah selesai dicetak dan siap diambil di loket penyerahan.' . ($notes ? " Catatan: {$notes}" : '')
        );

        // Sync with Serah Terima KTP-el module (SIAP_DIAMBIL)
        $handover = KtpHandover::firstOrCreate(
            ['application_id' => $application->id],
            ['status' => KtpHandover::STATUS_PENDING]
        );
        $fromHandoverStatus = $handover->status;
        $handover->update([
            'status'          => KtpHandover::STATUS_READY,
            'ready_marked_at' => now(),
            'ready_marked_by' => $officer?->id,
            'ready_notes'     => $notes,
            'has_exception'   => false,
        ]);

        KtpHandoverLog::create([
            'handover_id'    => $handover->id,
            'application_id' => $application->id,
            'user_id'        => $officer?->id,
            'action'         => 'MARKED_READY',
            'from_status'    => $fromHandoverStatus,
            'to_status'      => KtpHandover::STATUS_READY,
            'notes'          => 'Operator verifikasi/cetak menginformasikan ke bagian penerimaan bahwa fisik KTP-el telah selesai dicetak dan siap diserahkan kepada pemohon.' . ($notes ? " Catatan: {$notes}" : ''),
        ]);

        $this->notificationService->dispatch($application, 'ready_for_pickup');
    }

    /**
     * Complete handover: Physical card handed over to applicant or authorized representative.
     */
    public function completeHandover(QueueApplication $application, array $data, ?User $officer = null): void
    {
        $receiverType = $data['received_by_type'] ?? 'self';
        $receiverName = $data['receiver_name'] ?? ($receiverType === 'self' ? $application->applicant_name : ($application->representative_name ?: ''));
        $receiverNik = $data['receiver_nik'] ?? ($receiverType === 'self' ? $application->applicant_nik : ($application->representative_nik ?: ''));
        $receiverPhone = $data['receiver_phone'] ?? ($receiverType === 'self' ? $application->applicant_phone : ($application->representative_phone ?: ''));
        $notes = $data['handover_notes'] ?? null;

        $fromStatus = $application->status;

        $application->update([
            'status'           => QueueApplication::STATUS_COMPLETED,
            'completed_at'     => now(),
            'handover_at'      => now(),
            'handover_by'      => $officer ? $officer->id : null,
            'received_by_type' => $receiverType,
            'receiver_name'    => $receiverName,
            'receiver_nik'     => $receiverNik,
            'receiver_phone'   => $receiverPhone,
            'handover_notes'   => $notes,
        ]);

        $this->recordHistory(
            $application,
            $fromStatus,
            QueueApplication::STATUS_COMPLETED,
            $officer,
            "KTP-el fisik telah diserahkan kepada: {$receiverName} ({$receiverType}). NIK Penerima: {$receiverNik}." . ($notes ? " Catatan: {$notes}" : '')
        );
    }

    /**
     * Single document review action.
     */
    public function reviewDocument(ApplicationDocument $document, string $status, ?string $note = null, ?User $officer = null): ApplicationDocument
    {
        $document->update([
            'status'        => $status,
            'reviewer_note' => $note,
            'reviewed_by'   => $officer ? $officer->id : null,
            'reviewed_at'   => now(),
        ]);

        return $document;
    }

    private function transition(QueueApplication $application, string $toStatus, ?User $officer = null, ?string $notes = null): void
    {
        $fromStatus = $application->status;
        $application->update(['status' => $toStatus]);
        $this->recordHistory($application, $fromStatus, $toStatus, $officer, $notes);
    }

    private function recordHistory(QueueApplication $application, string $from, string $to, ?User $officer = null, ?string $notes = null): void
    {
        VerificationHistory::create([
            'application_id' => $application->id,
            'user_id'        => $officer ? $officer->id : null,
            'from_status'    => $from,
            'to_status'      => $to,
            'notes'          => $notes,
            'metadata'       => [
                'ip' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 150),
            ],
        ]);
    }
}
