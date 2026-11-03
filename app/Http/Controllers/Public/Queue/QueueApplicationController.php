<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
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

        if ($serviceSlug) {
            $selectedService = ServiceType::active()
                ->where('slug', $serviceSlug)
                ->with(['documents' => fn($q) => $q->orderBy('sort_order')])
                ->first();
        }

        $services = ServiceType::active()
            ->with(['documents' => fn($q) => $q->orderBy('sort_order')])
            ->get();

        // Ensure Dompu Kecamatan and Desa are seeded
        if (Kecamatan::count() === 0 || Desa::count() === 0) {
            try {
                (new \Database\Seeders\DompuWilayahSeeder())->run();
            } catch (\Throwable $e) {
                \Log::warning('DompuWilayahSeeder auto-seed: ' . $e->getMessage());
            }
        }

        $districts = Kecamatan::with(['desas' => fn($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code'])
            ->map(fn($k) => [
                'id'       => $k->id,
                'name'     => $k->name,
                'code'     => $k->code,
                'villages' => $k->desas->map(fn($d) => [
                    'id'         => $d->id,
                    'name'       => $d->name,
                    'code'       => $d->code,
                    'type'       => $d->type,
                    'type_label' => ucfirst($d->type),
                ])->values(),
            ]);

        return Inertia::render('Public/Queue/Apply/Step1Identity', [
            'services'        => $services,
            'selectedService' => $selectedService,
            'districts'       => $districts,
        ]);
    }

    /**
     * Store Step 1-3 applicant identity & attendance/representation and create draft application.
     */
    public function storeDraft(Request $request): RedirectResponse
    {
        // Sanitize phone numbers by removing non-digit characters (e.g. hyphens, spaces)
        if ($request->has('applicant_phone') && $request->input('applicant_phone') !== null) {
            $cleaned = preg_replace('/[^0-9]/', '', (string) $request->input('applicant_phone'));
            $request->merge(['applicant_phone' => $cleaned]);
        }
        if ($request->has('representative_phone') && $request->input('representative_phone') !== null) {
            $cleaned = preg_replace('/[^0-9]/', '', (string) $request->input('representative_phone'));
            $request->merge(['representative_phone' => $cleaned !== '' ? $cleaned : null]);
        }

        $service = ServiceType::find($request->service_type_id);
        $isBiometric = $service ? (bool) $service->is_biometric_first_recording : false;
        $minAge = $isBiometric ? 16 : 17;
        $maxBirthDate = now()->subYears($minAge)->format('Y-m-d');

        $validated = $request->validate([
            'service_type_id'              => 'required|exists:queue_service_types,id',
            'applicant_name'               => 'required|string|min:3|max:100',
            'applicant_nik'                => ['required', 'string', 'regex:/^[0-9]{16}$/'],
            'applicant_birth_place'        => 'required|string|min:2|max:100',
            'applicant_birth_date'         => [
                'required',
                'date',
                'before_or_equal:' . $maxBirthDate,
                'after:' . now()->subYears(120)->format('Y-m-d'),
            ],
            'applicant_phone'              => [
                'required',
                'string',
                'regex:/^[0-9]{10,13}$/',
            ],
            'applicant_email'              => 'nullable|email|max:100',
            'applicant_address'            => 'required|string|min:3|max:300',
            'applicant_village'            => 'nullable|string|max:100',
            'applicant_district'           => 'nullable|string|max:100',
            'attendance_type'              => 'required|in:self,family_in_kk,family_out_kk,other',
            'representative_nik'           => [
                'nullable',
                'required_if:attendance_type,family_in_kk,family_out_kk,other',
                'regex:/^[0-9]{16}$/',
            ],
            'representative_name'          => 'nullable|required_if:attendance_type,family_in_kk,family_out_kk,other|string|min:3|max:100',
            'representative_phone'         => [
                'nullable',
                'required_if:attendance_type,family_in_kk,family_out_kk,other',
                'regex:/^[0-9]{10,13}$/',
            ],
            'representative_relationship'  => 'nullable|required_if:attendance_type,family_in_kk,family_out_kk,other|string|max:100',
            'representative_kk_status'     => 'nullable|string|max:100',
            'absence_reason'               => 'nullable|required_if:attendance_type,family_in_kk,family_out_kk,other|string|min:5|max:500',
        ], [
            'service_type_id.required'                => 'Pilih salah satu jenis pelayanan KTP-el.',
            'service_type_id.exists'                  => 'Jenis pelayanan yang dipilih tidak valid.',
            'applicant_name.required'                 => 'Nama lengkap pemohon wajib diisi sesuai KTP/KK.',
            'applicant_name.min'                      => 'Nama pemohon minimal 3 karakter.',
            'applicant_nik.required'                  => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'applicant_nik.regex'                     => 'NIK pemilik dokumen harus tepat 16 digit angka (hanya boleh angka, tanpa spasi/huruf).',
            'applicant_birth_place.required'          => 'Tempat lahir pemohon wajib diisi.',
            'applicant_birth_date.required'           => 'Tanggal lahir pemohon wajib diisi.',
            'applicant_birth_date.date'               => 'Format tanggal lahir tidak valid.',
            'applicant_birth_date.before_or_equal'    => $isBiometric
                ? 'Usia pemohon untuk layanan perekaman KTP-el pemula minimal 16 tahun (tanggal lahir maksimal ' . now()->subYears(16)->translatedFormat('d F Y') . ').'
                : 'Usia pemohon untuk layanan pencetakan/penggantian KTP-el minimal 17 tahun (tanggal lahir maksimal ' . now()->subYears(17)->translatedFormat('d F Y') . '). Khusus warga usia 16 tahun, silakan pilih layanan "Perekaman KTP-el Baru".',
            'applicant_birth_date.after'              => 'Tanggal lahir tidak valid (melebihi batas wajar).',
            'applicant_phone.required'                => 'Nomor WhatsApp / HP aktif wajib diisi untuk konfirmasi antrean.',
            'applicant_phone.regex'                   => 'Nomor WhatsApp / HP pemohon harus berupa angka dengan panjang 10 sampai 13 digit.',
            'applicant_email.email'                   => 'Format alamat email tidak valid.',
            'applicant_address.required'              => 'Alamat tempat tinggal lengkap wajib diisi.',
            'applicant_address.min'                   => 'Alamat tempat tinggal minimal 3 karakter.',
            'attendance_type.required'                => 'Pilih status kehadiran: datang sendiri atau diwakilkan.',
            'representative_nik.required_if'          => 'NIK orang yang mewakili wajib diisi (16 digit angka).',
            'representative_nik.regex'                => 'NIK orang yang mewakili harus tepat 16 digit angka (hanya boleh angka, tanpa spasi/huruf).',
            'representative_name.required_if'         => 'Nama orang yang mewakili wajib diisi.',
            'representative_phone.required_if'        => 'Nomor kontak orang yang mewakili wajib diisi.',
            'representative_phone.regex'              => 'Nomor WhatsApp / HP perwakilan harus berupa angka dengan panjang 10 sampai 13 digit.',
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
            // Check if existing application is an expired draft (>24 hours)
            if ($existing->status === QueueApplication::STATUS_DRAFT && $existing->isExpiredDraft(24)) {
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

        $draft = $this->applicationService->createDraft($validated, (int) $validated['service_type_id']);

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
        $application = QueueApplication::where('reg_number', $regNumber)
            ->with([
                'serviceType.documents' => fn($q) => $q->orderBy('sort_order'),
                'documents.serviceDocument',
            ])
            ->first();

        if (!$application) {
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return redirect()->route('queue.apply.create')
                ->with('error', 'Permohonan draft tidak ditemukan atau telah kedaluwarsa dan dihapus otomatis (batas waktu 1x24 jam). Sesi Anda telah ditutup.');
        }

        // Check if draft has expired (older than 24 hours from creation)
        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft(24)) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);

            return redirect()->route('queue.apply.create')
                ->with('error', 'Sesi permohonan draft Anda telah kedaluwarsa karena telah melewati batas waktu 1x24 jam sejak dibuat. Sesi telah ditutup otomatis. Silakan lakukan pendaftaran baru.');
        }

        if (!$application->isEditable()) {
            return redirect()->route('queue.status', ['token' => $application->access_token]);
        }

        // Filter requirements based on attendance type
        $isRepresented = $application->is_represented;
        $filteredRequirements = $application->serviceType->documents->filter(function ($doc) use ($isRepresented) {
            if ($doc->applicable_to === 'representative_only' && !$isRepresented) {
                return false;
            }
            if ($doc->applicable_to === 'self_only' && $isRepresented) {
                return false;
            }
            if (!$isRepresented) {
                $docLabelLower = strtolower($doc->label ?? '');
                if (str_contains($docLabelLower, 'kuasa') || str_contains($docLabelLower, 'f-1.07') || str_contains($docLabelLower, 'f1.07') || str_contains($docLabelLower, 'dikuasakan') || str_contains($docLabelLower, 'diwakilkan')) {
                    return false;
                }
            }
            return true;
        })->values();

        return Inertia::render('Public/Queue/Apply/Step2Documents', [
            'application' => $application,
            'serviceType' => $application->serviceType,
            'requirements' => $filteredRequirements,
            'uploadedDocs' => $application->documents,
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

        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft(24)) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return response()->json(['success' => false, 'session_expired' => true, 'message' => 'Sesi permohonan draft Anda telah kedaluwarsa (1x24 jam). Sesi ditutup otomatis.'], 419);
        }

        $request->validate([
            'service_document_id' => 'required|exists:queue_service_documents,id',
            'file'                => 'required|file|mimes:jpg,jpeg,png,pdf|max:32768',
        ], [
            'service_document_id.required' => 'Persyaratan dokumen harus ditentukan.',
            'file.required'                => 'File atau foto dokumen wajib diunggah.',
            'file.mimes'                   => 'Format file harus berupa JPG, PNG, atau PDF.',
            'file.max'                     => 'Ukuran file melebihi batas sistem (maks 32 MB).',
        ]);

        $uploaded = $this->applicationService->uploadDocument(
            $application,
            (int) $request->input('service_document_id'),
            $request->file('file')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diunggah dan disimpan.',
                'document' => $uploaded->load('serviceDocument'),
            ]);
        }

        return back()->with('success', 'Dokumen berhasil diunggah.');
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

        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft(24)) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return response()->json(['success' => false, 'session_expired' => true, 'message' => 'Sesi permohonan draft Anda telah kedaluwarsa (1x24 jam). Sesi ditutup otomatis.'], 419);
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
        $application = QueueApplication::where('reg_number', $regNumber)
            ->with(['serviceType.documents', 'documents'])
            ->first();

        if (!$application) {
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);
            return redirect()->route('queue.apply.create')
                ->with('error', 'Permohonan draft tidak ditemukan atau telah kedaluwarsa (batas waktu 1x24 jam). Sesi Anda telah ditutup.');
        }

        if ($application->status === QueueApplication::STATUS_DRAFT && $application->isExpiredDraft(24)) {
            $this->applicationService->deleteDraft($application);
            session()->forget(['applicant_verified_nik', 'applicant_verified_at']);

            return redirect()->route('queue.apply.create')
                ->with('error', 'Sesi permohonan draft Anda telah kedaluwarsa karena telah melewati batas waktu 1x24 jam. Sesi telah ditutup otomatis. Silakan lakukan pendaftaran baru.');
        }

        $request->validate([
            'statement_agreed' => 'required|accepted',
        ], [
            'statement_agreed.required' => 'Anda wajib menyetujui pernyataan kebenaran data dan kesediaan membawa dokumen fisik asli.',
            'statement_agreed.accepted' => 'Anda wajib mencentang persetujuan pernyataan pemohon sebelum mengirim berkas.',
        ]);

        $this->applicationService->submit($application, true);

        // Establish verified session for applicant's NIK
        session([
            'applicant_verified_nik' => $application->applicant_nik,
            'applicant_verified_at'  => now()->timestamp,
        ]);

        return redirect()->route('queue.status', ['token' => $application->access_token])
            ->with('success', 'Permohonan Anda berhasil dikirim! Simpan Nomor Registrasi ini untuk memantau hasil pra-verifikasi berkas oleh petugas.');
    }

    /**
     * Serve a draft document for preview (authorized by matching regNumber).
     */
    public function serveDraftDocument(string $regNumber, int $documentId): StreamedResponse
    {
        $application = QueueApplication::where('reg_number', $regNumber)->firstOrFail();
        $doc = ApplicationDocument::where('id', $documentId)
            ->where('application_id', $application->id)
            ->firstOrFail();

        return QueueDocumentStorage::serve($doc, true);
    }
}
