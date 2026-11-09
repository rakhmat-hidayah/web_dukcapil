<?php

namespace App\Services\Queue;

use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use App\Models\Queue\VerificationHistory;
use App\Models\User;
use App\Services\ActivityLogger;
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
     * Persyaratan dokumen dikelola secara dinamis melalui Database / Panel Admin CMS.
     */
    public static function ensureAllRequiredDocuments(): void
    {
        // No-op: Mengikuti data dinamis dari database tanpa hardcode.
    }

    /**
     * Memastikan kolom is_active tersedia secara aman pada tabel queue_service_documents.
     */
    public static function ensureIsActiveColumn(): bool
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('queue_service_documents')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active')) {
                    \Illuminate\Support\Facades\Schema::table('queue_service_documents', function (\Illuminate\Database\Schema\Blueprint $table) {
                        if (!\Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active')) {
                            $table->boolean('is_active')->default(true)->after('is_required');
                        }
                    });
                }
                return \Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active');
            }
            return false;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ensureIsActiveColumn warning: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Persyaratan dokumen dikelola secara dinamis melalui Database / Panel Admin CMS.
     */
    public static function ensureSelfieRequirementForPerekaman(): void
    {
        // No-op: Mengikuti data dinamis dari database tanpa hardcode.
    }

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

            $absenceReason = $data['absence_reason'] ?? $data['representative_reason'] ?? null;
            if ($serviceType->require_absence_reason && empty($absenceReason)) {
                throw ValidationException::withMessages([
                    'absence_reason' => 'Alasan pemilik dokumen tidak dapat hadir langsung ke kantor wajib dijelaskan.',
                ]);
            }
        }

        $absenceReason = $attendanceType !== 'self' ? ($data['absence_reason'] ?? $data['representative_reason'] ?? null) : null;

        $application = new QueueApplication([
            'applicant_account_id'         => $data['applicant_account_id'] ?? \Illuminate\Support\Facades\Auth::guard('applicant')->id(),
            'reg_number'                   => QueueNumberGenerator::generateRegNumber(),
            'service_type_id'              => $serviceType->id,
            'status'                       => QueueApplication::STATUS_DRAFT,
            'applicant_name'               => mb_strtoupper(trim($data['applicant_name'])),
            'applicant_nik'                => trim($data['applicant_nik']),
            'applicant_email'              => !empty($data['applicant_email']) ? trim($data['applicant_email']) : null,
            'attendance_type'              => $attendanceType,
            'representative_nik'           => $attendanceType !== 'self' ? trim($data['representative_nik']) : null,
            'representative_name'          => $attendanceType !== 'self' ? mb_strtoupper(trim($data['representative_name'])) : null,
            'representative_relationship'  => $attendanceType !== 'self' ? ($data['representative_relationship'] ?? null) : null,
            'representative_kk_status'     => $attendanceType !== 'self' ? ($data['representative_kk_status'] ?? null) : null,
            'absence_reason'               => $absenceReason,
        ]);

        // Resilience fallback: If legacy columns still exist in the database table (e.g. pending migration on production)
        if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_phone')) {
            $application->applicant_phone = '-';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_address')) {
            $application->applicant_address = '-';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_village')) {
            $application->applicant_village = '-';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_district')) {
            $application->applicant_district = '-';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_birth_place')) {
            $application->applicant_birth_place = '-';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_birth_date')) {
            $application->applicant_birth_date = '2000-01-01';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'representative_phone')) {
            $application->representative_phone = '-';
        }

        $application->save();

        return $application;
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
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');
        if (in_array($extension, ['jpeg', 'jfif', 'pjpeg'])) {
            $extension = 'jpg';
        }

        $allowed = array_map(fn($f) => strtolower(trim($f)), $serviceDoc->accepted_formats_array);
        if (in_array('jpg', $allowed) || in_array('jpeg', $allowed) || in_array('png', $allowed)) {
            $allowed = array_unique(array_merge($allowed, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'jfif', 'pjpeg']));
        }

        if (!empty($extension) && !in_array($extension, $allowed)) {
            throw ValidationException::withMessages([
                'file' => "Format file .{$extension} tidak didukung untuk {$serviceDoc->label}. Harap gunakan format gambar (JPG/PNG) atau PDF.",
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
        $hasIsActive = static::ensureIsActiveColumn();
        $docsQuery = $application->serviceType->documents();
        if ($hasIsActive) {
            $docsQuery->where('is_active', true);
        }
        $allDocs = $docsQuery->get();
        $isRepresented = $application->is_represented;

        $uploadedDocIds = $application->documents()->pluck('service_document_id')->toArray();
        $missing = [];

        foreach ($allDocs as $doc) {
            // Check if document applies to this application based on attendance
            if ($doc->applicable_to === 'representative_only' && !$isRepresented) {
                continue;
            }
            if ($doc->applicable_to === 'self_only' && $isRepresented) {
                continue;
            }

            // Only required active documents must be uploaded
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

        // Otomatis terbitkan nomor antrean resmi & alokasikan jadwal segera setelah pemohon mengirim berkas verifikasi
        if (!$application->queue_number) {
            try {
                $slot = $this->slotService->getEarliestAvailableSlot($application->service_type_id);
                if ($slot) {
                    $this->slotService->bookSlot($slot->id, $application);
                    $application->refresh();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Auto-issuing queue number on submit warning [{$application->reg_number}]: " . $e->getMessage());
            }
        }

        $application->update([
            'status'               => QueueApplication::STATUS_SUBMITTED,
            'submitted_at'         => now(),
            'scheduled_at'         => $application->scheduled_at ?: now(),
            'statement_agreed'     => true,
            'statement_agreed_at'  => now(),
        ]);

        $queueNote = $application->queue_number ? " Nomor antrean diterbitkan: {$application->queue_number}." : "";

        VerificationHistory::create([
            'application_id' => $application->id,
            'user_id'        => null,
            'from_status'    => $fromStatus,
            'to_status'      => QueueApplication::STATUS_SUBMITTED,
            'notes'          => "Permohonan layanan dan berkas digital dikirimkan oleh pemohon untuk pra-verifikasi.{$queueNote}",
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
     * Purge all draft applications older than configured waiting time from creation.
     *
     * @param int|null $minutes Maximum age in minutes (default from queue.draft_timeout_minutes setting)
     * @return int Number of purged drafts
     */
    public function purgeExpiredDrafts(?int $minutes = null): int
    {
        if ($minutes === null) {
            $minutes = (int) \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes', 15);
        }

        $cutoff = now()->subMinutes($minutes);

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
            Log::info("Purged {$count} expired draft application(s) older than {$minutes} minutes.");
        }

        return $count;
    }

    /**
     * Delete an application permanently by Super Admin.
     * Releases booked quota, cleans up storage, removes related records, and logs the action.
     */
    public function deleteApplicationByAdmin(QueueApplication $application, User $admin): bool
    {
        // 1. Release queue slot quota if booked
        if ($application->queue_slot_id) {
            try {
                $this->slotService->releaseSlot($application);
            } catch (\Throwable $e) {
                Log::warning("Slot release error on admin delete [{$application->reg_number}]: " . $e->getMessage());
            }
        }

        // 2. Delete all physical files and their database records
        foreach ($application->documents as $doc) {
            try {
                QueueDocumentStorage::delete($doc);
            } catch (\Throwable $e) {
                Log::warning("Document file deletion error [{$doc->id}]: " . $e->getMessage());
            }
            $doc->forceDelete();
        }

        // 3. Delete physical storage folder
        $year = $application->created_at ? $application->created_at->format('Y') : date('Y');
        $month = $application->created_at ? $application->created_at->format('m') : date('m');
        $dirPath = QueueDocumentStorage::BASE_PATH . "/{$year}/{$month}/{$application->id}";
        try {
            Storage::disk(QueueDocumentStorage::DISK)->deleteDirectory($dirPath);
        } catch (\Throwable $e) {
            Log::warning("Application directory deletion error: " . $e->getMessage());
        }

        // 4. Delete related logs & handover
        try {
            $application->verificationHistory()->delete();
            $application->notificationLogs()->delete();
            if ($application->handover()->exists()) {
                $handover = $application->handover;
                if ($handover) {
                    $handover->logs()->delete();
                    $handover->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Related records deletion error: " . $e->getMessage());
        }

        // 5. Activity log
        ActivityLogger::log(
            "Super Admin ({$admin->username}) menghapus permanen permohonan antrian {$application->reg_number} ({$application->applicant_name})",
            null,
            'delete_queue_application',
            [
                'reg_number'     => $application->reg_number,
                'queue_number'   => $application->queue_number,
                'applicant_name' => $application->applicant_name,
                'applicant_nik'  => $application->applicant_nik,
                'deleted_by'     => $admin->username,
            ]
        );

        // 6. Force delete
        return (bool) $application->forceDelete();
    }
}
