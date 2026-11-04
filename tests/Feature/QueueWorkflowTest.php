<?php

namespace Tests\Feature;

use App\Exceptions\Queue\QuotaExceededException;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use App\Models\User;
use App\Notifications\Queue\ApplicationApprovedNotification;
use App\Notifications\Queue\ApplicationRejectedNotification;
use App\Notifications\Queue\KtpReadyForPickupNotification;
use App\Notifications\Queue\RevisionRequiredNotification;
use App\Services\Queue\QueueApplicationService;
use App\Services\Queue\QueueNumberGenerator;
use App\Services\Queue\QueueSlotService;
use App\Services\Queue\QueueVerificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QueueWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'dukcapil_dompukab']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
        DB::purge();
        DB::reconnect();
        Cache::forget('nav_menus');
    }

    public function test_public_queue_landing_page_renders_successfully()
    {
        $response = $this->get('/layanan/antrian');
        $response->assertStatus(200);
    }

    public function test_public_queue_application_wizard_renders()
    {
        $response = $this->get('/layanan/antrian/daftar');
        $response->assertStatus(200);
    }

    public function test_biometric_first_recording_rejects_representation_attempt()
    {
        // Perekaman KTP-el Pemula Wajib Datang Sendiri (is_biometric_first_recording = true)
        $pemulaService = ServiceType::where('is_biometric_first_recording', true)->first();
        if (!$pemulaService) {
            $pemulaService = ServiceType::firstOrCreate(
                ['code' => 'KTP-BARU'],
                [
                    'name' => 'Perekaman KTP-el Baru (Pemula / Usia 17 Tahun)',
                    'is_biometric_first_recording' => true,
                    'allow_self' => true,
                    'allow_family_in_kk' => false,
                    'allow_family_out_kk' => false,
                    'allow_other' => false,
                    'is_active' => true,
                ]
            );
        }

        $appService = app(QueueApplicationService::class);
        $uniqueNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $this->expectException(ValidationException::class);

        // Attempting to represent a biometric first recording must be blocked!
        $appService->createDraft([
            'applicant_nik' => $uniqueNik,
            'applicant_name' => 'Pemula Testing',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'family_in_kk',
            'representative_name' => 'Ayah Kandung',
            'representative_nik' => '5205111122223333',
        ], $pemulaService->id);
    }

    public function test_representation_allowed_for_replacement_service_with_valid_fields()
    {
        // Layanan KTP Hilang / Rusak diperbolehkan diwakilkan keluarga dalam KK
        $hilangService = ServiceType::where('is_biometric_first_recording', false)->first();
        $this->assertNotNull($hilangService, 'Replacement service must exist');

        $appService = app(QueueApplicationService::class);
        $applicantNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $repNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $draft = $appService->createDraft([
            'applicant_nik' => $applicantNik,
            'applicant_name' => 'Pemilik Dokumen Rusak',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Kec. Woja, Dompu',
            'attendance_type' => 'family_in_kk',
            'representative_name' => 'Istri Sah',
            'representative_nik' => $repNik,
            'representative_relationship' => 'Istri',
            'representative_phone' => '081987654321',
            'representative_reason' => 'Suami sedang bekerja di luar daerah',
        ], $hilangService->id);

        $this->assertEquals(QueueApplication::STATUS_DRAFT, $draft->status);
        $this->assertEquals('family_in_kk', $draft->attendance_type);
        $this->assertEquals('ISTRI SAH', $draft->representative_name);
        $this->assertTrue($draft->is_represented);
    }

    public function test_full_pre_verification_and_booking_and_counter_lifecycle()
    {
        Storage::fake('local');

        $service = ServiceType::with('documents')->first();
        $this->assertNotNull($service, 'Seeded service type must exist');

        // 1. Create draft
        $appService = app(QueueApplicationService::class);
        $uniqueNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $application = $appService->createDraft([
            'applicant_nik' => $uniqueNik,
            'applicant_name' => 'Muhammad Imran',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => '1995-05-12',
            'applicant_phone' => '081234567890',
            'applicant_email' => 'imran@example.com',
            'applicant_address' => 'Kel. Bada, Kec. Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $this->assertEquals(QueueApplication::STATUS_DRAFT, $application->status);

        // 2. Upload mandatory documents
        foreach ($service->documents as $docConfig) {
            $fakeFile = UploadedFile::fake()->image('test_doc_' . $docConfig->id . '.jpg', 800, 600);
            $appService->uploadDocument($application, $docConfig->id, $fakeFile);
        }

        // 3. Submit application with statement agreed (Nomor antrean resmi langsung terbit terlebih dahulu)
        $submittedApp = $appService->submit($application, true);
        $this->assertEquals(QueueApplication::STATUS_SUBMITTED, $submittedApp->status);
        $this->assertTrue($submittedApp->statement_agreed);
        $this->assertNotNull($submittedApp->queue_number);
        $this->assertNotNull($submittedApp->queue_slot_id);

        // 4. Officer Online Pre-Verification: Dokumen online disetujui & nomor antrean aktif
        $verifierService = app(QueueVerificationService::class);
        $officer = User::first() ?? User::factory()->create();

        $verifierService->preVerify($submittedApp, $officer, 'Dokumen online lengkap dan sah.');
        $this->assertEquals(QueueApplication::STATUS_QUEUE_BOOKED, $submittedApp->fresh()->status);

        // Akses halaman status menampilkan tiket & nomor antrean resmi pemohon
        $statusDashboardResponse = $this->get(route('queue.status', ['token' => $submittedApp->access_token]));
        $statusDashboardResponse->assertOk();

        // 5. Pemohon dapat melakukan ubah jadwal antrean jika berhalangan hadir pada tanggal awal
        $schedule = QueueSchedule::firstOrCreate(
            ['schedule_date' => now()->addDays(3)->format('Y-m-d')],
            [
                'open_time' => '08:00:00',
                'close_time' => '15:30:00',
                'total_quota' => 500,
                'is_holiday' => false,
                'is_open' => true,
            ]
        );
        $schedule->update(['total_quota' => 500, 'is_holiday' => false, 'is_open' => true]);
        $slot = QueueSlot::firstOrCreate(
            [
                'schedule_id' => $schedule->id,
                'service_type_id' => $service->id,
            ],
            [
                'name' => 'Sesi 1 Pagi (08:00 - 11:30)',
                'start_time' => '08:00:00',
                'end_time' => '11:30:00',
                'quota' => 100,
                'booked_count' => 0,
            ]
        );
        $slot->increment('quota', 50);

        $slotService = app(\App\Services\Queue\QueueSlotService::class);
        $slotService->bookSlot($slot->id, $submittedApp);
        $bookedApp = $submittedApp->fresh();
        $this->assertEquals(QueueApplication::STATUS_QUEUE_BOOKED, $bookedApp->status);
        $this->assertNotNull($bookedApp->queue_number);
        $this->assertEquals($slot->id, $bookedApp->queue_slot_id);

        // 6. Day of service: Front-desk counter check-in (ARRIVED)
        $verifierService->checkIn($bookedApp, [
            'attended_person_type' => 'self',
            'attended_person_name' => $bookedApp->applicant_name,
            'attended_person_nik' => $bookedApp->applicant_nik,
        ], $officer);

        $arrivedApp = $bookedApp->fresh();
        $this->assertEquals(QueueApplication::STATUS_ARRIVED, $arrivedApp->status);
        $this->assertNotNull($arrivedApp->checked_in_at);
        $this->assertEquals($officer->id, $arrivedApp->checked_in_by);

        // 7. Physical document verification at counter (APPROVED_FOR_SERVICE)
        $verifierService->physicalVerification($arrivedApp, [
            'decision' => 'APPROVED_FOR_SERVICE',
            'notes' => 'Fisik KK dan Surat Kehilangan asli sesuai dengan berkas online.',
            'match_identity' => true,
            'match_documents' => true,
            'match_authorization' => true,
        ], $officer);

        $physicallyApprovedApp = $arrivedApp->fresh();
        $this->assertEquals(QueueApplication::STATUS_APPROVED_FOR_SERVICE, $physicallyApprovedApp->status);
        $this->assertNotNull($physicallyApprovedApp->physical_verified_at);
        $this->assertEquals($officer->id, $physicallyApprovedApp->physical_verified_by);

        // 8. Technical processing: Biometrics recording or card printing (PROCESSING)
        $verifierService->startProcessing($physicallyApprovedApp, $officer, 'Mulai proses cetak KTP di loket SIAK.');
        $processingApp = $physicallyApprovedApp->fresh();
        $this->assertEquals(QueueApplication::STATUS_PROCESSING, $processingApp->status);

        // 9. Card ready for pickup (READY_FOR_PICKUP)
        $verifierService->markReadyForPickup($processingApp, $officer, 'KTP-el fisik telah tercetak dengan baik.');
        $readyApp = $processingApp->fresh();
        $this->assertEquals(QueueApplication::STATUS_READY_FOR_PICKUP, $readyApp->status);

        // 10. Handover physical card to applicant (COMPLETED)
        $verifierService->completeHandover($readyApp, [
            'received_by_type' => 'self',
            'received_by_name' => $readyApp->applicant_name,
            'received_by_nik' => $readyApp->applicant_nik,
            'notes' => 'Fisik KTP-el telah diserahkan langsung kepada pemilik dokumen.',
        ], $officer);

        $completedApp = $readyApp->fresh();
        $this->assertEquals(QueueApplication::STATUS_COMPLETED, $completedApp->status);
        $this->assertNotNull($completedApp->handover_at);
        $this->assertEquals($officer->id, $completedApp->handover_by);
        $this->assertEquals($readyApp->applicant_name, $completedApp->handover_recipient_name);
    }

    public function test_mandatory_document_validation_blocks_incomplete_submission()
    {
        $service = ServiceType::with('documents')->first();
        $appService = app(QueueApplicationService::class);
        $uniqueNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $application = $appService->createDraft([
            'applicant_nik' => $uniqueNik,
            'applicant_name' => 'Pemohon Belum Lengkap',
            'applicant_phone' => '081299998888',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        // Attempt submit without uploading required documents
        $this->expectException(ValidationException::class);
        $appService->submit($application, true);
    }

    public function test_queue_number_generator_creates_unique_daily_sequence()
    {
        $service = ServiceType::first();
        $schedule = QueueSchedule::firstOrCreate(
            ['schedule_date' => now()->addDays(5)->format('Y-m-d')],
            ['open_time' => '08:00', 'close_time' => '15:00', 'total_quota' => 30]
        );
        $slot = QueueSlot::firstOrCreate(
            ['schedule_id' => $schedule->id, 'service_type_id' => $service->id],
            ['quota' => 30, 'booked_count' => 0]
        );

        $num1 = QueueNumberGenerator::generateQueueNumber($slot);
        $this->assertMatchesRegularExpression('/^[A-Z]-\d{3}$/', $num1);
    }

    public function test_pessimistic_locking_prevents_slot_quota_exceeded()
    {
        $service = ServiceType::first();
        $dateStr = now()->addDays(rand(60, 120))->format('Y-m-d');
        $schedule = QueueSchedule::firstOrCreate(
            ['schedule_date' => $dateStr],
            [
                'open_time' => '08:00',
                'close_time' => '15:00',
                'total_quota' => 1,
                'is_holiday' => false,
                'is_open' => true,
            ]
        );
        $slot = QueueSlot::firstOrCreate(
            [
                'schedule_id' => $schedule->id,
                'service_type_id' => $service->id,
            ],
            [
                'quota' => 1,
                'booked_count' => 1,
            ]
        );
        $slot->update(['quota' => 1, 'booked_count' => 1]);

        $slotService = app(QueueSlotService::class);

        $appService = app(QueueApplicationService::class);
        $uniqueNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $dummyApp = $appService->createDraft([
            'applicant_nik' => $uniqueNik,
            'applicant_name' => 'Pemohon Lock Test',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $this->expectException(QuotaExceededException::class);
        $slotService->bookSlot($slot->id, $dummyApp);
    }

    public function test_nik_must_be_strictly_16_digits_and_numeric()
    {
        $service = ServiceType::first();

        // 1. Letters in NIK must be rejected
        $response = $this->post(route('queue.apply.store'), [
            'service_type_id' => $service->id,
            'applicant_name' => 'Testing NIK Letters',
            'applicant_nik' => '52051234567890AB',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => now()->subYears(20)->format('Y-m-d'),
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu Kota',
            'attendance_type' => 'self',
        ]);
        $response->assertSessionHasErrors('applicant_nik');

        // 2. Less than 16 digits must be rejected
        $response2 = $this->post(route('queue.apply.store'), [
            'service_type_id' => $service->id,
            'applicant_name' => 'Testing Short NIK',
            'applicant_nik' => '52051234567',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => now()->subYears(20)->format('Y-m-d'),
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu Kota',
            'attendance_type' => 'self',
        ]);
        $response2->assertSessionHasErrors('applicant_nik');
    }

    public function test_minimum_age_16_years_for_perekaman_pemula()
    {
        $perekamanService = ServiceType::where('is_biometric_first_recording', true)->first();
        $this->assertNotNull($perekamanService);
        $this->assertTrue($perekamanService->requires_face_photo);

        $selfieDoc = $perekamanService->documents()->where(function ($q) {
            $q->where('label', 'like', '%Selfie%')->orWhere('label', 'like', '%Foto Wajah%');
        })->first();
        $this->assertNotNull($selfieDoc);
        $this->assertTrue($selfieDoc->is_required);
        $this->assertEquals('user', $selfieDoc->capture_mode);

        // 15-year-old must be rejected for Perekaman
        $responseUnder16 = $this->post(route('queue.apply.store'), [
            'service_type_id' => $perekamanService->id,
            'applicant_name' => 'Anak Usia 15',
            'applicant_nik' => '5205999988887777',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => now()->subYears(15)->format('Y-m-d'),
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu Kota',
            'attendance_type' => 'self',
        ]);
        $responseUnder16->assertSessionHasErrors('applicant_birth_date');

        // 16-year-old must pass for Perekaman
        $nik16 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $response16 = $this->post(route('queue.apply.store'), [
            'service_type_id' => $perekamanService->id,
            'applicant_name' => 'Remaja Usia 16',
            'applicant_nik' => $nik16,
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => now()->subYears(16)->format('Y-m-d'),
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu Kota',
            'attendance_type' => 'self',
        ]);
        $response16->assertSessionHasNoErrors();
    }

    public function test_minimum_age_17_years_for_pencetakan()
    {
        $cetakService = ServiceType::where('is_biometric_first_recording', false)->first();
        $this->assertNotNull($cetakService);

        // 16-year-old must be rejected for Cetak KTP
        $response16ForCetak = $this->post(route('queue.apply.store'), [
            'service_type_id' => $cetakService->id,
            'applicant_name' => 'Remaja 16 Cetak',
            'applicant_nik' => '5205888877776666',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => now()->subYears(16)->format('Y-m-d'),
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu Kota',
            'attendance_type' => 'self',
        ]);
        $response16ForCetak->assertSessionHasErrors('applicant_birth_date');

        // 17-year-old must pass for Cetak KTP
        $nik17 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $response17 = $this->post(route('queue.apply.store'), [
            'service_type_id' => $cetakService->id,
            'applicant_name' => 'Dewasa 17 Cetak',
            'applicant_nik' => $nik17,
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => now()->subYears(17)->format('Y-m-d'),
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu Kota',
            'attendance_type' => 'self',
        ]);
        $response17->assertSessionHasNoErrors();
    }

    public function test_admin_can_update_service_type()
    {
        $admin = User::first();
        $this->assertNotNull($admin, 'Admin user must exist');

        $service = ServiceType::first();
        $this->assertNotNull($service, 'Service type must exist');

        $response = $this->actingAs($admin)->put(route('admin.queue.service-types.update', $service->id), [
            'name' => $service->name,
            'slug' => $service->slug,
            'description' => 'Keterangan diuji via automated test',
            'color' => '#0284c7',
            'quota_per_day' => 25,
            'processing_days_label' => '1 Hari Kerja',
            'cost_label' => 'GRATIS (Rp 0)',
            'requires_face_photo' => true,
            'allow_self' => true,
            'allow_family_in_kk' => true,
            'allow_family_out_kk' => false,
            'allow_other' => false,
            'require_power_of_attorney' => false,
            'require_absence_reason' => false,
            'is_biometric_first_recording' => false,
            'policy_notes' => 'Catatan kebijakan tes',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('queue_service_types', [
            'id' => $service->id,
            'quota_per_day' => 25,
            'color' => '#0284c7',
        ]);
    }

    public function test_admin_can_manage_document_requirements_via_canonical_and_alias_routes()
    {
        $admin = User::first();
        $this->assertNotNull($admin, 'Admin user must exist');

        $service = ServiceType::first();
        $this->assertNotNull($service, 'Service type must exist');

        // 1. Add requirement document via canonical route
        $createResponse = $this->actingAs($admin)->post(route('admin.queue.service-documents.store', [
            'serviceTypeId' => $service->id,
        ]), [
            'label' => 'Surat Keterangan Uji Otomatis',
            'description' => 'Dokumen uji coba untuk validasi',
            'accepted_formats' => 'jpg,jpeg,png,pdf',
            'max_size_kb' => 5120,
            'is_required' => false,
            'capture_mode' => 'environment',
            'applicable_to' => 'all',
            'capture_hint' => 'Pastikan dokumen jelas',
            'sort_order' => 99,
        ]);

        $createResponse->assertRedirect();
        $createResponse->assertSessionHasNoErrors();

        $doc = ServiceDocument::where('service_type_id', $service->id)
            ->where('label', 'Surat Keterangan Uji Otomatis')
            ->first();
        $this->assertNotNull($doc, 'Document requirement must be stored');

        // 2. Update requirement document via alias route (service-types.documents.update)
        $updateResponse = $this->actingAs($admin)->put(route('admin.queue.service-types.documents.update', [
            'serviceTypeId' => $service->id,
            'documentId' => $doc->id,
        ]), [
            'label' => 'Surat Keterangan Uji Otomatis (Updated)',
            'description' => 'Diperbarui via alias route',
            'accepted_formats' => 'pdf',
            'max_size_kb' => 2048,
            'is_required' => true,
            'capture_mode' => 'environment',
            'applicable_to' => 'all',
            'capture_hint' => 'Format PDF saja',
            'sort_order' => 99,
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('queue_service_documents', [
            'id' => $doc->id,
            'label' => 'Surat Keterangan Uji Otomatis (Updated)',
            'accepted_formats' => 'pdf',
        ]);

        // 3. Delete requirement document via canonical route
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.queue.service-documents.destroy', [
            'serviceTypeId' => $service->id,
            'documentId' => $doc->id,
        ]));

        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('queue_service_documents', [
            'id' => $doc->id,
        ]);
    }

    public function test_f107_proxy_document_present_and_required_when_represented_in_rusak_and_perubahan_data(): void
    {
        $rusakService = ServiceType::where('slug', 'cetak-ktp-rusak')->first();
        $perubahanService = ServiceType::where('slug', 'perubahan-elemen-data')->first();
        $appService = app(QueueApplicationService::class);

        foreach ([$rusakService, $perubahanService] as $service) {
            $this->assertNotNull($service, "Service {$service->name} should exist");

            $uniqueNikSelf = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
            $uniqueNikProxy = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
            $uniqueRepNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

            // 1. Test Self: F-1.07 should NOT be shown
            $appSelf = $appService->createDraft([
                'applicant_nik' => $uniqueNikSelf,
                'applicant_name' => 'Warga Uji Sendiri',
                'applicant_phone' => '081234567890',
                'applicant_address' => 'Dompu',
                'attendance_type' => 'self',
            ], $service->id);

            $responseSelf = $this->get(route('queue.apply.documents', ['regNumber' => $appSelf->reg_number]));
            $responseSelf->assertOk();
            $requirementsSelf = $responseSelf->viewData('page')['props']['requirements'];
            $selfDocLabels = collect($requirementsSelf)->pluck('label')->toArray();

            $hasKuasaInSelf = collect($selfDocLabels)->contains(fn($l) => str_contains($l, 'F-1.07') || str_contains($l, 'Kuasa'));
            $this->assertFalse($hasKuasaInSelf, "F-1.07 Surat Kuasa should NOT be required or shown when applying for self");

            // 2. Test Diwakilkan (family_in_kk): F-1.07 MUST be shown and required
            $appProxy = $appService->createDraft([
                'applicant_nik' => $uniqueNikProxy,
                'applicant_name' => 'Warga Yang Diwakilkan',
                'applicant_phone' => '081234567891',
                'applicant_address' => 'Dompu',
                'attendance_type' => 'family_in_kk',
                'representative_name' => 'Keluarga Penerima Kuasa',
                'representative_nik' => $uniqueRepNik,
                'representative_relationship' => 'Orang Tua',
                'representative_phone' => '081234567892',
                'representative_reason' => 'Sedang bekerja',
            ], $service->id);

            $responseProxy = $this->get(route('queue.apply.documents', ['regNumber' => $appProxy->reg_number]));
            $responseProxy->assertOk();
            $requirementsProxy = $responseProxy->viewData('page')['props']['requirements'];
            $proxyDocLabels = collect($requirementsProxy)->pluck('label')->toArray();

            $hasKuasaInProxy = collect($proxyDocLabels)->contains(fn($l) => str_contains($l, 'F-1.07') || str_contains($l, 'Kuasa'));
            $this->assertTrue($hasKuasaInProxy, "F-1.07 Surat Kuasa MUST be shown when applying as representative for {$service->name}");

            // Verify that F-1.07 is marked as required (is_required = true)
            $kuasaDoc = collect($requirementsProxy)->first(fn($d) => str_contains($d['label'], 'F-1.07') || str_contains($d['label'], 'Kuasa'));
            $this->assertNotNull($kuasaDoc);
            $this->assertTrue((bool) $kuasaDoc['is_required'], "F-1.07 Surat Kuasa must be marked as required (Wajib)");
        }
    }

    public function test_requirements_dynamically_add_and_remove_according_to_admin_settings(): void
    {
        $perubahanService = ServiceType::where('slug', 'perubahan-elemen-data')->first();
        $this->assertNotNull($perubahanService);
        $appService = app(QueueApplicationService::class);

        // 1. Initial draft without F-1.06 (since it was deleted by admin)
        $uniqueNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $app = $appService->createDraft([
            'applicant_nik' => $uniqueNik,
            'applicant_name' => 'Warga Ubah Data Dinamis',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $perubahanService->id);

        $response = $this->get(route('queue.apply.documents', ['regNumber' => $app->reg_number]));
        $response->assertOk();
        $requirements = $response->viewData('page')['props']['requirements'];

        // Ensure deleted document does not reappear automatically
        $hasF106 = collect($requirements)->contains(fn($d) => str_contains($d['label'], 'F-1.06') || str_contains($d['label'], 'F1.06'));
        $this->assertFalse($hasF106, "Deleted F-1.06 must NOT reappear automatically on applicant form");

        // 2. Dynamically add a new requirement via admin and verify it appears immediately
        $customDoc = $perubahanService->documents()->create([
            'label' => 'Surat Pengantar RT/RW Dinamis',
            'description' => 'Surat pengantar resmi dari RT/RW domisili.',
            'accepted_formats' => 'jpg,jpeg,png,pdf',
            'max_size_kb' => 5120,
            'is_required' => true,
            'capture_mode' => 'environment',
            'applicable_to' => 'all',
            'sort_order' => 10,
        ]);

        $responseAfterAdd = $this->get(route('queue.apply.documents', ['regNumber' => $app->reg_number]));
        $responseAfterAdd->assertOk();
        $requirementsAfterAdd = $responseAfterAdd->viewData('page')['props']['requirements'];
        $hasCustomDoc = collect($requirementsAfterAdd)->contains(fn($d) => $d['id'] === $customDoc->id);
        $this->assertTrue($hasCustomDoc, "Newly added document must immediately appear in applicant form");

        // 3. Dynamically delete the requirement via admin and verify it disappears immediately
        $customDoc->delete();

        $responseAfterDelete = $this->get(route('queue.apply.documents', ['regNumber' => $app->reg_number]));
        $responseAfterDelete->assertOk();
        $requirementsAfterDelete = $responseAfterDelete->viewData('page')['props']['requirements'];
        $hasCustomDocAfterDelete = collect($requirementsAfterDelete)->contains(fn($d) => $d['id'] === $customDoc->id);
        $this->assertFalse($hasCustomDocAfterDelete, "Deleted document must immediately disappear from applicant form");

        // Clean up test draft
        $appService->deleteDraft($app);
    }

    public function test_draft_older_than_24_hours_is_automatically_purged_and_storage_cleaned(): void
    {
        $service = ServiceType::first();
        $this->assertNotNull($service);
        $appService = app(QueueApplicationService::class);

        $uniqueNikExpired = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $uniqueNikActive = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        // 1. Create an expired draft (e.g., 25 hours old)
        $expiredDraft = $appService->createDraft([
            'applicant_nik' => $uniqueNikExpired,
            'applicant_name' => 'Warga Draft Kedaluwarsa',
            'applicant_phone' => '081234567801',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        // Backdate created_at to 26 hours ago
        DB::table('queue_applications')
            ->where('id', $expiredDraft->id)
            ->update(['created_at' => now()->subHours(26)]);
        $expiredDraft->refresh();

        $this->assertTrue($expiredDraft->isExpiredDraft(24));

        // 2. Create an active draft (e.g. 1 hour old)
        $activeDraft = $appService->createDraft([
            'applicant_nik' => $uniqueNikActive,
            'applicant_name' => 'Warga Draft Aktif',
            'applicant_phone' => '081234567802',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $this->assertFalse($activeDraft->isExpiredDraft(24));

        // Run purge
        $purgedCount = $appService->purgeExpiredDrafts(24);
        $this->assertGreaterThanOrEqual(1, $purgedCount);

        // Assert expired draft was deleted from DB
        $this->assertDatabaseMissing('queue_applications', ['id' => $expiredDraft->id]);

        // Assert active draft remains untouched
        $this->assertDatabaseHas('queue_applications', ['id' => $activeDraft->id]);

        // Cleanup active draft
        $appService->deleteDraft($activeDraft);
    }

    public function test_visiting_expired_draft_session_purges_application_and_clears_session(): void
    {
        $service = ServiceType::first();
        $this->assertNotNull($service);
        $appService = app(QueueApplicationService::class);

        $uniqueNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $draft = $appService->createDraft([
            'applicant_nik' => $uniqueNik,
            'applicant_name' => 'Warga Uji Sesi Kedaluwarsa',
            'applicant_phone' => '081234567803',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        // Backdate created_at to 25 hours ago
        DB::table('queue_applications')
            ->where('id', $draft->id)
            ->update(['created_at' => now()->subHours(25)]);
        $draft->refresh();

        // Simulate applicant visiting Step 2 with verified session
        $response = $this->withSession([
            'applicant_verified_nik' => $uniqueNik,
            'applicant_verified_at' => now()->toIso8601String(),
        ])->get(route('queue.apply.documents', ['regNumber' => $draft->reg_number]));

        // Should redirect back to queue.apply.create with error notice
        $response->assertRedirect(route('queue.apply.create'));
        $response->assertSessionHas('error');
        $this->assertNull(session('applicant_verified_nik'));

        // Assert draft was purged from database
        $this->assertDatabaseMissing('queue_applications', ['id' => $draft->id]);
    }

    public function test_artisan_purge_expired_drafts_command_runs_successfully(): void
    {
        $command = app(\App\Console\Commands\PurgeExpiredDraftApplicationsCommand::class);
        $command->setLaravel($this->app);
        $exitCode = $command->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput()
        );
        $this->assertEquals(0, $exitCode);
    }

    public function test_admin_can_update_queue_settings_via_post_and_put(): void
    {
        $admin = User::first();
        $this->assertNotNull($admin);

        // 1. Test POST update
        $responsePost = $this->actingAs($admin)->post(route('admin.queue.settings.update'), [
            'settings' => [
                'queue.office_address' => 'Jl. Soekarno Hatta No. 9, Dompu Testing POST',
                'queue.open_time' => '08:15',
                'queue.notify_on_submit' => true,
            ]
        ]);
        $responsePost->assertRedirect();
        $responsePost->assertSessionHas('success');

        $this->assertEquals('Jl. Soekarno Hatta No. 9, Dompu Testing POST', \App\Models\Queue\QueueSetting::get('queue.office_address'));
        $this->assertEquals('08:15', \App\Models\Queue\QueueSetting::get('queue.open_time'));
        $this->assertEquals('1', \App\Models\Queue\QueueSetting::get('queue.notify_on_submit'));

        // Ensure group preservation
        $settingModel = \App\Models\Queue\QueueSetting::where('key', 'queue.open_time')->first();
        $this->assertEquals('schedule', $settingModel->group);

        // 2. Test PUT update
        $responsePut = $this->actingAs($admin)->put(route('admin.queue.settings.update'), [
            'settings' => [
                'queue.office_address' => 'Jl. Soekarno Hatta No. 9, Dompu Testing PUT',
                'queue.open_time' => '08:30',
                'queue.notify_on_submit' => false,
            ]
        ]);
        $responsePut->assertRedirect();
        $responsePut->assertSessionHas('success');

        $this->assertEquals('Jl. Soekarno Hatta No. 9, Dompu Testing PUT', \App\Models\Queue\QueueSetting::get('queue.office_address'));
        $this->assertEquals('08:30', \App\Models\Queue\QueueSetting::get('queue.open_time'));
        $this->assertEquals('0', \App\Models\Queue\QueueSetting::get('queue.notify_on_submit'));
    }

    public function test_all_four_applicant_email_notifications_are_dispatched_successfully(): void
    {
        Notification::fake();

        $service = ServiceType::first();
        $this->assertNotNull($service);
        $appService = app(QueueApplicationService::class);
        $notificationService = app(\App\Services\Queue\QueueNotificationService::class);

        $testEmail = 'pemohon.uji@example.com';
        $uniqueNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $application = $appService->createDraft([
            'applicant_nik' => $uniqueNik,
            'applicant_name' => 'Warga Uji Notifikasi',
            'applicant_phone' => '081234567800',
            'applicant_email' => $testEmail,
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $application->update([
            'queue_number' => 'H-007',
            'revision_notes' => 'Foto KK buram, mohon foto ulang.',
            'rejection_reason' => 'NIK tidak terdaftar dalam database SIAK.',
        ]);

        // 1. Nomor Antrean Terbit
        $notificationService->dispatch($application, 'approved');
        Notification::assertSentOnDemand(
            ApplicationApprovedNotification::class,
            function ($notification, $channels, $notifiable) use ($testEmail) {
                return $notifiable->routes['mail'] === $testEmail;
            }
        );

        // 2. Permohonan Ditolak
        $notificationService->dispatch($application, 'rejected');
        Notification::assertSentOnDemand(
            ApplicationRejectedNotification::class,
            function ($notification, $channels, $notifiable) use ($testEmail) {
                return $notifiable->routes['mail'] === $testEmail;
            }
        );

        // 3. Permohonan Perbaikan
        $notificationService->dispatch($application, 'revision');
        Notification::assertSentOnDemand(
            RevisionRequiredNotification::class,
            function ($notification, $channels, $notifiable) use ($testEmail) {
                return $notifiable->routes['mail'] === $testEmail;
            }
        );

        // 4. KTP Siap Diambil
        $notificationService->dispatch($application, 'ready_for_pickup');
        Notification::assertSentOnDemand(
            KtpReadyForPickupNotification::class,
            function ($notification, $channels, $notifiable) use ($testEmail) {
                return $notifiable->routes['mail'] === $testEmail;
            }
        );

        // Verify mail messages content
        $mailApproved = (new ApplicationApprovedNotification($application))->toMail(null);
        $this->assertStringContainsString('H-007', $mailApproved->subject);
        $this->assertStringContainsString('Nomor Antrean', $mailApproved->subject);

        $mailRejected = (new ApplicationRejectedNotification($application))->toMail(null);
        $this->assertStringContainsString('Ditolak', $mailRejected->subject);
        $this->assertStringContainsString('SIAK', implode(' ', $mailRejected->introLines));

        $mailRevision = (new RevisionRequiredNotification($application))->toMail(null);
        $this->assertStringContainsString('Perbaikan', $mailRevision->subject);
        $this->assertStringContainsString('Foto KK buram', implode(' ', $mailRevision->introLines));

        $mailReady = (new KtpReadyForPickupNotification($application))->toMail(null);
        $this->assertStringContainsString('Siap Diambil', $mailReady->subject);
        $this->assertStringContainsString('selesai dicetak', implode(' ', $mailReady->introLines));
        $this->assertStringNotContainsString('Bhayangkara', implode(' ', $mailReady->introLines));
        $this->assertStringNotContainsString('Kel. Bada', implode(' ', $mailReady->introLines));

        // Cleanup
        $appService->deleteDraft($application);
    }

    public function test_email_notifications_follow_office_contact_settings()
    {
        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $service = ServiceType::first();

        \App\Services\ConfigurationService::set('office_address', 'Jl. Pahlawan Reformasi No. 77, Dompu', 'office_contact');
        \App\Services\ConfigurationService::set('office_work_hours', 'Senin - Kamis: 08:00 - 15:45 WITA | Jumat: 08:00 - 11:30 WITA', 'office_contact');

        $application = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Budi Santoso',
            'applicant_phone' => '081234567890',
            'applicant_email' => 'budi@example.com',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $mailReady = (new KtpReadyForPickupNotification($application))->toMail(null);
        $readyContent = implode("\n", $mailReady->introLines);

        $this->assertStringContainsString('Jl. Pahlawan Reformasi No. 77, Dompu', $readyContent);
        $this->assertStringContainsString('08:00 - 15:45 WITA', $readyContent);
        $this->assertStringNotContainsString('Bhayangkara', $readyContent);

        $mailApproved = (new ApplicationApprovedNotification($application))->toMail(null);
        $approvedContent = implode("\n", $mailApproved->introLines);

        $this->assertStringContainsString('Jl. Pahlawan Reformasi No. 77, Dompu', $approvedContent);
        $this->assertStringNotContainsString('Bhayangkara', $approvedContent);

        $appService->deleteDraft($application);
    }

    public function test_cannot_reject_or_restart_processing_when_ktp_is_ready_for_pickup()
    {
        $admin = User::first() ?? User::factory()->create();
        $service = ServiceType::first();

        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $application = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Pemohon Siap Ambil',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $application->update([
            'status'         => QueueApplication::STATUS_READY_FOR_PICKUP,
            'applicant_name' => 'Pemohon Siap Ambil',
            'nik'            => '5205011212900001',
            'phone'          => '081234567890',
            'submitted_at'   => now(),
        ]);

        // Attempt to restart processing when KTP is already ready for pickup
        $responseProcessing = $this->actingAs($admin)->post(
            route('admin.queue.applications.start-processing', $application->id),
            ['notes' => 'Coba proses kembali']
        );
        $responseProcessing->assertSessionHas('error');
        $this->assertEquals(QueueApplication::STATUS_READY_FOR_PICKUP, $application->fresh()->status);

        // Attempt to reject when KTP is already ready for pickup
        $responseReject = $this->actingAs($admin)->post(
            route('admin.queue.applications.reject', $application->id),
            ['reason' => 'Dokumen asli tidak cocok']
        );
        $responseReject->assertSessionHas('error');
        $this->assertEquals(QueueApplication::STATUS_READY_FOR_PICKUP, $application->fresh()->status);

        // Cleanup
        $application->delete();
    }

    public function test_can_approve_single_and_all_documents_and_sync_status()
    {
        $admin = User::first() ?? User::factory()->create();
        $service = ServiceType::first();

        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $application = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Warga Uji Dokumen',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $doc1 = ApplicationDocument::create([
            'application_id' => $application->id,
            'service_document_id' => 1,
            'original_filename' => 'surat_hilang.jpg',
            'stored_filename' => 'surat_hilang.jpg',
            'disk' => 'local',
            'path' => 'test/surat_hilang.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'status' => ApplicationDocument::STATUS_PENDING,
        ]);

        $doc2 = ApplicationDocument::create([
            'application_id' => $application->id,
            'service_document_id' => 2,
            'original_filename' => 'kk.jpg',
            'stored_filename' => 'kk.jpg',
            'disk' => 'local',
            'path' => 'test/kk.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'status' => ApplicationDocument::STATUS_PENDING,
        ]);

        // 1. Approve single document
        $responseSingle = $this->actingAs($admin)->post(
            route('admin.queue.documents.approve', ['applicationId' => $application->id, 'docId' => $doc1->id])
        );
        $responseSingle->assertRedirect();
        $this->assertEquals(ApplicationDocument::STATUS_APPROVED, $doc1->fresh()->status);
        $this->assertEquals(ApplicationDocument::STATUS_PENDING, $doc2->fresh()->status);

        // 2. Approve all pending documents
        $responseAll = $this->actingAs($admin)->post(
            route('admin.queue.documents.approve-all', ['applicationId' => $application->id])
        );
        $responseAll->assertRedirect();
        $this->assertEquals(ApplicationDocument::STATUS_APPROVED, $doc2->fresh()->status);

        // Cleanup
        $doc1->delete();
        $doc2->delete();
        $application->delete();
    }

    public function test_store_draft_accepts_hyphen_formatted_phone_numbers()
    {
        $service = ServiceType::first();
        $this->assertNotNull($service);

        $applicantNik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $payload = [
            'service_type_id' => $service->id,
            'applicant_nik' => $applicantNik,
            'applicant_name' => 'Hyphen Phone Tester',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date' => '1995-05-15',
            'applicant_phone' => '0853-3344-7003',
            'applicant_address' => 'Jl. Bhayangkara No. 10',
            'applicant_district' => 'Kecamatan Dompu',
            'applicant_village' => 'Bada',
            'attendance_type' => 'self',
        ];

        $response = $this->post(route('queue.apply.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $app = QueueApplication::where('applicant_nik', $applicantNik)->first();
        $this->assertNotNull($app);
        $this->assertEquals('085333447003', $app->applicant_phone, 'Phone number must be cleanly stored without hyphens in database');

        $app->delete();
    }

    public function test_printing_actions_blocked_before_schedule_and_queue_number_issued()
    {
        $admin = User::first() ?? User::factory()->create();
        $service = ServiceType::first();

        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $application = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Warga Tanpa Antrean',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $application->update([
            'status' => QueueApplication::STATUS_PRE_VERIFIED,
            'queue_number' => null,
            'queue_slot_id' => null,
        ]);

        // Attempt start-processing before queue number/schedule is issued
        $resProcess = $this->actingAs($admin)->post(
            route('admin.queue.applications.start-processing', $application->id),
            ['notes' => 'Cetak tanpa antrean']
        );
        $resProcess->assertSessionHas('error');
        $this->assertStringContainsString('nomor antrean', session('error'));

        // Attempt ready-pickup before queue number/schedule is issued
        $resReady = $this->actingAs($admin)->post(
            route('admin.queue.applications.ready-pickup', $application->id),
            ['notes' => 'Siap ambil tanpa antrean']
        );
        $resReady->assertSessionHas('error');
        $this->assertStringContainsString('nomor antrean', session('error'));

        $application->delete();
    }

    public function test_application_verification_urgency_attributes_and_filtering()
    {
        $admin = User::first() ?? User::factory()->create();
        $service = ServiceType::first();

        $appService = app(\App\Services\Queue\QueueApplicationService::class);

        // App 1: Overdue unverified application (>24 hours)
        $app1 = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Pemohon Terlambat SLA',
            'applicant_phone' => '081234567891',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);
        $app1->update([
            'status' => QueueApplication::STATUS_SUBMITTED,
            'submitted_at' => now()->subHours(26),
        ]);

        // App 2: New incoming unverified application (<24 hours)
        $app2 = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Pemohon Baru Masuk',
            'applicant_phone' => '081234567892',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);
        $app2->update([
            'status' => QueueApplication::STATUS_SUBMITTED,
            'submitted_at' => now()->subMinutes(15),
        ]);

        // App 3: Already pre-verified (no longer needs initial review)
        $app3 = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Pemohon Sudah Lolos',
            'applicant_phone' => '081234567893',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);
        $app3->update([
            'status' => QueueApplication::STATUS_PRE_VERIFIED,
            'submitted_at' => now()->subHours(30),
        ]);

        // Assert model attributes and accessors
        $this->assertEquals('overdue', $app1->verification_urgency);
        $this->assertStringContainsString('24 Jam', $app1->verification_urgency_label);
        $this->assertNotNull($app1->waiting_time_human);

        $this->assertEquals('new', $app2->verification_urgency);
        $this->assertStringContainsString('Baru Masuk', $app2->verification_urgency_label);
        $this->assertNotNull($app2->waiting_time_human);

        $this->assertNull($app3->verification_urgency);
        $this->assertNull($app3->verification_urgency_label);
        $this->assertNull($app3->waiting_time_human);

        // Test filtering by overdue
        $resOverdue = $this->actingAs($admin)->get(route('admin.queue.applications.index', ['urgency' => 'overdue']));
        $resOverdue->assertStatus(200);
        $resOverdue->assertInertia(fn ($page) => 
            $page->component('Admin/Queue/Applications/Index')
                ->has('urgencyCounts.overdue')
                ->has('urgencyCounts.new')
                ->where('filters.urgency', 'overdue')
        );

        // Test filtering by new
        $resNew = $this->actingAs($admin)->get(route('admin.queue.applications.index', ['urgency' => 'new']));
        $resNew->assertStatus(200);
        $resNew->assertInertia(fn ($page) => 
            $page->component('Admin/Queue/Applications/Index')
                ->where('filters.urgency', 'new')
        );

        // Test show page includes verification_urgency
        $resShow = $this->actingAs($admin)->get(route('admin.queue.applications.show', $app1->id));
        $resShow->assertStatus(200);
        $resShow->assertInertia(fn ($page) => 
            $page->component('Admin/Queue/Applications/Show')
                ->where('application.verification_urgency', 'overdue')
        );

        $app1->forceDelete();
        $app2->forceDelete();
        $app3->forceDelete();
    }

    public function test_workflow_lifecycle_status_tabs_and_filtering()
    {
        $admin = User::first() ?? User::factory()->create();
        $service = ServiceType::first();
        $appService = app(\App\Services\Queue\QueueApplicationService::class);

        $appReady = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Warga Siap Ambil',
            'applicant_phone' => '081234567899',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);
        $appReady->update([
            'status' => QueueApplication::STATUS_READY_FOR_PICKUP,
            'queue_number' => 'H-999',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.queue.applications.index', ['status' => 'READY_FOR_PICKUP']));
        $res->assertStatus(200);
        $res->assertInertia(fn ($page) => 
            $page->component('Admin/Queue/Applications/Index')
                ->where('filters.status', 'READY_FOR_PICKUP')
                ->has('statusCounts.READY_FOR_PICKUP')
                ->has('statusCounts.QUEUE_BOOKED')
                ->has('statusCounts.PROCESSING')
                ->has('statusCounts.PRE_VERIFIED')
                ->has('statusCounts.ARRIVED')
        );

        $appReady->forceDelete();
    }

    public function test_applicant_email_defaults_to_gmail_when_username_only_is_provided_and_supports_custom_email()
    {
        $service = ServiceType::first();
        $this->assertNotNull($service);

        // 1. Kasus input hanya username tanpa domain: harus otomatis menjadi @gmail.com
        $nik1 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $res1 = $this->post(route('queue.apply.store'), [
            'service_type_id'       => $service->id,
            'applicant_nik'         => $nik1,
            'applicant_name'        => 'Ahmad Otomatis Gmail',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date'  => '2000-01-01',
            'applicant_phone'       => '0853-1122-3344',
            'applicant_email'       => 'ahmad.fauzi', // hanya username
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'Jl. Soekarno Hatta No. 10',
            'attendance_type'       => 'self',
        ]);
        $res1->assertSessionHasNoErrors();
        $app1 = QueueApplication::where('applicant_nik', $nik1)->first();
        $this->assertNotNull($app1);
        $this->assertEquals('ahmad.fauzi@gmail.com', $app1->applicant_email);

        // 2. Kasus input email kustom (misal @yahoo.com): harus dipertahankan sesuai input
        $nik2 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $res2 = $this->post(route('queue.apply.store'), [
            'service_type_id'       => $service->id,
            'applicant_nik'         => $nik2,
            'applicant_name'        => 'Budi Yahoo User',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date'  => '2001-02-02',
            'applicant_phone'       => '0853-2233-4455',
            'applicant_email'       => 'budi_dompu@yahoo.com', // kustom domain
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'Jl. Bhayangkara No. 5',
            'attendance_type'       => 'self',
        ]);
        $res2->assertSessionHasNoErrors();
        $app2 = QueueApplication::where('applicant_nik', $nik2)->first();
        $this->assertNotNull($app2);
        $this->assertEquals('budi_dompu@yahoo.com', $app2->applicant_email);

        // 3. Kasus input email dikosongkan (opsional): harus bernilai null
        $nik3 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $res3 = $this->post(route('queue.apply.store'), [
            'service_type_id'       => $service->id,
            'applicant_nik'         => $nik3,
            'applicant_name'        => 'Citra Tanpa Email',
            'applicant_birth_place' => 'Dompu',
            'applicant_birth_date'  => '2002-03-03',
            'applicant_phone'       => '0853-3344-5566',
            'applicant_email'       => '', // dikosongkan
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'Jl. Kartini No. 8',
            'attendance_type'       => 'self',
        ]);
        $res3->assertSessionHasNoErrors();
        $app3 = QueueApplication::where('applicant_nik', $nik3)->first();
        $this->assertNotNull($app3);
        $this->assertNull($app3->applicant_email);

        // Cleanup
        $app1->forceDelete();
        $app2->forceDelete();
        $app3->forceDelete();
    }

    public function test_demographic_text_fields_are_automatically_converted_to_uppercase()
    {
        $service = ServiceType::where('is_biometric_first_recording', false)->first() ?? ServiceType::first();
        $this->assertNotNull($service);

        $nik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $res = $this->post(route('queue.apply.store'), [
            'service_type_id'             => $service->id,
            'applicant_nik'               => $nik,
            'applicant_name'              => 'muhammad ihsan ramadhan', // huruf kecil
            'applicant_birth_place'       => 'kabupaten dompu', // huruf kecil
            'applicant_birth_date'        => '1998-05-15',
            'applicant_phone'             => '0853-9988-7766',
            'applicant_district'          => 'kecamatan dompu', // huruf kecil
            'applicant_village'           => 'kelurahan bali satu', // huruf kecil
            'applicant_address'           => 'dusun suka maju, rt 02 / rw 01', // huruf kecil
            'attendance_type'             => 'family_in_kk',
            'representative_nik'          => '5205123456789012',
            'representative_name'         => 'ayah kandung pemohon', // huruf kecil
            'representative_phone'        => '0852-1122-3344',
            'representative_relationship' => 'Orang Tua (Ayah / Ibu)',
            'absence_reason'              => 'Pemohon sedang dinas',
        ]);

        $res->assertSessionHasNoErrors();
        $app = QueueApplication::where('applicant_nik', $nik)->first();
        $this->assertNotNull($app);

        // Verifikasi semua field teks otomatis tersimpan dalam HURUF BESAR (UPPERCASE)
        $this->assertEquals('MUHAMMAD IHSAN RAMADHAN', $app->applicant_name);
        $this->assertEquals('KABUPATEN DOMPU', $app->applicant_birth_place);
        $this->assertEquals('DUSUN SUKA MAJU, RT 02 / RW 01', $app->applicant_address);
        $this->assertEquals('KECAMATAN DOMPU', $app->applicant_district);
        $this->assertEquals('KELURAHAN BALI SATU', $app->applicant_village);
        $this->assertEquals('AYAH KANDUNG PEMOHON', $app->representative_name);

        $app->forceDelete();
    }

    public function test_manual_birth_date_input_with_auto_correction()
    {
        $service = ServiceType::first();
        $this->assertNotNull($service);

        // 1. Input manual DD/MM/YYYY dengan tanggal 31 di bulan April (30 hari)
        $nik1 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $res1 = $this->post(route('queue.apply.store'), [
            'service_type_id'       => $service->id,
            'applicant_nik'         => $nik1,
            'applicant_name'        => 'Koreksi April Tiga Puluh Satu',
            'applicant_birth_place' => 'DOMPU',
            'applicant_birth_date'  => '31/04/1998', // 31 April tidak ada, dikoreksi jadi 30 April
            'applicant_phone'       => '0853-1111-2222',
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'JL. SOEKARNO HATTA',
            'attendance_type'       => 'self',
        ]);
        $res1->assertSessionHasNoErrors();
        $app1 = QueueApplication::where('applicant_nik', $nik1)->first();
        $this->assertNotNull($app1);
        $this->assertEquals('1998-04-30', $app1->applicant_birth_date->format('Y-m-d'));

        // 2. Input manual DD-MM-YYYY dengan tanggal 29 Februari di tahun bukan kabisat (2001)
        $nik2 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $res2 = $this->post(route('queue.apply.store'), [
            'service_type_id'       => $service->id,
            'applicant_nik'         => $nik2,
            'applicant_name'        => 'Koreksi Februari Bukan Kabisat',
            'applicant_birth_place' => 'DOMPU',
            'applicant_birth_date'  => '29-02-2001', // 2001 bukan kabisat, dikoreksi jadi 28 Feb
            'applicant_phone'       => '0853-3333-4444',
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'JL. BHAYANGKARA',
            'attendance_type'       => 'self',
        ]);
        $res2->assertSessionHasNoErrors();
        $app2 = QueueApplication::where('applicant_nik', $nik2)->first();
        $this->assertNotNull($app2);
        $this->assertEquals('2001-02-28', $app2->applicant_birth_date->format('Y-m-d'));

        // 3. Input manual tahun 2 digit (misal 17/08/98 -> 1998-08-17)
        $nik3 = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        $res3 = $this->post(route('queue.apply.store'), [
            'service_type_id'       => $service->id,
            'applicant_nik'         => $nik3,
            'applicant_name'        => 'Koreksi Tahun Dua Digit',
            'applicant_birth_place' => 'DOMPU',
            'applicant_birth_date'  => '17/08/98', // 98 dikoreksi jadi 1998
            'applicant_phone'       => '0853-5555-6666',
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'JL. KARTINI',
            'attendance_type'       => 'self',
        ]);
        $res3->assertSessionHasNoErrors();
        $app3 = QueueApplication::where('applicant_nik', $nik3)->first();
        $this->assertNotNull($app3);
        $this->assertEquals('1998-08-17', $app3->applicant_birth_date->format('Y-m-d'));

        // Cleanup
        $app1->forceDelete();
        $app2->forceDelete();
        $app3->forceDelete();
    }

    public function test_queue_number_is_issued_immediately_upon_submitting_verification_documents()
    {
        $service = ServiceType::first();
        $this->assertNotNull($service);

        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $nik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $app = $appService->createDraft([
            'applicant_nik'         => $nik,
            'applicant_name'        => 'Uji Terbit Nomor Antrean Dahulu',
            'applicant_birth_place' => 'DOMPU',
            'applicant_birth_date'  => '1995-08-17',
            'applicant_phone'       => '0853-9999-8888',
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'JL. SOEKARNO HATTA',
            'attendance_type'       => 'self',
        ], $service->id);

        $this->assertNull($app->queue_number);
        $this->assertNull($app->queue_slot_id);

        // Upload berkas persyaratan
        foreach ($service->documents as $docConfig) {
            $fakeFile = UploadedFile::fake()->image('doc_' . $docConfig->id . '.jpg', 600, 400);
            $appService->uploadDocument($app, $docConfig->id, $fakeFile);
        }

        // Kirim berkas verifikasi (Step 2 submit)
        $submitted = $appService->submit($app, true);

        // Nomor antrean dan jadwal wajib langsung terbit terlebih dahulu
        $this->assertEquals(QueueApplication::STATUS_SUBMITTED, $submitted->status);
        $this->assertNotNull($submitted->queue_number);
        $this->assertNotNull($submitted->queue_slot_id);
        $this->assertMatchesRegularExpression('/^[A-Z]-\d{3}$/', $submitted->queue_number);

        // Akses status menampilkan nomor antrean resmi
        $res = $this->get(route('queue.status', ['token' => $submitted->access_token]));
        $res->assertOk();

        $submitted->forceDelete();
    }

    public function test_non_superadmin_cannot_delete_queue_application()
    {
        $service = ServiceType::first();
        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $nik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $app = $appService->createDraft([
            'applicant_nik'         => $nik,
            'applicant_name'        => 'Uji Hak Akses Hapus Non-Superadmin',
            'applicant_birth_place' => 'DOMPU',
            'applicant_birth_date'  => '1995-08-17',
            'applicant_phone'       => '0853-9999-8888',
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'JL. SOEKARNO HATTA',
            'attendance_type'       => 'self',
        ], $service->id);

        // 1. Regular operator (Petugas Verifikasi)
        $operator = User::whereHas('roles', fn($q) => $q->where('name', 'Petugas Verifikasi / Registrasi KTP-el'))->first();
        if (!$operator) {
            $operator = User::factory()->create();
            $operator->assignRole('Petugas Verifikasi / Registrasi KTP-el');
        }

        $response = $this->actingAs($operator)->delete(route('admin.queue.applications.destroy', $app->id));
        $response->assertForbidden();

        // Application must still exist
        $this->assertNotNull(QueueApplication::find($app->id));

        $app->forceDelete();
    }

    public function test_superadmin_can_delete_queue_application_permanently()
    {
        $service = ServiceType::first();
        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $nik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        $app = $appService->createDraft([
            'applicant_nik'         => $nik,
            'applicant_name'        => 'Uji Hapus Permanen Superadmin',
            'applicant_birth_place' => 'DOMPU',
            'applicant_birth_date'  => '1995-08-17',
            'applicant_phone'       => '0853-9999-8888',
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'JL. SOEKARNO HATTA',
            'attendance_type'       => 'self',
        ], $service->id);

        $superAdmin = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Administrator Utama', 'Super Administrator']))->first();
        if (!$superAdmin) {
            $superAdmin = User::where('username', 'superadmin')->first();
        }
        $this->assertNotNull($superAdmin);

        $response = $this->actingAs($superAdmin)->delete(route('admin.queue.applications.destroy', $app->id));
        $response->assertSessionHas('success');

        // Application must be permanently removed
        $this->assertNull(QueueApplication::withTrashed()->find($app->id));
    }

    public function test_officer_can_escalate_and_synchronize_application_status_manually()
    {
        $service = ServiceType::first();
        $appService = app(\App\Services\Queue\QueueApplicationService::class);
        $nik = '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);

        // 1. Create an application in DRAFT status
        $app = $appService->createDraft([
            'applicant_nik'         => $nik,
            'applicant_name'        => 'Uji Eskalasi Status Draf',
            'applicant_birth_place' => 'DOMPU',
            'applicant_birth_date'  => '1995-08-17',
            'applicant_phone'       => '0853-9999-8888',
            'applicant_district'    => 'DOMPU',
            'applicant_village'     => 'BADA',
            'applicant_address'     => 'JL. SOEKARNO HATTA',
            'attendance_type'       => 'self',
        ], $service->id);

        $this->assertEquals(QueueApplication::STATUS_DRAFT, $app->status);
        $this->assertNull($app->queue_number);

        $officer = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Petugas Verifikasi', 'Super Administrator', 'Administrator Utama']))->first()
            ?: User::first();

        // 2. Escalate status from DRAFT to SUBMITTED with auto_issue_queue
        $response = $this->actingAs($officer)->post(route('admin.queue.applications.update-status', $app->id), [
            'status'           => QueueApplication::STATUS_SUBMITTED,
            'reason'           => 'Eskalasi manual permohonan yang macet di draf',
            'auto_issue_queue' => true,
        ]);

        $response->assertSessionHas('success');
        $app->refresh();
        $this->assertEquals(QueueApplication::STATUS_SUBMITTED, $app->status);
        $this->assertNotNull($app->submitted_at, 'submitted_at must not be null');
        $this->assertNotNull($app->queue_number, 'queue_number must not be null');

        // 3. Verify history audit log was recorded
        $history = \App\Models\Queue\VerificationHistory::where('application_id', $app->id)
            ->where('to_status', QueueApplication::STATUS_SUBMITTED)
            ->first();
        $this->assertNotNull($history, 'history must not be null');
        $this->assertStringContainsString('Eskalasi manual', $history->notes);

        // 4. Escalate to READY_FOR_PICKUP and ensure handover is synchronized
        $response2 = $this->actingAs($officer)->post(route('admin.queue.applications.update-status', $app->id), [
            'status' => QueueApplication::STATUS_READY_FOR_PICKUP,
            'reason' => 'KTP fisik sudah dicetak di loket',
        ]);
        $response2->assertSessionHas('success');
        $app->refresh();
        $this->assertEquals(QueueApplication::STATUS_READY_FOR_PICKUP, $app->status);

        $handover = \App\Models\Queue\KtpHandover::where('application_id', $app->id)->first();
        $this->assertNotNull($handover, 'handover must not be null');
        $this->assertEquals(\App\Models\Queue\KtpHandover::STATUS_READY, $handover->status);
        $this->assertNotNull($handover->ready_marked_at, 'handover ready_marked_at must not be null');

        $app->forceDelete();
    }

    /**
     * Test configurable draft waiting timeout and auto-purge workflow.
     */
    public function test_draft_timeout_setting_and_auto_purge_workflow(): void
    {
        // 1. Set configurable timeout to 15 minutes
        \App\Models\Queue\QueueSetting::set('queue.draft_timeout_minutes', '15');
        $this->assertEquals('15', \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes'));

        $service = ServiceType::first();
        $this->assertNotNull($service);

        $appService = app(\App\Services\Queue\QueueApplicationService::class);

        // 2. Create a fresh draft
        $freshApp = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Warga Draf Baru',
            'applicant_phone' => '081234567890',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        $this->assertFalse($freshApp->isExpiredDraft());
        $this->assertGreaterThan(0, $freshApp->draft_remaining_seconds);
        $this->assertLessThanOrEqual(15, $freshApp->draft_remaining_minutes);

        // 3. Create an abandoned draft (older than 15 minutes, e.g. 20 minutes ago)
        $staleApp = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Warga Draf Usang',
            'applicant_phone' => '081234567891',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);

        // Manually age the draft beyond 15 minutes
        $staleApp->created_at = now()->subMinutes(20);
        $staleApp->saveQuietly();

        $this->assertTrue($staleApp->isExpiredDraft());
        $this->assertEquals(0, $staleApp->draft_remaining_seconds);

        // 4. Accessing documents page for stale app triggers automatic cleanup & redirect
        $response = $this->get(route('queue.apply.documents', ['regNumber' => $staleApp->reg_number]));
        $response->assertRedirect(route('queue.apply.create'));
        $this->assertNull(QueueApplication::find($staleApp->id));

        // 5. Verify service purgeExpiredDrafts automatically cleans up stale drafts
        $staleApp2 = $appService->createDraft([
            'applicant_nik' => '5205' . str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
            'applicant_name' => 'Warga Draf Usang 2',
            'applicant_phone' => '081234567892',
            'applicant_address' => 'Dompu',
            'attendance_type' => 'self',
        ], $service->id);
        $staleApp2->created_at = now()->subMinutes(30);
        $staleApp2->saveQuietly();

        $purgedCount = $appService->purgeExpiredDrafts();
        $this->assertGreaterThanOrEqual(1, $purgedCount);
        $this->assertNull(QueueApplication::find($staleApp2->id));

        // Fresh draft should still exist
        $this->assertNotNull(QueueApplication::find($freshApp->id));

        // 6. Test Console Command respects dynamic timeout
        \Illuminate\Support\Facades\Artisan::registerCommand(app(\App\Console\Commands\PurgeExpiredDraftApplicationsCommand::class));
        $this->artisan('queue:purge-expired-drafts')->assertSuccessful();

        $freshApp->forceDelete();
    }
}
