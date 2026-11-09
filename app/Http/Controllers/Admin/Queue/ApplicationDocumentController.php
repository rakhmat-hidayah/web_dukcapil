<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSelfieVerification;
use App\Services\Queue\QueueDocumentStorage;
use App\Services\Queue\QueueSelfieVerificationService;
use App\Services\Queue\QueueVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationDocumentController extends Controller
{
    public function __construct(
        protected QueueVerificationService $verificationService,
        protected QueueSelfieVerificationService $selfieVerificationService
    ) {}

    /**
     * Securely serve a private document to an authenticated and authorized admin officer.
     */
    public function serve(Request $request, int $documentId): StreamedResponse
    {
        $user = Auth::user();

        // Strict role and least-privilege permission check for queue citizen documents
        $allowedRoles = [
            'Super Administrator',
            'Administrator Utama',
            'Administrator Website',
            'Supervisor Layanan KTP-el',
            'Petugas Verifikasi / Registrasi KTP-el',
            'Petugas Pengambilan KTP-el',
            'Petugas Pengarsipan',
        ];

        $hasAllowedRole = $user->isSuperAdmin()
            || $user->can('manage_queue_verification')
            || $user->roles->pluck('name')->intersect($allowedRoles)->isNotEmpty();

        if (!$user || !$hasAllowedRole) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin kewenangan tugas untuk melihat dokumen kependudukan ini.');
        }

        $document = ApplicationDocument::with(['application.selfieVerification', 'serviceDocument'])->findOrFail($documentId);

        // If this document is a destroyed selfie, return 410 Gone
        if ($document->is_destroyed || empty($document->getRawOriginal('path'))) {
            abort(410, 'Foto selfie pemohon telah dimusnahkan pasca verifikasi demi perlindungan privasi.');
        }

        $isSelfie = $document->isSelfie();

        // If already verified with DELETED status, strictly forbid access
        if ($isSelfie && $document->application?->selfieVerification?->deletion_status === QueueSelfieVerification::DELETION_DELETED) {
            abort(410, 'Foto selfie pemohon telah dimusnahkan pasca verifikasi demi perlindungan privasi.');
        }

        // Audit log preview activity without recording sensitive NIK
        Log::info("Queue document previewed: DocID {$document->id} by User {$user->id} ({$user->name}) from IP {$request->ip()}" . ($isSelfie ? ' [Biometric Selfie]' : ''));

        // Otomatis sinkronkan status dokumen menjadi Sudah Diperiksa (APPROVED) saat dibuka oleh verifikator
        // KECUALI foto selfie (karena selfie memerlukan formulir pernyataan dan konfirmasi petugas di Tahap D)
        if (!$isSelfie && !$request->boolean('preview_only', false) && $document->status === ApplicationDocument::STATUS_PENDING) {
            $document->update([
                'status'      => ApplicationDocument::STATUS_APPROVED,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
        }

        $download = $isSelfie ? false : $request->boolean('download', false);

        return QueueDocumentStorage::serve($document, !$download);
    }

    /**
     * Submit biometric selfie verification decision (Tahap D) and immediately delete physical photo.
     */
    public function verifySelfie(Request $request, int $applicationId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'decision'          => 'required|string|in:MATCH,MISMATCH',
            'statement_agreed'  => 'required|accepted',
            'statement_version' => 'nullable|string|max:20',
        ], [
            'decision.required'         => 'Hasil pemeriksaan wajib dipilih antara "FOTO SESUAI" atau "FOTO BUKAN PEMOHON".',
            'decision.in'               => 'Pilihan hasil pemeriksaan tidak valid.',
            'statement_agreed.required' => 'Pernyataan tanggung jawab hasil pemeriksaan wajib dicentang.',
            'statement_agreed.accepted' => 'Pernyataan tanggung jawab hasil pemeriksaan wajib disetujui.',
        ]);

        $application = QueueApplication::with('documents.serviceDocument')->findOrFail($applicationId);

        $verification = $this->selfieVerificationService->recordVerification(
            $application,
            $request->input('decision'),
            $request->boolean('statement_agreed'),
            Auth::user(),
            $request->input('statement_version', 'v1.0')
        );

        $freshApp = $application->fresh(['documents.serviceDocument', 'selfieVerification.officer']);

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => $verification->decision === QueueSelfieVerification::DECISION_MATCH
                    ? 'Pemeriksaan foto selesai: SESUAI DENGAN PEMOHON. Berkas foto fisik telah dimusnahkan secara aman dari server.'
                    : 'Pemeriksaan foto selesai: FOTO BUKAN PEMOHON (MISMATCH). Pengajuan ditandai butuh perbaikan dan berkas foto fisik telah dimusnahkan.',
                'verification' => $verification,
                'application'  => $freshApp,
            ]);
        }

        return back()->with('success', 'Hasil pemeriksaan foto pemohon telah disimpan dan berkas fisik foto telah dimusnahkan.');
    }

    /**
     * Mark all pending documents for this application as approved.
     */
    public function approveAll(int $applicationId): JsonResponse|RedirectResponse
    {
        $application = QueueApplication::findOrFail($applicationId);
        $user = Auth::user();

        $application->documents()
            ->where('status', ApplicationDocument::STATUS_PENDING)
            ->update([
                'status'      => ApplicationDocument::STATUS_APPROVED,
                'reviewed_by' => $user?->id,
                'reviewed_at' => now(),
            ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Seluruh berkas persyaratan ditandai sah / sudah diperiksa.',
                'documents' => $application->fresh('documents')->documents,
            ]);
        }

        return back()->with('success', 'Seluruh berkas persyaratan telah ditandai sah / sudah diperiksa.');
    }

    /**
     * Mark an individual document approved by officer.
     */
    public function approve(int $applicationId, int $docId): JsonResponse|RedirectResponse
    {
        $document = ApplicationDocument::where('id', $docId)
            ->where('application_id', $applicationId)
            ->firstOrFail();

        $this->verificationService->reviewDocument($document, ApplicationDocument::STATUS_APPROVED, null, Auth::user());

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen ditandai sah / disetujui.',
                'document' => $document->fresh(),
            ]);
        }

        return back()->with('success', 'Dokumen disetujui.');
    }

    /**
     * Mark an individual document as requiring revision.
     */
    public function reject(Request $request, int $applicationId, int $docId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $document = ApplicationDocument::where('id', $docId)
            ->where('application_id', $applicationId)
            ->firstOrFail();

        $this->verificationService->reviewDocument(
            $document,
            ApplicationDocument::STATUS_REVISION_REQUIRED,
            $request->input('reason'),
            Auth::user()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen ditandai perlu perbaikan.',
                'document' => $document->fresh(),
            ]);
        }

        return back()->with('success', 'Catatan perbaikan dokumen berhasil disimpan.');
    }
}
