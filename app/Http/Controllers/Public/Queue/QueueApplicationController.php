<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use App\Services\Queue\QueueApplicationService;
use App\Services\Queue\QueueDocumentStorage;
use App\Services\Queue\QueueSlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QueueApplicationController extends Controller
{
    public function __construct(
        protected QueueApplicationService $applicationService,
        protected QueueSlotService $slotService
    ) {}

    /**
     * Show applicant identity & representation form (Steps 1, 2, 3).
     */
    public function create(Request $request): Response
    {
        QueueInfoController::ensureDatabaseSchema();

        // Check if queue service is in temporary maintenance / update mode
        if (in_array((string) \App\Models\Queue\QueueSetting::get('queue.maintenance_mode', '0'), ['1', 'true'], true)) {
            $maintenanceInfo = [
                'title' => \App\Models\Queue\QueueSetting::get('queue.maintenance_title', 'Layanan Pendaftaran Online KTP-el Sedang Dalam Pemeliharaan Sistem'),
                'message' => \App\Models\Queue\QueueSetting::get('queue.maintenance_message', 'Mohon maaf, saat ini pendaftaran dan antrian online KTP-el Dinas Dukcapil Kabupaten Dompu sedang dinonaktifkan sementara untuk pemeliharaan dan pembaruan sistem.'),
                'estimated_end' => \App\Models\Queue\QueueSetting::get('queue.maintenance_estimated_end', ''),
                'office_address' => \App\Services\ConfigurationService::get('office_address') ?: \App\Models\Queue\QueueSetting::get('queue.office_address', 'Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211'),
                'helpdesk_phone' => \App\Services\ConfigurationService::get('whatsapp_number') ?: \App\Models\Queue\QueueSetting::get('queue.helpdesk_phone', '6282124824114'),
            ];

            return Inertia::render('Public/Queue/Maintenance', [
                'maintenance' => $maintenanceInfo,
            ]);
        }

        // Self-heal: ensure mandatory selfie photo exists for Perekaman KTP-el
        \App\Services\Queue\QueueApplicationService::ensureSelfieRequirementForPerekaman();

        // Auto-seed initial queue services & settings if table is empty in production
        if (ServiceType::count() === 0) {
            try {
                (new \Database\Seeders\QueueServiceTypeSeeder())->run();
                (new \Database\Seeders\QueueSettingsSeeder())->run();
                (new \Database\Seeders\QueueScheduleSeeder())->run();
            } catch (\Throwable $e) {
                \Log::warning('Queue auto-seed error: ' . $e->getMessage());
            }
        }

        $serviceSlug = $request->query('layanan');
        $selectedService = null;

        $hasIsActive = \Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active');
        $docFilter = function ($q) use ($hasIsActive) {
            if ($hasIsActive) {
                $q->where('is_active', true);
            }
            $q->orderBy('sort_order');
        };

        if ($serviceSlug) {
            $selectedService = ServiceType::active()
                ->where('slug', $serviceSlug)
                ->with(['documents' => $docFilter])
                ->first();
        }

        $services = ServiceType::active()
            ->with(['documents' => $docFilter])
            ->get();

        return Inertia::render('Public/Queue/Apply/Step1Identity', [
            'services'        => $services,
            'selectedService' => $selectedService,
        ]);
    }

    /**
     * Store Step 1-3 applicant identity & attendance/representation and create draft application.
     */
    public function storeDraft(Request $request): RedirectResponse
    {
        QueueInfoController::ensureDatabaseSchema();

        if (in_array((string) \App\Models\Queue\QueueSetting::get('queue.maintenance_mode', '0'), ['1', 'true'], true)) {
            return redirect()->route('queue.apply.create')->with('error', 'Layanan pendaftaran online sedang dalam pemeliharaan sistem sementara.');
        }

        $applicantAccount = \Illuminate\Support\Facades\Auth::guard('applicant')->user();

        // Auto-populate applicant email from logged-in applicant account if empty or not provided in form
        if (!$request->filled('applicant_email') && $applicantAccount && !empty($applicantAccount->email)) {
            $request->merge(['applicant_email' => strtolower(trim($applicantAccount->email))]);
        } elseif ($request->has('applicant_email') && $request->input('applicant_email') !== null) {
            $email = trim((string) $request->input('applicant_email'));
            if ($email !== '') {
                $email = preg_replace('/\s+/', '', $email);
                if (!str_contains($email, '@')) {
                    $email .= '@gmail.com';
                } elseif (str_ends_with($email, '@')) {
                    $email .= 'gmail.com';
                }
                $request->merge(['applicant_email' => strtolower($email)]);
            } else {
                $request->merge(['applicant_email' => null]);
            }
        }

        // Sanitize demographic text fields to uppercase
        foreach (['applicant_name', 'representative_name'] as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                $request->merge([$field => mb_strtoupper(trim((string) $request->input($field)))]);
            }
        }

        $service = ServiceType::find($request->service_type_id);
        $isBiometric = $service ? (bool) $service->is_biometric_first_recording : false;

        // Auto-configure representative data if applicant represents a family member in 1 KK
        $target = $request->input('application_target', 'self');
        if ($target === 'family_in_kk' || $request->filled('family_shdk')) {
            if ($isBiometric) {
                return back()->withErrors([
                    'application_target' => 'Pelayanan perekaman KTP-el baru wajib diajukan dan dihadiri langsung oleh pemilik dokumen (tidak dapat diwakilkan).',
                ])->withInput();
            }

            $request->merge(['attendance_type' => 'family_in_kk']);
            if ($applicantAccount) {
                $request->merge([
                    'representative_nik'  => $applicantAccount->nik,
                    'representative_name' => $applicantAccount->name,
                ]);
            }
            if ($request->filled('family_shdk')) {
                $request->merge(['representative_relationship' => $request->input('family_shdk')]);
            }
            $request->merge(['representative_kk_status' => 'same_kk']);
            $shdkSuffix = $request->filled('family_shdk') ? ' - ' . $request->input('family_shdk') : '';
            $request->merge(['absence_reason' => 'Mewakili pengurusan dokumen KTP-el anggota keluarga dalam 1 Kartu Keluarga (KK)' . $shdkSuffix]);
        } else {
            if ($applicantAccount) {
                $request->merge([
                    'applicant_nik'  => $applicantAccount->nik,
                    'applicant_name' => $applicantAccount->name,
                ]);
            }
            $request->merge([
                'attendance_type'             => 'self',
                'representative_nik'          => null,
                'representative_name'         => null,
                'representative_relationship' => null,
                'representative_kk_status'    => null,
                'absence_reason'              => null,
                'family_shdk'                 => null,
            ]);
        }

        $validated = $request->validate([
            'service_type_id'              => 'required|exists:queue_service_types,id',
            'application_target'           => 'nullable|in:self,family_in_kk',
            'family_shdk'                  => [
                'nullable',
                'required_if:application_target,family_in_kk',
                'string',
                'max:50',
            ],
            'applicant_name'               => [
                'required',
                'string',
                'min:2',
                'max:60',
                'regex:/^[A-Za-z\s\'\-]+$/',
            ],
            'applicant_nik'                => ['required', 'string', 'regex:/^[0-9]{16}$/'],
            'applicant_email'              => 'nullable|email|max:100',
            'attendance_type'              => 'required|in:self,family_in_kk,family_out_kk,other',
            'representative_nik'           => [
                'nullable',
                'required_if:attendance_type,family_in_kk,family_out_kk,other',
                'regex:/^[0-9]{16}$/',
            ],
            'representative_name'          => [
                'nullable',
                'required_if:attendance_type,family_in_kk,family_out_kk,other',
                'string',
                'min:2',
                'max:60',
                'regex:/^[A-Za-z\s\'\-]+$/',
            ],
            'representative_relationship'  => 'nullable|required_if:attendance_type,family_in_kk,family_out_kk,other|string|max:100',
            'representative_kk_status'     => 'nullable|string|max:100',
            'absence_reason'               => 'nullable|required_if:attendance_type,family_in_kk,family_out_kk,other|string|min:5|max:500',
        ], [
            'service_type_id.required'                => 'Pilih salah satu jenis pelayanan KTP-el.',
            'service_type_id.exists'                  => 'Jenis pelayanan yang dipilih tidak valid.',
            'family_shdk.required_if'                 => 'Status Hubungan Dalam Keluarga (SHDK) wajib dipilih jika mewakili anggota keluarga 1 KK.',
            'applicant_name.required'                 => 'Nama lengkap pemohon wajib diisi sesuai KTP/KK.',
            'applicant_name.regex'                    => 'Kaidah penulisan nama: Nama hanya boleh memuat huruf kapital, spasi, tanda petik (\'), atau tanda hubung (-) tanpa angka (0-9) atau simbol sesuai Permendagri No. 73/2022.',
            'applicant_name.min'                      => 'Nama pemohon minimal 2 huruf.',
            'applicant_name.max'                      => 'Nama pemohon maksimal 60 huruf sesuai Permendagri No. 73 Tahun 2022.',
            'applicant_nik.required'                  => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'applicant_nik.regex'                     => 'NIK pemilik dokumen harus tepat 16 digit angka (hanya boleh angka, tanpa spasi/huruf).',
            'applicant_email.email'                   => 'Format alamat email tidak valid.',
            'attendance_type.required'                => 'Pilih status kehadiran: datang sendiri atau diwakilkan.',
            'representative_nik.required_if'          => 'NIK orang yang mewakili wajib diisi (16 digit angka).',
            'representative_nik.regex'                => 'NIK orang yang mewakili harus tepat 16 digit angka (hanya boleh angka, tanpa spasi/huruf).',
            'representative_name.required_if'         => 'Nama orang yang mewakili wajib diisi.',
            'representative_name.regex'               => 'Kaidah penulisan nama: Nama orang yang mewakili hanya boleh memuat huruf kapital, spasi, tanda petik (\'), atau tanda hubung (-) tanpa angka (0-9) atau simbol sesuai Permendagri No. 73/2022.',
            'representative_name.min'                 => 'Nama orang yang mewakili minimal 2 huruf.',
            'representative_name.max'                 => 'Nama orang yang mewakili maksimal 60 huruf sesuai Permendagri No. 73 Tahun 2022.',
            'representative_relationship.required_if' => 'Hubungan orang yang mewakili dengan pemilik dokumen wajib dipilih.',
            'absence_reason.required_if'              => 'Alasan pemilik dokumen tidak dapat hadir langsung wajib dijelaskan.',
        ]);

        if ($isBiometric && $validated['attendance_type'] !== 'self') {
            return back()->withErrors([
                'attendance_type' => 'Pelayanan perekaman KTP-el baru wajib dihadiri langsung oleh pemohon (tidak dapat diwakilkan).',
            ])->withInput();
        }

        // Check if there is already an active (uncompleted) application for this NIK and service
        $existing = QueueApplication::where('applicant_nik', $validated['applicant_nik'])
            ->where('service_type_id', $validated['service_type_id'])
            ->whereIn('status', [
                QueueApplication::STATUS_DRAFT,
                QueueApplication::STATUS_SUBMITTED,
                QueueApplication::STATUS_UNDER_REVIEW,
                QueueApplication::STATUS_REVISION_REQUIRED,
                QueueApplication::STATUS_RESUBMITTED,
                QueueApplication::STATUS_PRE_VERIFIED,
                QueueApplication::STATUS_QUEUE_BOOKED,
                QueueApplication::STATUS_ARRIVED,
                QueueApplication::STATUS_PHYSICAL_VERIFICATION,
                QueueApplication::STATUS_APPROVED_FOR_SERVICE,
                QueueApplication::STATUS_PROCESSING,
                QueueApplication::STATUS_READY_FOR_PICKUP,
            ])
            ->first();

        if ($existing) {
            // Check if existing application is an expired draft
            if ($existing->status === QueueApplication::STATUS_DRAFT && $existing->isExpiredDraft()) {
                $this->applicationService->deleteDraft($existing);
                $existing = null;
            }
        }

        if ($existing) {
            // Register verified session for applicant's NIK
            session([
                'applicant_verified_nik' => $existing->applicant_nik,
                'applicant_verified_at'  => now()->timestamp,
            ]);

            // If already drafted, resume it directly
            if ($existing->status === QueueApplication::STATUS_DRAFT) {
                return redirect()->route('queue.apply.documents', ['regNumber' => $existing->reg_number]);
            }
            // If already submitted/active, redirect to status tracking
            return redirect()->route('queue.status', ['token' => $existing->access_token])
                ->with('info', 'Anda telah memiliki permohonan aktif untuk jenis layanan ini.');
        }

        try {
            $draft = $this->applicationService->createDraft($validated, (int) $validated['service_type_id']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Queue storeDraft error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors([
                'service_type_id' => 'Terjadi kendala sistem saat memproses draf permohonan (' . $e->getMessage() . '). Silakan coba kembali.',
            ])->withInput();
        }

        // Establish verified session for applicant's NIK
        session([
            'applicant_verified_nik' => $draft->applicant_nik,
            'applicant_verified_at'  => now()->timestamp,
        ]);

        return redirect()->route('queue.apply.documents', ['regNumber' => $draft->reg_number]);
    }

    /**
     * Show document capture / upload wizard (Step 4 & 5).
     */
    public function showDocuments(string $regNumber): Response|RedirectResponse
    {
        $hasIsActive = \Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active');
        $docFilter = function ($q) use ($hasIsActive) {
            if ($hasIsActive) {
                $q->where('is_active', true);
            }
            $q->orderBy('sort_order');
        };

        $application = QueueApplication::where('reg_number', $regNumber)
            ->with([
                'serviceType.documents' => $docFilter,
                'documents.serviceDocument',
            ])
            ->first();

        $timeoutMinutes = (int) \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes', 15);

        if (!$application) {
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return redirect()->route('queue.apply.create')
                ->with('error', "Permohonan draf tidak ditemukan atau telah kedaluwarsa dan dibersihkan otomatis (batas waktu {$timeoutMinutes} menit). Sesi Anda telah ditutup.");
        }

        // Check if draft has expired based on configured waiting time
        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft()) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);

            return redirect()->route('queue.apply.create')
                ->with('error', "Sesi permohonan draf Anda telah kedaluwarsa karena telah melewati batas waktu tunggu {$timeoutMinutes} menit sejak dibuat. Silakan lakukan pendaftaran baru.");
        }

        $isOwnerAccount = \Illuminate\Support\Facades\Auth::guard('applicant')->check() &&
            $application->applicant_account_id === \Illuminate\Support\Facades\Auth::guard('applicant')->id();

        // Verify applicant session identity to prevent unauthorized BOLA/IDOR access
        if (session('applicant_verified_nik') !== $application->applicant_nik && !\Illuminate\Support\Facades\Auth::check() && !$isOwnerAccount) {
            return redirect()->route('queue.apply.create')
                ->with('error', 'Sesi pendaftaran tidak valid atau telah berakhir. Silakan masukkan NIK Anda kembali.');
        }

        if (!$application->isEditable()) {
            return redirect()->route('queue.status', ['token' => $application->access_token]);
        }

        // Filter requirements based on attendance type using database applicable_to field
        $isRepresented = $application->is_represented;
        $filteredRequirements = $application->serviceType->documents->filter(function ($doc) use ($isRepresented) {
            if ($doc->applicable_to === 'representative_only' && !$isRepresented) {
                return false;
            }
            if ($doc->applicable_to === 'self_only' && $isRepresented) {
                return false;
            }
            return true;
        })->values();

        $createdAt = $application->created_at ?: now();
        $expiresAt = $createdAt->copy()->addMinutes($timeoutMinutes);
        $remainingSeconds = max(0, (int) now()->diffInSeconds($expiresAt, false));

        return Inertia::render('Public/Queue/Apply/Step2Documents', [
            'application'           => $application,
            'serviceType'           => $application->serviceType,
            'requirements'          => $filteredRequirements,
            'uploadedDocs'          => $application->documents,
            'draftTimeoutMinutes'   => $timeoutMinutes,
            'draftRemainingSeconds' => $remainingSeconds,
            'draftExpiresAt'        => $expiresAt->toISOString(),
        ]);
    }

    /**
     * Upload a document via AJAX or form POST (Step 4).
     */
    public function uploadDocument(Request $request, string $regNumber): JsonResponse|RedirectResponse
    {
        $application = QueueApplication::where('reg_number', $regNumber)->first();

        if (!$application) {
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return response()->json(['success' => false, 'session_expired' => true, 'message' => 'Sesi permohonan draft telah kedaluwarsa atau tidak ditemukan.'], 404);
        }

        $timeoutMinutes = (int) \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes', 15);

        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft()) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return response()->json(['success' => false, 'session_expired' => true, 'message' => "Sesi permohonan draf Anda telah kedaluwarsa ({$timeoutMinutes} menit). Sesi ditutup otomatis."], 419);
        }

        // Verify session identity
        if (session('applicant_verified_nik') !== $application->applicant_nik && !\Illuminate\Support\Facades\Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid untuk permohonan ini.'], 403);
        }

        try {
            \Log::info("Queue doc upload attempt [{$regNumber}] doc_id={$request->input('service_document_id')}");

            $request->validate([
                'service_document_id' => 'required|exists:queue_service_documents,id',
                'file'                => 'required|file|max:32768',
            ], [
                'service_document_id.required' => 'Persyaratan dokumen harus ditentukan.',
                'file.required'                => 'File atau foto dokumen wajib diunggah.',
                'file.max'                     => 'Ukuran file melebihi batas sistem (maks 32 MB).',
            ]);

            $uploaded = $this->applicationService->uploadDocument(
                $application,
                (int) $request->input('service_document_id'),
                $request->file('file')
            );

            \Log::info("Queue doc upload success [{$regNumber}] doc_id={$request->input('service_document_id')} app_doc_id={$uploaded->id}");

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diunggah dan disimpan.',
                'document' => $uploaded->load('serviceDocument'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            \Log::warning("Queue doc upload validation error [{$regNumber}]: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => implode(' ', $errors),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error("Upload document failed [{$regNumber}]: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah berkas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove an uploaded document from draft.
     */
    public function removeDocument(Request $request, string $regNumber, int $documentId): JsonResponse|RedirectResponse
    {
        $application = QueueApplication::where('reg_number', $regNumber)->first();

        if (!$application) {
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return response()->json(['success' => false, 'session_expired' => true, 'message' => 'Sesi permohonan draft telah kedaluwarsa atau tidak ditemukan.'], 404);
        }

        $timeoutMinutes = (int) \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes', 15);

        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft()) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return response()->json(['success' => false, 'session_expired' => true, 'message' => "Sesi permohonan draf Anda telah kedaluwarsa ({$timeoutMinutes} menit). Sesi ditutup otomatis."], 419);
        }

        // Verify session identity
        if (session('applicant_verified_nik') !== $application->applicant_nik && !\Illuminate\Support\Facades\Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid untuk permohonan ini.'], 403);
        }

        $this->applicationService->removeDocument($application, $documentId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Complete wizard submission with statement agreement and forward for pre-verification.
     */
    public function submit(Request $request, string $regNumber): RedirectResponse
    {
        if (in_array((string) \App\Models\Queue\QueueSetting::get('queue.maintenance_mode', '0'), ['1', 'true'], true)) {
            return redirect()->route('queue.apply.create')
                ->with('error', 'Layanan pendaftaran online sedang dalam pemeliharaan sistem sementara.');
        }

        \App\Services\Queue\QueueApplicationService::ensureSelfieRequirementForPerekaman();

        $application = QueueApplication::where('reg_number', $regNumber)
            ->with(['serviceType.documents', 'documents'])
            ->first();

        $timeoutMinutes = (int) \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes', 15);

        if (!$application) {
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return redirect()->route('queue.apply.create')
                ->with('error', "Permohonan draf tidak ditemukan atau telah kedaluwarsa (batas waktu {$timeoutMinutes} menit). Sesi Anda telah ditutup.");
        }

        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft()) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);

            return redirect()->route('queue.apply.create')
                ->with('error', "Sesi permohonan draf Anda telah kedaluwarsa karena telah melewati batas waktu {$timeoutMinutes} menit. Sesi telah ditutup otomatis. Silakan lakukan pendaftaran baru.");
        }

        // Verify session identity
        if (session('applicant_verified_nik') !== $application->applicant_nik && !\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('queue.apply.create')
                ->with('error', 'Sesi pendaftaran tidak valid atau telah berakhir.');
        }

        $request->validate([
            'statement_agreed' => 'required|accepted',
        ], [
            'statement_agreed.required' => 'Anda wajib menyetujui pernyataan kebenaran data dan kesediaan membawa dokumen fisik asli.',
            'statement_agreed.accepted' => 'Anda wajib mencentang persetujuan pernyataan pemohon sebelum mengirim berkas.',
        ]);

        try {
            $application = $this->applicationService->submit($application, true);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            \Log::error("Submit queue application failed [{$regNumber}]: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            return redirect()->back()->with('error', 'Gagal memproses permohonan pendaftaran: ' . $e->getMessage());
        }

        // Establish verified session for applicant's NIK
        session([
            'applicant_verified_nik' => $application->applicant_nik,
            'applicant_verified_at'  => now()->timestamp,
        ]);

        $queueInfo = $application->queue_number ? " Nomor antrean Anda telah terbit: {$application->queue_number}." : "";

        return redirect()->route('queue.status', ['token' => $application->access_token])
            ->with('success', "Permohonan berkas Anda berhasil dikirim!{$queueInfo} Simpan bukti antrean ini untuk kedatangan Anda ke kantor.");
    }

    /**
     * Serve a draft document for preview (strictly authorized to matching verified session or authorized officer).
     */
    public function serveDraftDocument(Request $request, string $regNumber, int $documentId): StreamedResponse
    {
        $application = QueueApplication::where('reg_number', $regNumber)->firstOrFail();

        $sessionNik = session('applicant_verified_nik');
        $isAuthorizedApplicant = $sessionNik && $sessionNik === $application->applicant_nik;

        $isAuthorizedOfficer = \Illuminate\Support\Facades\Auth::check() && (
            \Illuminate\Support\Facades\Auth::user()->hasAnyRole([
                'Super Administrator', 'Administrator Utama',
                'Website Administrator', 'Administrator Website',
                'Queue Officer', 'Petugas Antrean'
            ])
            || \Illuminate\Support\Facades\Auth::user()->can('manage_queue_verification')
        );

        if (!$isAuthorizedApplicant && !$isAuthorizedOfficer) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat dokumen ini.');
        }

        $doc = ApplicationDocument::where('id', $documentId)
            ->where('application_id', $application->id)
            ->firstOrFail();

        if ($doc->is_destroyed || empty($doc->getRawOriginal('path'))) {
            abort(410, 'Dokumen ini telah dimusnahkan pasca verifikasi demi perlindungan privasi.');
        }

        if ($doc->isSelfie() && !$isAuthorizedOfficer) {
            abort(403, 'Akses ditolak. Foto selfie pemohon hanya dapat diperiksa oleh petugas pemeriksa berwenang.');
        }

        return QueueDocumentStorage::serve($doc, true);
    }
}
