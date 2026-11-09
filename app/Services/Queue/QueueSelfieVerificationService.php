<?php

namespace App\Services\Queue;

use App\Jobs\Queue\DeleteSelfieFileJob;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSelfieVerification;
use App\Models\Queue\VerificationHistory;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QueueSelfieVerificationService
{
    /**
     * Allowed officer roles permitted to verify applicant biometric selfies.
     */
    const ALLOWED_ROLES = [
        'Super Administrator',
        'Administrator Utama',
        'Administrator Website',
        'Website Administrator',
        'Supervisor Layanan KTP-el',
        'Petugas Verifikasi / Registrasi KTP-el',
        'Petugas Pengambilan KTP-el',
        'Petugas Pengarsipan',
        'Queue Officer',
        'Petugas Antrean',
    ];

    /**
     * Verify officer authority.
     */
    public function authorizeOfficer(?User $user): void
    {
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu untuk melakukan verifikasi.');
        }

        $hasAllowedRole = $user->isSuperAdmin()
            || $user->can('manage_queue_verification')
            || $user->roles->pluck('name')->intersect(self::ALLOWED_ROLES)->isNotEmpty();

        if (!$hasAllowedRole) {
            abort(403, 'Akses ditolak. Anda tidak memiliki kewenangan tugas untuk memverifikasi foto biometrik pemohon.');
        }
    }

    /**
     * Record officer selfie verification result and trigger immediate physical file destruction.
     */
    public function recordVerification(
        QueueApplication $application,
        string $decision,
        bool $statementAgreed,
        User $officer,
        string $statementVersion = 'v1.0'
    ): QueueSelfieVerification {
        $this->authorizeOfficer($officer);

        $decision = strtoupper(trim($decision));
        if (!in_array($decision, [QueueSelfieVerification::DECISION_MATCH, QueueSelfieVerification::DECISION_MISMATCH])) {
            throw ValidationException::withMessages([
                'decision' => 'Hasil verifikasi wajib dipilih antara "FOTO SESUAI" atau "FOTO BUKAN PEMOHON".',
            ]);
        }

        if (!$statementAgreed) {
            throw ValidationException::withMessages([
                'statement_agreed' => 'Anda wajib mencentang pernyataan tanggung jawab atas hasil pemeriksaan yang dipilih.',
            ]);
        }

        $selfieDoc = $application->selfie_document;

        // Persist verification audit metadata
        $verification = QueueSelfieVerification::updateOrCreate(
            ['application_id' => $application->id],
            [
                'document_id'        => $selfieDoc?->id,
                'decision'           => $decision,
                'verified_by'        => $officer->id,
                'verified_by_name'   => $officer->name,
                'verified_at'        => now(),
                'statement_version'  => $statementVersion,
                'statement_agreed'   => true,
                'deletion_status'    => QueueSelfieVerification::DELETION_PENDING,
                'deletion_error'     => null,
            ]
        );

        // Update document status & record verification history
        $originalStatus = $application->status;

        if ($decision === QueueSelfieVerification::DECISION_MATCH) {
            if ($selfieDoc) {
                $selfieDoc->update([
                    'status'        => ApplicationDocument::STATUS_APPROVED,
                    'reviewer_note' => 'Foto wajah pemohon diverifikasi SESUAI (MATCH). Berkas foto dimusnahkan pasca verifikasi.',
                    'reviewed_by'   => $officer->id,
                    'reviewed_at'   => now(),
                ]);
            }

            VerificationHistory::create([
                'application_id' => $application->id,
                'user_id'        => $officer->id,
                'from_status'    => $originalStatus,
                'to_status'      => $originalStatus,
                'notes'          => "Pemeriksaan foto wajah pemohon: SESUAI (MATCH). Pernyataan disetujui ({$statementVersion}).",
                'metadata'       => [
                    'decision'          => $decision,
                    'statement_version' => $statementVersion,
                ],
            ]);
        } else {
            // MISMATCH decision: flag application for revision, prevent issuance
            if ($selfieDoc) {
                $selfieDoc->update([
                    'status'        => ApplicationDocument::STATUS_REVISION_REQUIRED,
                    'reviewer_note' => 'Foto wajah pemohon diverifikasi TIDAK SESUAI (MISMATCH). Harap unggah ulang foto identitas asli atau datang langsung.',
                    'reviewed_by'   => $officer->id,
                    'reviewed_at'   => now(),
                ]);
            }

            $application->update([
                'status'         => QueueApplication::STATUS_REVISION_REQUIRED,
                'revision_notes' => 'Hasil verifikasi biometrik: Foto wajah yang diunggah TIDAK SESUAI dengan data pemohon (MISMATCH). Silakan unggah foto identitas yang valid atau datang langsung ke loket Disdukcapil.',
            ]);

            VerificationHistory::create([
                'application_id' => $application->id,
                'user_id'        => $officer->id,
                'from_status'    => $originalStatus,
                'to_status'      => QueueApplication::STATUS_REVISION_REQUIRED,
                'notes'          => "Pemeriksaan foto wajah pemohon: BUKAN PEMOHON (MISMATCH). Pengajuan dialihkan ke perbaikan dokumen. Pernyataan disetujui ({$statementVersion}).",
                'metadata'       => [
                    'decision'          => $decision,
                    'statement_version' => $statementVersion,
                ],
            ]);
        }

        // Immediately execute secure physical file deletion and disk verification
        $this->deletePhysicalSelfie($verification, $selfieDoc);

        return $verification->fresh(['officer', 'document']);
    }

    /**
     * Physically delete the selfie photo from private storage, verify disk state, and update DB.
     */
    public function deletePhysicalSelfie(QueueSelfieVerification $verification, ?ApplicationDocument $document = null): bool
    {
        $document = $document ?: $verification->document ?: $verification->application?->selfie_document;

        if (!$document) {
            $verification->update([
                'deletion_status' => QueueSelfieVerification::DELETION_DELETED,
                'deleted_at'      => now(),
                'deletion_error'  => null,
            ]);
            return true;
        }

        $path = $document->getRawOriginal('path');

        if (empty($path) || $document->is_destroyed) {
            $verification->update([
                'deletion_status' => QueueSelfieVerification::DELETION_DELETED,
                'deleted_at'      => $verification->deleted_at ?: now(),
                'deletion_error'  => null,
            ]);
            return true;
        }

        $disk = QueueDocumentStorage::DISK;

        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);

                // Strictly verify file is no longer present on active physical disk
                if (Storage::disk($disk)->exists($path)) {
                    throw new \RuntimeException("File selfie {$path} masih terdeteksi pada filesystem disk aktif setelah eksekusi delete.");
                }
            }

            $verification->update([
                'deletion_status' => QueueSelfieVerification::DELETION_DELETED,
                'deleted_at'      => now(),
                'deletion_error'  => null,
            ]);

            $document->update([
                'is_destroyed'    => true,
                'destroyed_at'    => now(),
                'path'            => '',
                'file_size_bytes' => 0,
            ]);

            Log::info("Selfie photo permanently destroyed: App {$verification->application_id}, Doc {$document->id}, Officer {$verification->verified_by_name} [Decision: {$verification->decision}]");
            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to destroy selfie photo [App {$verification->application_id}, Doc {$document->id}]: " . $e->getMessage());

            $verification->update([
                'deletion_status' => QueueSelfieVerification::DELETION_FAILED,
                'deletion_error'  => $e->getMessage(),
                'retry_count'     => $verification->retry_count + 1,
            ]);

            // Dispatch background queue retry
            try {
                DeleteSelfieFileJob::dispatch($verification->id)->delay(now()->addSeconds(30));
            } catch (\Throwable $qe) {
                Log::warning("Queue dispatch error for selfie retry job: " . $qe->getMessage());
            }

            return false;
        }
    }

    /**
     * Retry any unfulfilled selfie deletions across the application.
     */
    public function retryFailedDeletions(): int
    {
        $pending = QueueSelfieVerification::where('deletion_status', '!=', QueueSelfieVerification::DELETION_DELETED)
            ->with(['application.documents', 'document'])
            ->get();

        $count = 0;
        foreach ($pending as $item) {
            if ($this->deletePhysicalSelfie($item)) {
                $count++;
            }
        }

        return $count;
    }
}
