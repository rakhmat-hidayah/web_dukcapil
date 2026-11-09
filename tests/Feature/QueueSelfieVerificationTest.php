<?php

namespace Tests\Feature;

use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSelfieVerification;
use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use App\Models\User;
use App\Services\Queue\QueueDocumentStorage;
use App\Services\Queue\QueueSelfieVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueueSelfieVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $officer;
    protected User $regularUser;
    protected QueueApplication $application;
    protected ApplicationDocument $selfieDoc;
    protected ApplicationDocument $ktpDoc;
    protected string $fakeSelfiePath;
    protected string $fakeKtpPath;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(QueueDocumentStorage::DISK);

        // Ensure Indonesian roles exist
        \App\Services\AutoRoleSync::sync(true);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Setup users
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'username' => 'superadmin',
        ]);
        $this->superAdmin->assignRole('Administrator Utama');

        $this->officer = User::factory()->create([
            'name' => 'Petugas Verifikator',
            'username' => 'verifikator',
        ]);
        $this->officer->assignRole('Petugas Verifikasi / Registrasi KTP-el');

        $this->regularUser = User::factory()->create([
            'name' => 'Pengguna Biasa',
            'username' => 'userbiasa',
        ]);

        // Setup service type
        $serviceType = ServiceType::create([
            'name' => 'Perekaman KTP-el Baru',
            'code' => 'KTP-BARU',
            'is_active' => true,
        ]);

        $selfieServiceDoc = ServiceDocument::create([
            'service_type_id' => $serviceType->id,
            'label' => 'Foto Wajah Pemohon (Selfie Verifikasi)',
            'capture_mode' => 'user',
            'is_required' => true,
            'accepted_formats' => 'jpg,jpeg,png',
            'max_size_kb' => 5120,
        ]);

        $ktpServiceDoc = ServiceDocument::create([
            'service_type_id' => $serviceType->id,
            'label' => 'Kartu Keluarga (KK)',
            'capture_mode' => 'environment',
            'is_required' => true,
            'accepted_formats' => 'jpg,jpeg,png,pdf',
            'max_size_kb' => 5120,
        ]);

        // Setup application
        $this->application = QueueApplication::create([
            'reg_number' => 'REG-' . strtoupper(Str::random(8)),
            'access_token' => Str::random(48),
            'service_type_id' => $serviceType->id,
            'status' => QueueApplication::STATUS_SUBMITTED,
            'applicant_name' => 'Ahmad Dahlan',
            'applicant_nik' => '5205011234560001',
            'attendance_type' => 'self',
        ]);

        // Setup physical fake files in storage
        $this->fakeSelfiePath = 'queue-documents/2026/11/' . $this->application->id . '/selfie.jpg';
        $this->fakeKtpPath = 'queue-documents/2026/11/' . $this->application->id . '/kk.jpg';

        Storage::disk(QueueDocumentStorage::DISK)->put($this->fakeSelfiePath, 'FAKE_SELFIE_BINARY_CONTENT');
        Storage::disk(QueueDocumentStorage::DISK)->put($this->fakeKtpPath, 'FAKE_KK_BINARY_CONTENT');

        $this->selfieDoc = ApplicationDocument::create([
            'application_id' => $this->application->id,
            'service_document_id' => $selfieServiceDoc->id,
            'original_filename' => 'selfie_pemohon.jpg',
            'stored_filename' => 'selfie.jpg',
            'disk' => QueueDocumentStorage::DISK,
            'path' => $this->fakeSelfiePath,
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => strlen('FAKE_SELFIE_BINARY_CONTENT'),
            'status' => ApplicationDocument::STATUS_PENDING,
        ]);

        $this->ktpDoc = ApplicationDocument::create([
            'application_id' => $this->application->id,
            'service_document_id' => $ktpServiceDoc->id,
            'original_filename' => 'kartu_keluarga.jpg',
            'stored_filename' => 'kk.jpg',
            'disk' => QueueDocumentStorage::DISK,
            'path' => $this->fakeKtpPath,
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => strlen('FAKE_KK_BINARY_CONTENT'),
            'status' => ApplicationDocument::STATUS_PENDING,
        ]);
    }

    /**
     * Test 1 & 2: Anonymous visitor and unauthorized user cannot access protected document serve endpoint.
     */
    public function test_unauthorized_and_anonymous_visitors_cannot_access_serve_endpoint()
    {
        // 1. Anonymous visitor
        $resAnon = $this->get(route('admin.queue.documents.serve', ['documentId' => $this->selfieDoc->id]));
        $this->assertTrue(in_array($resAnon->status(), [302, 401, 403]));

        // 2. Regular user without permissions
        $resUser = $this->actingAs($this->regularUser)->get(route('admin.queue.documents.serve', ['documentId' => $this->selfieDoc->id]));
        $resUser->assertStatus(403);
    }

    /**
     * Test 3: Public applicant token cannot access biometric selfie photo.
     */
    public function test_public_applicant_cannot_access_selfie_photo_via_token()
    {
        $res = $this->get(route('queue.serve-doc', [
            'token' => $this->application->access_token,
            'documentId' => $this->selfieDoc->id,
        ]));

        $res->assertStatus(403);
    }

    /**
     * Test 4, 5 & 6: Authenticated officer with permission can preview selfie with Cache-Control: no-store and nosniff headers.
     */
    public function test_officer_can_preview_selfie_with_no_store_and_nosniff_headers()
    {
        $res = $this->actingAs($this->officer)->get(route('admin.queue.documents.serve', ['documentId' => $this->selfieDoc->id]));
        $res->assertStatus(200);
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));
    }

    /**
     * Test 7 & 8: Form validation requires decision and mandatory responsibility checkbox.
     */
    public function test_selfie_verification_requires_decision_and_responsibility_checkbox()
    {
        // Missing both decision and checkbox
        $res1 = $this->actingAs($this->officer)
            ->postJson(route('admin.queue.applications.selfie.verify', ['applicationId' => $this->application->id]), []);
        $res1->assertStatus(422)
             ->assertJsonValidationErrors(['decision', 'statement_agreed']);

        // Missing checkbox
        $res2 = $this->actingAs($this->officer)
            ->postJson(route('admin.queue.applications.selfie.verify', ['applicationId' => $this->application->id]), [
                'decision' => 'MATCH',
                'statement_agreed' => false,
            ]);
        $res2->assertStatus(422)
             ->assertJsonValidationErrors(['statement_agreed']);

        // Invalid decision
        $res3 = $this->actingAs($this->officer)
            ->postJson(route('admin.queue.applications.selfie.verify', ['applicationId' => $this->application->id]), [
                'decision' => 'INVALID_CHOICE',
                'statement_agreed' => true,
            ]);
        $res3->assertStatus(422)
             ->assertJsonValidationErrors(['decision']);
    }

    /**
     * Test 9, 11, 12, 13: MATCH decision records metadata, deletes selfie file from disk, and serves 410 on subsequent calls.
     */
    public function test_match_decision_persists_metadata_and_permanently_destroys_selfie_file()
    {
        // Verify file exists on storage before verification
        $this->assertTrue(Storage::disk(QueueDocumentStorage::DISK)->exists($this->fakeSelfiePath));

        $res = $this->actingAs($this->officer)
            ->postJson(route('admin.queue.applications.selfie.verify', ['applicationId' => $this->application->id]), [
                'decision' => 'MATCH',
                'statement_agreed' => true,
                'statement_version' => 'v1.0',
            ]);

        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
                'verification' => [
                    'decision' => 'MATCH',
                    'deletion_status' => QueueSelfieVerification::DELETION_DELETED,
                ],
            ]);

        // 1. Database assertion
        $this->assertDatabaseHas('queue_selfie_verifications', [
            'application_id' => $this->application->id,
            'decision' => 'MATCH',
            'verified_by' => $this->officer->id,
            'deletion_status' => QueueSelfieVerification::DELETION_DELETED,
        ]);

        // 2. Physical file deletion assertion (Point 11 & 12)
        $this->assertFalse(Storage::disk(QueueDocumentStorage::DISK)->exists($this->fakeSelfiePath));

        // 3. Document model update assertion
        $this->selfieDoc->refresh();
        $this->assertTrue($this->selfieDoc->is_destroyed);
        $this->assertEquals('', $this->selfieDoc->getRawOriginal('path'));
        $this->assertEquals(ApplicationDocument::STATUS_APPROVED, $this->selfieDoc->status);

        // 4. Point 13: Old preview URL returns 410 Gone
        $resServe = $this->actingAs($this->officer)->get(route('admin.queue.documents.serve', ['documentId' => $this->selfieDoc->id]));
        $resServe->assertStatus(410);

        // 5. Point 16: KK document remains 100% intact
        $this->assertTrue(Storage::disk(QueueDocumentStorage::DISK)->exists($this->fakeKtpPath));
        $this->ktpDoc->refresh();
        $this->assertFalse($this->ktpDoc->is_destroyed);
    }

    /**
     * Test 10: MISMATCH decision flags application for revision and still permanently destroys selfie.
     */
    public function test_mismatch_decision_flags_revision_and_destroys_selfie_file()
    {
        $this->assertTrue(Storage::disk(QueueDocumentStorage::DISK)->exists($this->fakeSelfiePath));

        $res = $this->actingAs($this->officer)
            ->postJson(route('admin.queue.applications.selfie.verify', ['applicationId' => $this->application->id]), [
                'decision' => 'MISMATCH',
                'statement_agreed' => true,
                'statement_version' => 'v1.0',
            ]);

        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
                'verification' => [
                    'decision' => 'MISMATCH',
                    'deletion_status' => QueueSelfieVerification::DELETION_DELETED,
                ],
            ]);

        // Application status is set to REVISION_REQUIRED
        $this->application->refresh();
        $this->assertEquals(QueueApplication::STATUS_REVISION_REQUIRED, $this->application->status);
        $this->assertStringContainsString('MISMATCH', $this->application->revision_notes);

        // Selfie document marked for revision and destroyed
        $this->selfieDoc->refresh();
        $this->assertEquals(ApplicationDocument::STATUS_REVISION_REQUIRED, $this->selfieDoc->status);
        $this->assertTrue($this->selfieDoc->is_destroyed);

        // Physical file destroyed
        $this->assertFalse(Storage::disk(QueueDocumentStorage::DISK)->exists($this->fakeSelfiePath));
    }

    /**
     * Test 14: If deletion encounters an issue, status is marked DELETION_FAILED and retry function resolves it.
     */
    public function test_deletion_failure_and_retry_mechanism()
    {
        $service = app(QueueSelfieVerificationService::class);

        $verification = QueueSelfieVerification::create([
            'application_id' => $this->application->id,
            'document_id' => $this->selfieDoc->id,
            'decision' => 'MATCH',
            'verified_by' => $this->officer->id,
            'verified_by_name' => $this->officer->name,
            'verified_at' => now(),
            'statement_version' => 'v1.0',
            'statement_agreed' => true,
            'deletion_status' => QueueSelfieVerification::DELETION_FAILED,
            'deletion_error' => 'Simulated filesystem lock',
            'retry_count' => 1,
        ]);

        $this->assertTrue(Storage::disk(QueueDocumentStorage::DISK)->exists($this->fakeSelfiePath));

        // Trigger retry
        $retriedCount = $service->retryFailedDeletions();
        $this->assertEquals(1, $retriedCount);

        // Verify status is now DELETED and file is gone
        $verification->refresh();
        $this->assertEquals(QueueSelfieVerification::DELETION_DELETED, $verification->deletion_status);
        $this->assertFalse(Storage::disk(QueueDocumentStorage::DISK)->exists($this->fakeSelfiePath));
    }

    /**
     * Test 15: ArchiveService ignores destroyed selfie documents and does not fail completeness check.
     */
    public function test_archive_service_excludes_selfie_and_passes_completeness_check_after_destruction()
    {
        // Destroy selfie
        $this->selfieDoc->update([
            'is_destroyed' => true,
            'destroyed_at' => now(),
            'path' => '',
            'status' => ApplicationDocument::STATUS_APPROVED,
        ]);

        $archiveService = app(\App\Services\Archive\ArchiveService::class);
        $completeness = $archiveService->checkApplicationCompleteness($this->application->fresh(['documents', 'serviceType.documents']));

        // Application has 1 valid non-selfie document (KK) and completeness check has 0 issues
        $this->assertTrue($completeness['is_complete']);
        $this->assertEmpty($completeness['issues']);
        $this->assertCount(1, $completeness['documents']);
        $this->assertEquals('Kartu Keluarga (KK)', $completeness['documents'][0]['requirement']->label);
    }
}
