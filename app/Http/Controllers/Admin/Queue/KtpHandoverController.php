<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\KtpHandover;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\ServiceType;
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
     * Display all registered applicants (Pemohon Sudah Registrasi)
     * with full verification, queue, and handover tracking.
     */
    public function registrations(Request $request): Response
    {
        // Auto-purge stale drafts older than configured timeout to keep registration records clean
        app(\App\Services\Queue\QueueApplicationService::class)->purgeExpiredDrafts();

        $search = $request->query('search');
        if ($search && preg_match('/(REG-[A-Za-z0-9-]+)/i', $search, $matches)) {
            $search = strtoupper($matches[1]);
        }

        $serviceId = $request->query('service_id');
        $status = $request->query('status');
        $attendanceType = $request->query('attendance_type');

        $query = QueueApplication::with([
            'serviceType',
            'slot.schedule',
            'handover.handoverUser',
            'documents.serviceDocument',
            'applicantAccount',
        ])->latest('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reg_number', 'like', "%{$search}%")
                  ->orWhere('queue_number', 'like', "%{$search}%")
                  ->orWhere('applicant_name', 'like', "%{$search}%")
                  ->orWhere('applicant_nik', 'like', "%{$search}%")
                  ->orWhere('representative_name', 'like', "%{$search}%")
                  ->orWhere('representative_nik', 'like', "%{$search}%")
                  ->orWhere('family_shdk', 'like', "%{$search}%");
            });
        }

        if ($serviceId) {
            $query->where('service_type_id', $serviceId);
        }

        if ($attendanceType === 'self') {
            $query->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('attendance_type', 'self')->orWhereNull('attendance_type');
                })
                ->whereNull('family_shdk')
                ->where(function ($sq) {
                    $sq->whereNull('representative_name')->orWhere('representative_name', '');
                });
            });
        } elseif ($attendanceType === 'represented') {
            $query->where(function ($q) {
                $q->whereIn('attendance_type', ['family_in_kk', 'family_out_kk', 'other', 'representative', 'PROXY'])
                  ->orWhereNotNull('family_shdk')
                  ->orWhere(function ($sq) {
                      $sq->whereNotNull('representative_name')->where('representative_name', '!=', '');
                  });
            });
        }

        if ($status) {
            if ($status === 'MENUNGGU_VERIFIKASI') {
                $query->whereIn('status', [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED, QueueApplication::STATUS_UNDER_REVIEW]);
            } elseif ($status === 'LOLOS_VERIFIKASI') {
                $query->whereIn('status', [QueueApplication::STATUS_PRE_VERIFIED, QueueApplication::STATUS_QUEUE_BOOKED]);
            } elseif ($status === 'PROSES_LOKET') {
                $query->whereIn('status', [QueueApplication::STATUS_ARRIVED, QueueApplication::STATUS_PHYSICAL_VERIFICATION, QueueApplication::STATUS_PROCESSING]);
            } elseif ($status === 'MENUNGGU_KETERSEDIAAN') {
                $query->where('status', QueueApplication::STATUS_APPROVED_FOR_SERVICE)
                      ->orWhereHas('handover', fn($h) => $h->where('status', KtpHandover::STATUS_PENDING));
            } elseif ($status === 'SIAP_DIAMBIL') {
                $query->where('status', QueueApplication::STATUS_READY_FOR_PICKUP)
                      ->orWhereHas('handover', fn($h) => $h->where('status', KtpHandover::STATUS_READY));
            } elseif ($status === 'SUDAH_DISERAHKAN') {
                $query->where('status', QueueApplication::STATUS_COMPLETED)
                      ->orWhereHas('handover', fn($h) => $h->where('status', KtpHandover::STATUS_HANDED_OVER));
            } elseif ($status === 'KENDALA') {
                $query->whereHas('handover', fn($h) => $h->where('status', KtpHandover::STATUS_EXCEPTION));
            } elseif ($status === 'DITOLAK') {
                $query->whereIn('status', [QueueApplication::STATUS_REJECTED, QueueApplication::STATUS_EXPIRED]);
            } elseif ($status === 'DRAFT') {
                $query->where('status', QueueApplication::STATUS_DRAFT);
            } else {
                $query->where('status', $status);
            }
        }

        $perPage = (int) $request->query('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $applications = $query->paginate($perPage)->withQueryString();

        $services = ServiceType::active()->orderBy('sort_order')->get(['id', 'name']);

        $representedCount = QueueApplication::where(function ($q) {
            $q->whereIn('attendance_type', ['family_in_kk', 'family_out_kk', 'other', 'representative', 'PROXY'])
              ->orWhereNotNull('family_shdk')
              ->orWhere(function ($sq) {
                  $sq->whereNotNull('representative_name')->where('representative_name', '!=', '');
              });
        })->count();
        $totalApps = QueueApplication::count();
        $selfCount = max(0, $totalApps - $representedCount);

        $counts = [
            'total'                 => $totalApps,
            'self'                  => $selfCount,
            'represented'           => $representedCount,
            'draft'                 => QueueApplication::where('status', QueueApplication::STATUS_DRAFT)->count(),
            'menunggu_verifikasi'   => QueueApplication::whereIn('status', [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED, QueueApplication::STATUS_UNDER_REVIEW])->count(),
            'lolos_verifikasi'      => QueueApplication::whereIn('status', [QueueApplication::STATUS_PRE_VERIFIED, QueueApplication::STATUS_QUEUE_BOOKED])->count(),
            'proses_loket'          => QueueApplication::whereIn('status', [QueueApplication::STATUS_ARRIVED, QueueApplication::STATUS_PHYSICAL_VERIFICATION, QueueApplication::STATUS_PROCESSING])->count(),
            'menunggu_ketersediaan' => KtpHandover::pending()->count(),
            'siap_diambil'          => KtpHandover::ready()->count(),
            'sudah_diserahkan'      => KtpHandover::handedOver()->count(),
            'kendala'               => KtpHandover::exception()->count(),
        ];

        return Inertia::render('Admin/Handover/Registrations', [
            'applications'        => $applications,
            'services'            => $services,
            'counts'              => $counts,
            'draftTimeoutMinutes' => (int) \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes', 15),
            'filters'             => [
                'search'          => $search,
                'service_id'      => $serviceId,
                'status'          => $status,
                'attendance_type' => $attendanceType,
                'per_page'        => $perPage,
            ],
        ]);
    }

    /**
     * Display permohonan waiting for physical card verification (MENUNGGU_KETERSEDIAAN).
     */
    public function pending(Request $request): Response
    {
        $search = $request->query('search');
        if ($search && preg_match('/(REG-[A-Za-z0-9-]+)/i', $search, $matches)) {
            $search = strtoupper($matches[1]);
        }

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
        if ($search && preg_match('/(REG-[A-Za-z0-9-]+)/i', $search, $matches)) {
            $search = strtoupper($matches[1]);
        }

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
        if ($search && preg_match('/(REG-[A-Za-z0-9-]+)/i', $search, $matches)) {
            $search = strtoupper($matches[1]);
        }
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
        if ($search && preg_match('/(REG-[A-Za-z0-9-]+)/i', $search, $matches)) {
            $search = strtoupper($matches[1]);
        }

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
     * Dedicated hardware barcode / QR code scanner fast lookup endpoint.
     */
    public function scan(Request $request): RedirectResponse
    {
        $code = trim($request->query('code', $request->query('q', '')));

        if (!$code) {
            return redirect()->route('admin.handover.ready')->with('error', 'Silakan arahkan scanner ke QR Code atau masukkan nomor registrasi.');
        }

        // Sanitize scanned text (e.g. if scanner emitted full URL: https://.../lacak/REG-...)
        if (preg_match('/(REG-[A-Za-z0-9-]+)/i', $code, $matches)) {
            $code = strtoupper($matches[1]);
        }

        // 1. Direct match on KtpHandover
        $handover = KtpHandover::whereHas('application', function ($q) use ($code) {
            $q->where('reg_number', $code);
        })->first();

        if ($handover) {
            return redirect()->route('admin.handover.show', $handover->id)
                ->with('success', "Dokumen [{$code}] berhasil ditemukan melalui scanner QR.");
        }

        // 2. Check if application exists in queue (auto-link handover if in valid state)
        $app = \App\Models\Queue\QueueApplication::where('reg_number', $code)->first();
        if ($app) {
            if (in_array($app->status, [
                \App\Models\Queue\QueueApplication::STATUS_PRE_VERIFIED,
                \App\Models\Queue\QueueApplication::STATUS_QUEUE_BOOKED,
                \App\Models\Queue\QueueApplication::STATUS_ARRIVED,
                \App\Models\Queue\QueueApplication::STATUS_PHYSICAL_VERIFICATION,
                \App\Models\Queue\QueueApplication::STATUS_APPROVED_FOR_SERVICE,
                \App\Models\Queue\QueueApplication::STATUS_PROCESSING,
                \App\Models\Queue\QueueApplication::STATUS_READY_FOR_PICKUP,
                \App\Models\Queue\QueueApplication::STATUS_COMPLETED,
            ])) {
                $handover = KtpHandover::firstOrCreate(
                    ['queue_application_id' => $app->id],
                    ['status' => KtpHandover::STATUS_PENDING]
                );
                return redirect()->route('admin.handover.show', $handover->id)
                    ->with('success', "Dokumen [{$code}] berhasil ditemukan melalui scanner QR.");
            }

            return redirect()->route('admin.queue.applications.show', $app->id)
                ->with('info', "Dokumen [{$code}] ditemukan pada antrean online (status: {$app->status_label}), belum masuk tahap serah terima fisik.");
        }

        return redirect()->route('admin.handover.ready', ['search' => $code])
            ->with('error', "Dokumen dengan nomor registrasi [{$code}] tidak ditemukan.");
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
        $user = Auth::user();
        $allowedRoles = [
            'Super Administrator',
            'Administrator Utama',
            'Administrator Website',
            'Supervisor Layanan KTP-el',
            'Petugas Pengambilan KTP-el',
            'Petugas Verifikasi / Registrasi KTP-el',
            'Petugas Pengarsipan',
        ];

        if (!$user || (!$user->hasAnyRole($allowedRoles) && !$user->can('manage_queue_verification'))) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat foto bukti penyerahan ini.');
        }

        $handover = KtpHandover::findOrFail($id);
        return $this->handoverService->servePhoto($handover);
    }
}
