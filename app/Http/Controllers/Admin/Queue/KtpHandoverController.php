<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\KtpHandover;
use App\Services\Queue\KtpHandoverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KtpHandoverController extends Controller
{
    public function __construct(
        protected KtpHandoverService $handoverService
    ) {}

    /**
     * Display the Serah Terima KTP-el Dashboard with accurate real-time stats.
     */
    public function dashboard(): Response
    {
        $stats = $this->handoverService->getDashboardStats();

        return Inertia::render('Admin/Handover/Dashboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * Display permohonan waiting for physical card verification (MENUNGGU_KETERSEDIAAN).
     */
    public function pending(Request $request): Response
    {
        $search = $request->query('search');

        $query = KtpHandover::pending()
            ->with(['application.serviceType'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('application', function ($q) use ($search) {
                $q->where('reg_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_nik', 'like', "%{$search}%")
                  ->orWhere('representative_name', 'like', "%{$search}%")
                  ->orWhere('representative_nik', 'like', "%{$search}%");
            });
        }

        $handovers = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Handover/Pending', [
            'handovers' => $handovers,
            'filters'   => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display cards that are physically verified and ready for pickup (SIAP_DIAMBIL).
     */
    public function ready(Request $request): Response
    {
        $search = $request->query('search');

        $query = KtpHandover::ready()
            ->with(['application.serviceType', 'readyMarkedUser'])
            ->orderBy('ready_marked_at', 'desc');

        if ($search) {
            $query->whereHas('application', function ($q) use ($search) {
                $q->where('reg_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_nik', 'like', "%{$search}%")
                  ->orWhere('representative_name', 'like', "%{$search}%")
                  ->orWhere('representative_nik', 'like', "%{$search}%");
            });
        }

        $handovers = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Handover/Ready', [
            'handovers' => $handovers,
            'filters'   => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display history of completed handovers (SUDAH_DISERAHKAN).
     */
    public function history(Request $request): Response
    {
        $search = $request->query('search');
        $date = $request->query('date');

        $query = KtpHandover::handedOver()
            ->with(['application.serviceType', 'handoverUser'])
            ->orderBy('handover_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('actual_receiver_name', 'like', "%{$search}%")
                  ->orWhere('actual_receiver_nik', 'like', "%{$search}%")
                  ->orWhereHas('application', function ($sq) use ($search) {
                      $sq->where('reg_number', 'like', "%{$search}%")
                         ->orWhere('applicant_name', 'like', "%{$search}%")
                         ->orWhere('applicant_nik', 'like', "%{$search}%");
                  });
            });
        }

        if ($date) {
            $query->whereDate('handover_at', $date);
        }

        $handovers = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Handover/History', [
            'handovers' => $handovers,
            'filters'   => [
                'search' => $search,
                'date'   => $date,
            ],
        ]);
    }

    /**
     * Display handovers that encountered issues (KENDALA_PENGAMBILAN).
     */
    public function exceptions(Request $request): Response
    {
        $search = $request->query('search');

        $query = KtpHandover::exception()
            ->with(['application.serviceType', 'exceptionReportedUser'])
            ->orderBy('exception_reported_at', 'desc');

        if ($search) {
            $query->whereHas('application', function ($q) use ($search) {
                $q->where('reg_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_nik', 'like', "%{$search}%");
            })->orWhere('exception_reason', 'like', "%{$search}%");
        }

        $handovers = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Handover/Exceptions', [
            'handovers' => $handovers,
            'filters'   => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display detail of a handover transaction and processing workspace.
     */
    public function show(int $id): Response
    {
        $handover = KtpHandover::with([
            'application.serviceType',
            'application.documents.serviceDocument',
            'logs.user',
            'readyMarkedUser',
            'handoverUser',
            'exceptionReportedUser',
            'exceptionResolvedUser',
        ])->findOrFail($id);

        return Inertia::render('Admin/Handover/Show', [
            'handover' => $handover,
        ]);
    }

    /**
     * Officer physically inspects card and marks it as ready for pickup.
     */
    public function markReady(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $handover = $this->handoverService->markReady($id, Auth::user(), $request->input('notes'));
            return back()->with('success', "Fisik KTP-el untuk pemohon {$handover->application->applicant_name} ({$handover->application->reg_number}) berhasil ditandai SIAP DIAMBIL.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Officer logs an issue during card pickup.
     */
    public function reportException(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ], [
            'reason.required' => 'Catatan alasan kendala wajib diisi.',
            'reason.min'      => 'Catatan kendala minimal 5 karakter.',
        ]);

        try {
            $this->handoverService->reportException($id, $request->input('reason'), Auth::user());
            return back()->with('success', 'Kendala pengambilan KTP-el berhasil dicatat.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Officer resolves an issue and returns card to ready status.
     */
    public function resolveException(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'notes' => 'required|string|min:5|max:1000',
        ], [
            'notes.required' => 'Catatan penyelesaian kendala wajib diisi.',
            'notes.min'      => 'Catatan penyelesaian kendala minimal 5 karakter.',
        ]);

        try {
            $this->handoverService->resolveException($id, $request->input('notes'), Auth::user());
            return back()->with('success', 'Kendala berhasil diselesaikan. KTP-el dikembalikan ke status Siap Diambil.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete physical card handover with required photo proof.
     */
    public function complete(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'actual_receiver_type'  => 'required|in:self,representative',
            'actual_receiver_name'  => 'required|string|min:3|max:150',
            'actual_receiver_nik'   => 'required|digits:16',
            'actual_receiver_phone' => 'nullable|string|max:20',
            'receiver_relationship' => 'required|string|max:50',
            'handover_notes'        => 'nullable|string|max:500',
            'photo'                 => 'required|image|max:10240', // max 10MB
        ], [
            'actual_receiver_type.required'  => 'Tentukan tipe penerima fisik KTP-el (Pemohon Sendiri atau Kuasa/Perwakilan).',
            'actual_receiver_name.required'  => 'Nama penerima aktual wajib diisi.',
            'actual_receiver_nik.required'   => 'NIK penerima aktual wajib 16 digit angka.',
            'receiver_relationship.required' => 'Hubungan penerima dengan pemilik KTP-el wajib diisi.',
            'photo.required'                 => 'Foto dokumentasi bukti penyerahan langsung wajib diambil menggunakan kamera.',
            'photo.image'                    => 'File bukti penyerahan harus berupa foto valid.',
            'photo.max'                      => 'Ukuran foto maksimal 10MB.',
        ]);

        try {
            $handover = $this->handoverService->completeHandover($id, $validated, $request->file('photo'), Auth::user());
            return redirect()->route('admin.handover.show', $handover->id)
                ->with('success', "Penyerahan fisik KTP-el kepada {$validated['actual_receiver_name']} ({$validated['receiver_relationship']}) berhasil diselesaikan dengan bukti foto.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Securely stream private photo proof.
     */
    public function photo(int $id): StreamedResponse
    {
        $handover = KtpHandover::findOrFail($id);
        return $this->handoverService->servePhoto($handover);
    }
}
