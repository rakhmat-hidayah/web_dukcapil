<?php

namespace App\Services\Queue;

use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use App\Models\Queue\VerificationHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QueueApplicationService
{
    public function __construct(
        protected QueueNotificationService $notificationService,
        protected QueueSlotService $slotService
    ) {}

    /**
     * Create a new draft application with identity and representation validation.
     */
    public function createDraft(array $data, int $serviceTypeId): QueueApplication
    {
        $serviceType = ServiceType::active()->findOrFail($serviceTypeId);

        $attendanceType = $data['attendance_type'] ?? 'self';
        if (!in_array($attendanceType, ['self', 'family_in_kk', 'family_out_kk', 'other'])) {
            $attendanceType = 'self';
        }

        // 1. Strict Biometric Recording Policy Check
        if ($serviceType->is_biometric_first_recording && $attendanceType !== 'self') {
            throw ValidationException::withMessages([
                'attendance_type' => 'Layanan Perekaman KTP-el Pemula WAJIB HADIR SENDIRI secara fisik di kantor Disdukcapil untuk perekaman biometrik (sidik jari, iris mata, foto wajah) dan TIDAK DAPAT DIWAKILKAN.',
            ]);
        }

        // 2. Service Specific Representation Policies
        if ($attendanceType === 'family_in_kk' && !$serviceType->allow_family_in_kk) {
            throw ValidationException::withMessages([
                'attendance_type' => 'Layanan ini tidak mengizinkan perwakilan anggota keluarga dalam satu KK sesuai SOP Disdukcapil.',
            ]);
        }

        if ($attendanceType === 'family_out_kk' && !$serviceType->allow_family_out_kk) {
            throw ValidationException::withMessages([
                'attendance_type' => 'Layanan ini tidak mengizinkan perwakilan anggota keluarga di luar KK.',
            ]);
        }

        if ($attendanceType === 'other' && !$serviceType->allow_other) {
            throw ValidationException::withMessages([
                'attendance_type' => 'Layanan ini tidak mengizinkan perwakilan oleh pihak lain / kuasa hukum.',
            ]);
        }

        // 3. Representative Mandatory Fields
        if ($attendanceType !== 'self') {
            $repNik = preg_replace('/[^0-9]/', '', $data['representative_nik'] ?? '');
            if (strlen($repNik) !== 16) {
                throw ValidationException::withMessages([
                    'representative_nik' => 'NIK orang yang mewakili harus berjumlah 16 digit angka.',
                ]);
            }

            if (empty($data['representative_name'])) {
                throw ValidationException::withMessages([
                    'representative_name' => 'Nama lengkap orang yang mewakili wajib diisi.',
                ]);
            }

            if (empty($data['representative_phone'])) {
                throw ValidationException::withMessages([
                    'representative_phone' => 'Nomor WhatsApp / telepon orang yang mewakili wajib diisi.',
                ]);
            }

            $absenceReason = $data['absence_reason'] ?? $data['representative_reason'] ?? null;
            if ($serviceType->require_absence_reason && empty($absenceReason)) {
                throw ValidationException::withMessages([
                    'absence_reason' => 'Alasan pemilik dokumen tidak dapat hadir langsung ke kantor wajib dijelaskan.',
                ]);
            }
        }

        $absenceReason = $attendanceType !== 'self' ? ($data['absence_reason'] ?? $data['representative_reason'] ?? null) : null;

        return QueueApplication::create([
            'reg_number'                   => QueueNumberGenerator::generateRegNumber(),
            'service_type_id'              => $serviceType->id,
            'status'                       => QueueApplication::STATUS_DRAFT,
            'applicant_name'               => trim($data['applicant_name']),
            'applicant_nik'                => trim($data['applicant_nik']),
            'applicant_birth_place'        => !empty($data['applicant_birth_place']) ? trim($data['applicant_birth_place']) : null,
            'applicant_birth_date'         => !empty($data['applicant_birth_date']) ? $data['applicant_birth_date'] : null,
            'applicant_phone'              => trim($data['applicant_phone']),
            'applicant_email'              => !empty($data['applicant_email']) ? trim($data['applicant_email']) : null,
            'applicant_address'            => trim($data['applicant_address']),
            'applicant_village'            => !empty($data['applicant_village']) ? trim($data['applicant_village']) : null,
            'applicant_district'           => !empty($data['applicant_district']) ? trim($data['applicant_district']) : null,
            'attendance_type'              => $attendanceType,
            'representative_nik'           => $attendanceType !== 'self' ? trim($data['representative_nik']) : null,
            'representative_name'          => $attendanceType !== 'self' ? trim($data['representative_name']) : null,
            'representative_phone'         => $attendanceType !== 'self' ? trim($data['representative_phone']) : null,
            'representative_relationship'  => $attendanceType !== 'self' ? ($data['representative_relationship'] ?? null) : null,
            'representative_kk_status'     => $attendanceType !== 'self' ? ($data['representative_kk_status'] ?? null) : null,
            'absence_reason'               => $absenceReason,
        ]);
    }

    /**
     * Upload or replace a document in private storage for an application.
     */
    public function uploadDocument(QueueApplication $application, int $serviceDocumentId, UploadedFile $file): ApplicationDocument
    {
        if (!$application->isEditable()) {
            throw new \InvalidArgumentException("Status pengajuan saat ini ({$application->status_label}) tidak mengizinkan penambahan dokumen.");
        }

        $serviceDoc = ServiceDocument::where('id', $serviceDocumentId)
            ->where('service_type_id', $application->service_type_id)
            ->firstOrFail();

        // Validate accepted format
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = $serviceDoc->accepted_formats_array;
        if (!in_array($extension, $allowed)) {
            throw ValidationException::withMessages([
                'file' => "Format file .{$extension} tidak didukung untuk {$serviceDoc->label}. Harap gunakan: " . implode(', ', $allowed),
            ]);
        }

        // Store securely into private disk
        $stored = QueueDocumentStorage::store($file, $application);

        // Delete existing uploaded document for this slot if any
        $existing = ApplicationDocument::where('application_id', $application->id)
            ->where('service_document_id', $serviceDocumentId)
            ->first();

        if ($existing) {
            QueueDocumentStorage::delete($existing);
            $existing->delete();
        }

        return ApplicationDocument::create([
            'application_id'      => $application->id,
            'service_document_id' => $serviceDocumentId,
            'original_filename'   => $stored['original_filename'],
            'stored_filename'     => $stored['stored_filename'],
            'disk'                => QueueDocumentStorage::DISK,
            'path'                => $stored['path'],
            'mime_type'           => $stored['mime_type'],
            'file_size_bytes'     => $stored['file_size_bytes'],
            'original_size_bytes' => $stored['original_size_bytes'],
            'is_compressed'       => $stored['is_compressed'],
            'width'               => $stored['width'],
            'height'              => $stored['height'],
            'status'              => ApplicationDocument::STATUS_PENDING,
        ]);
    }

    /**
     * Delete an uploaded document from draft application.
     */
    public function removeDocument(QueueApplication $application, int $documentId): bool
    {
        if (!$application->isEditable()) {
            throw new \InvalidArgumentException("Pengajuan tidak dalam status yang dapat diubah.");
        }

        $doc = ApplicationDocument::where('id', $documentId)
            ->where('application_id', $application->id)
            ->firstOrFail();

        QueueDocumentStorage::delete($doc);
        return (bool) $doc->delete();
    }

    /**
     * Submit an application for officer verification with applicant integrity statement.
     */
    public function submit(QueueApplication $application, bool $statementAgreed = true): QueueApplication
    {
        if (!$application->canBeSubmitted() && $application->status !== QueueApplication::STATUS_REVISION_REQUIRED) {
            throw new \InvalidArgumentException("Pengajuan dalam status '{$application->status_label}' tidak dapat dikirim.");
        }

        if (!$statementAgreed) {
            throw ValidationException::withMessages([
                'statement' => 'Anda wajib menyetujui pernyataan kebenaran data dan kesiapan membawa dokumen fisik asli ke kantor.',
            ]);
        }

        // Determine required documents taking into account representation policy
        $allDocs = $application->serviceType->documents()->get();
        $isRepresented = $application->is_represented;

        $uploadedDocIds = $application->documents()->pluck('service_document_id')->toArray();
        $missing = [];

        foreach ($allDocs as $doc) {
            // Check if document applies to this application
            if ($doc->applicable_to === 'representative_only' && !$isRepresented) {
                continue;
            }
            if ($doc->applicable_to === 'self_only' && $isRepresented) {
                continue;
            }

            // Safety guard: if not represented, skip any F-1.07 or proxy document
            if (!$isRepresented) {
                $docLabelLower = strtolower($doc->label ?? '');
                if (str_contains($docLabelLower, 'kuasa') || str_contains($docLabelLower, 'f-1.07') || str_contains($docLabelLower, 'f1.07') || str_contains($docLabelLower, 'dikuasakan') || str_contains($docLabelLower, 'diwakilkan')) {
                    continue;
                }
            }

            // If representative requires power of attorney specifically
            if ($doc->applicable_to === 'representative_only' && $isRepresented) {
                $docLabelLower = strtolower($doc->label);
                $isKuasaDoc = str_contains($docLabelLower, 'kuasa')
                    || str_contains($docLabelLower, 'f-1.07')
                    || str_contains($docLabelLower, 'f1.07')
                    || str_contains($docLabelLower, 'f.017');

                // If service requires power of attorney and attendance is other or family
                if ($application->serviceType->require_power_of_attorney && $isKuasaDoc) {
                    if (!in_array($doc->id, $uploadedDocIds)) {
                        $missing[] = $doc->label;
                    }
                    continue;
                }
            }

            if ($doc->is_required && !in_array($doc->id, $uploadedDocIds)) {
                $missing[] = $doc->label;
            }
        }

        if (!empty($missing)) {
            throw ValidationException::withMessages([
                'documents' => 'Masih ada berkas persyaratan yang belum lengkap: ' . implode(', ', $missing),
            ]);
        }

        $fromStatus = $application->status;

        $application->update([
            'status'               => QueueApplication::STATUS_SUBMITTED,
            'submitted_at'         => now(),
            'statement_agreed'     => true,
            'statement_agreed_at'  => now(),
        ]);

        VerificationHistory::create([
            'application_id' => $application->id,
            'user_id'        => null,
            'from_status'    => $fromStatus,
            'to_status'      => QueueApplication::STATUS_SUBMITTED,
            'notes'          => 'Permohonan layanan dan berkas digital dikirimkan oleh pemohon untuk pra-verifikasi.',
            'metadata'       => [
                'ip' => request()->ip(),
            ],
        ]);

        // Dispatch submitted email
        $this->notificationService->dispatch($application, 'submit');

        return $application->fresh(['serviceType', 'documents.serviceDocument']);
    }

    /**
     * Resubmit documents after revision request.
     */
    public function resubmit(QueueApplication $application, array $files): QueueApplication
    {
        if ($application->status !== QueueApplication::STATUS_REVISION_REQUIRED) {
            throw new \InvalidArgumentException("Hanya permohonan dengan status Perlu Perbaikan yang dapat dikirim ulang.");
        }

        DB::transaction(function () use ($application, $files) {
            foreach ($files as $serviceDocId => $file) {
                if ($file instanceof UploadedFile) {
                    $this->uploadDocument($application, (int) $serviceDocId, $file);
                }
            }

            // Reset documents that were in revision/rejected to pending
            $application->documents()
                ->whereIn('status', [ApplicationDocument::STATUS_REVISION_REQUIRED, ApplicationDocument::STATUS_REJECTED])
                ->update([
                    'status'        => ApplicationDocument::STATUS_PENDING,
                    'reviewer_note' => null,
                ]);

            $application->update([
                'status'         => QueueApplication::STATUS_RESUBMITTED,
                'submitted_at'   => now(),
                'revision_notes' => null,
            ]);

            VerificationHistory::create([
                'application_id' => $application->id,
                'user_id'        => null,
                'from_status'    => QueueApplication::STATUS_REVISION_REQUIRED,
                'to_status'      => QueueApplication::STATUS_RESUBMITTED,
                'notes'          => 'Berkas perbaikan telah diunggah ulang oleh pemohon.',
                'metadata'       => [
                    'ip' => request()->ip(),
                ],
            ]);
        });

        $this->notificationService->dispatch($application, 'submit');

        return $application->fresh(['serviceType', 'documents.serviceDocument']);
    }

    /**
     * Cancel application by applicant.
     */
    public function cancel(QueueApplication $application, string $reason = 'Permohonan dibatalkan oleh pemohon.'): void
    {
        if (!$application->canBeCancelled()) {
            throw new \InvalidArgumentException("Pengajuan dalam status '{$application->status_label}' tidak dapat dibatalkan.");
        }

        // Release slot if booked
        if ($application->queue_slot_id) {
            $this->slotService->releaseSlot($application);
        }

        $fromStatus = $application->status;
        $application->update([
            'status' => QueueApplication::STATUS_CANCELLED,
        ]);

        VerificationHistory::create([
            'application_id' => $application->id,
            'user_id'        => null,
            'from_status'    => $fromStatus,
            'to_status'      => QueueApplication::STATUS_CANCELLED,
            'notes'          => $reason,
        ]);
    }

    /**
     * Delete a single draft application, its physical uploaded files, and all related records.
     */
    public function deleteDraft(QueueApplication $draft): bool
    {
        if ($draft->status !== QueueApplication::STATUS_DRAFT) {
            throw new \InvalidArgumentException("Hanya permohonan berstatus DRAFT yang dapat dihapus otomatis.");
        }

        // Release queue slot if any booked
        if ($draft->queue_slot_id) {
            try {
                $this->slotService->releaseSlot($draft);
            } catch (\Throwable $e) {
                Log::warning("Draft slot release error: " . $e->getMessage());
            }
        }

        // Delete all physical files and their database records
        foreach ($draft->documents as $doc) {
            QueueDocumentStorage::delete($doc);
            $doc->forceDelete();
        }

        // Delete the application directory from storage if present
        $year = $draft->created_at ? $draft->created_at->format('Y') : date('Y');
        $month = $draft->created_at ? $draft->created_at->format('m') : date('m');
        $dirPath = QueueDocumentStorage::BASE_PATH . "/{$year}/{$month}/{$draft->id}";
        try {
            Storage::disk(QueueDocumentStorage::DISK)->deleteDirectory($dirPath);
        } catch (\Throwable $e) {
            Log::warning("Draft directory deletion error: " . $e->getMessage());
        }

        // Delete any related logs
        $draft->verificationHistory()->delete();
        $draft->notificationLogs()->delete();

        return (bool) $draft->forceDelete();
    }

    /**
     * Purge all draft applications older than 24 hours from creation.
     *
     * @param int $hours Maximum age in hours (default: 24)
     * @return int Number of purged drafts
     */
    public function purgeExpiredDrafts(int $hours = 24): int
    {
        $cutoff = now()->subHours($hours);

        $expiredDrafts = QueueApplication::where('status', QueueApplication::STATUS_DRAFT)
            ->where('created_at', '<=', $cutoff)
            ->with('documents')
            ->get();

        $count = 0;

        foreach ($expiredDrafts as $draft) {
            try {
                $this->deleteDraft($draft);
                $count++;
            } catch (\Throwable $e) {
                Log::error("Failed to delete expired draft #{$draft->id} ({$draft->reg_number}): " . $e->getMessage());
            }
        }

        if ($count > 0) {
            Log::info("Purged {$count} expired draft application(s) older than {$hours} hours.");
        }

        return $count;
    }
}
