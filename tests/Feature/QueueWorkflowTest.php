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
}
