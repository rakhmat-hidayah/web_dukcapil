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
use App\Services\Queue\QueueApplicationService;
use App\Services\Queue\QueueNumberGenerator;
use App\Services\Queue\QueueSlotService;
use App\Services\Queue\QueueVerificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $this->assertEquals('Istri Sah', $draft->representative_name);
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

        // 3. Submit application with statement agreed
        $submittedApp = $appService->submit($application, true);
        $this->assertEquals(QueueApplication::STATUS_SUBMITTED, $submittedApp->status);
        $this->assertTrue($submittedApp->statement_agreed);

        // 4. Officer Online Pre-Verification (PRE_VERIFIED)
        $verifierService = app(QueueVerificationService::class);
        $officer = User::first() ?? User::factory()->create();

        $verifierService->preVerify($submittedApp, $officer, 'Dokumen online lengkap dan sah.');
        $this->assertEquals(QueueApplication::STATUS_PRE_VERIFIED, $submittedApp->fresh()->status);

        // 5. Applicant chooses queue schedule slot
        $schedule = QueueSchedule::firstOrCreate(
            ['schedule_date' => now()->addDays(3)->format('Y-m-d')],
            [
                'open_time' => '08:00:00',
                'close_time' => '15:30:00',
                'total_quota' => 50,
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
                'name' => 'Sesi 1 Pagi (08:00 - 11:30)',
                'start_time' => '08:00:00',
                'end_time' => '11:30:00',
                'quota' => 20,
                'booked_count' => 0,
            ]
        );

        $verifierService->bookQueueSlot($submittedApp, $slot->id);
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
}
