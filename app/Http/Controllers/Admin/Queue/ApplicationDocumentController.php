<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Services\Queue\QueueDocumentStorage;
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
        protected QueueVerificationService $verificationService
    ) {}

    /**
     * Securely serve a private document to an authenticated and authorized admin officer.
     */
    public function serve(Request $request, int $documentId): StreamedResponse
    {
        $user = Auth::user();

        // Strict role/permission check
        if (!$user || (!$user->hasAnyRole(['Super Administrator', 'Administrator Utama', 'Website Administrator', 'Administrator Website']) && !$user->can('manage_queue_verification'))) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat dokumen kependudukan ini.');
        }

        $document = ApplicationDocument::with('application')->findOrFail($documentId);

        // Audit log preview activity without recording sensitive NIK
        Log::info("Queue document previewed: DocID {$document->id} by User {$user->id} ({$user->name}) from IP {$request->ip()}");

        // Otomatis sinkronkan status dokumen menjadi Sudah Diperiksa (APPROVED) saat dibuka oleh verifikator
        // Kecuali saat dipanggil sebagai preview thumbnail di tabel antrean (preview_only)
        if (!$request->boolean('preview_only', false) && $document->status === ApplicationDocument::STATUS_PENDING) {
            $document->update([
                'status'      => ApplicationDocument::STATUS_APPROVED,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
        }

        $download = $request->boolean('download', false);

        return QueueDocumentStorage::serve($document, !$download);
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
