<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueApplication;
use App\Services\Queue\QueueVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QueueCounterController extends Controller
{
    public function __construct(
        protected QueueVerificationService $verificationService
    ) {}

    /**
     * Show the front-desk counter check-in & physical verification workspace.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $selectedApplication = null;

        if ($search) {
            $selectedApplication = QueueApplication::where(function ($q) use ($search) {
                $q->where('reg_number', $search)
                  ->orWhere('queue_number', $search)
                  ->orWhere('applicant_nik', $search)
                  ->orWhere('representative_nik', $search);
            })
            ->with([
                'serviceType',
                'slot.schedule',
                'documents.serviceDocument',
                'verificationHistory.user',
                'checkedInUser',
                'physicalVerifiedUser',
                'handoverUser',
            ])
            ->first();
        }

        // Today's active queue list for quick lookup
        $todayApplications = QueueApplication::whereHas('slot.schedule', function ($q) {
            $q->where('schedule_date', now()->toDateString());
        })
        ->orWhereDate('checked_in_at', now()->toDateString())
        ->orWhereIn('status', [
            QueueApplication::STATUS_QUEUE_BOOKED,
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PROCESSING,
            QueueApplication::STATUS_READY_FOR_PICKUP,
        ])
        ->with(['serviceType', 'slot.schedule'])
        ->orderBy('queue_number')
        ->limit(50)
        ->get();

        return Inertia::render('Admin/Queue/Counter/Index', [
            'search'              => $search,
            'selectedApplication' => $selectedApplication,
            'todayApplications'   => $todayApplications,
        ]);
    }

    /**
     * Process counter check-in when applicant or representative physically arrives.
     */
    public function checkIn(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        $validated = $request->validate([
            'attended_person_type' => 'required|in:self,representative',
            'attended_person_name' => 'required|string|min:3|max:150',
            'attended_person_nik'  => 'required|digits:16',
        ], [
            'attended_person_type.required' => 'Pilih siapa yang hadir di loket.',
            'attended_person_name.required' => 'Nama orang yang hadir wajib diisi.',
            'attended_person_nik.required'  => 'NIK orang yang hadir wajib 16 digit.',
        ]);

        $this->verificationService->checkIn($application, $validated, Auth::user());

        return redirect()->route('admin.queue.counter.index', ['search' => $application->queue_number ?: $application->reg_number])
            ->with('success', "Check-in berhasil dicatat untuk pemohon {$application->applicant_name}.");
    }

    /**
     * Process physical document inspection & decision at counter.
     */
    public function physicalVerification(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        $validated = $request->validate([
            'decision'            => 'required|in:APPROVED_FOR_SERVICE,REVISION_REQUIRED,REJECTED,RESCHEDULE,NO_SHOW',
            'notes'               => 'nullable|string|max:1000',
            'match_identity'      => 'nullable|boolean',
            'match_documents'     => 'nullable|boolean',
            'match_authorization' => 'nullable|boolean',
        ], [
            'decision.required' => 'Pilih keputusan verifikasi fisik di loket.',
        ]);

        $this->verificationService->physicalVerification($application, $validated, Auth::user());

        return redirect()->route('admin.queue.counter.index', ['search' => $application->queue_number ?: $application->reg_number])
            ->with('success', "Pemeriksaan dokumen fisik selesai. Keputusan: {$validated['decision']}.");
    }

    /**
     * Mark application as in service / processing (recording biometrics or printing).
     */
    public function startProcessing(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);
        $this->verificationService->startProcessing($application, Auth::user(), $request->input('notes'));

        return redirect()->route('admin.queue.counter.index', ['search' => $application->queue_number ?: $application->reg_number])
            ->with('success', 'Status permohonan diubah: Sedang Diproses (Perekaman/Pencetakan).');
    }

    /**
     * Mark KTP-el as printed and ready for pickup.
     */
    public function markReadyForPickup(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);
        $this->verificationService->markReadyForPickup($application, Auth::user(), $request->input('notes'));

        return redirect()->route('admin.queue.counter.index', ['search' => $application->queue_number ?: $application->reg_number])
            ->with('success', 'Fisik KTP-el siap diambil di loket penyerahan.');
    }

    /**
     * Process physical card handover to applicant or representative.
     */
    public function completeHandover(Request $request, int $id): RedirectResponse
    {
        $application = QueueApplication::findOrFail($id);

        $validated = $request->validate([
            'received_by_type' => 'required|in:self,representative',
            'receiver_name'    => 'required|string|min:3|max:150',
            'receiver_nik'     => 'required|digits:16',
            'receiver_phone'   => 'nullable|string|max:20',
            'handover_notes'   => 'nullable|string|max:500',
        ], [
            'received_by_type.required' => 'Tentukan siapa yang menerima fisik KTP-el.',
            'receiver_name.required'    => 'Nama penerima KTP-el wajib diisi.',
            'receiver_nik.required'     => 'NIK penerima KTP-el wajib 16 digit.',
        ]);

        $this->verificationService->completeHandover($application, $validated, Auth::user());

        return redirect()->route('admin.queue.counter.index', ['search' => $application->queue_number ?: $application->reg_number])
            ->with('success', "KTP-el berhasil diserahkan kepada {$validated['receiver_name']}. Pelayanan selesai.");
    }
}
