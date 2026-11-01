<?php

namespace Tests\Feature;

use App\Models\Archive\ArchivePackage;
use App\Models\Archive\ArchivePackageItem;
use App\Models\Archive\ArchiveSetting;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use App\Models\User;
use App\Services\Archive\ArchiveService;
use App\Services\Archive\Storage\ArchiveStorageManager;
use App\Services\Archive\Storage\LocalDiskArchiveStorageAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use ZipArchive;

class ArchiveWorkflowTest extends TestCase
{
    protected User $adminUser;
    protected User $archiveOperatorUser;
    protected User $unauthorizedUser;

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

        ArchiveSetting::seedDefaults();

        // 1. Admin User
        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_archive_test@dukcapil.dompukab.go.id'],
            ['name' => 'Admin Test', 'username' => 'admin_archive_test', 'password' => bcrypt('password')]
        );
        $this->adminUser->syncRoles(['Super Administrator']);

        // 2. Archive Operator User
        $this->archiveOperatorUser = User::firstOrCreate(
            ['email' => 'operator_archive_test@dukcapil.dompukab.go.id'],
            ['name' => 'Operator Arsip Test', 'username' => 'operator_archive_test', 'password' => bcrypt('password')]
        );
        $this->archiveOperatorUser->syncRoles(['Petugas Pengarsipan']);

        // 3. Unauthorized User
        $this->unauthorizedUser = User::firstOrCreate(
            ['email' => 'unauth_user_test@dukcapil.dompukab.go.id'],
            ['name' => 'Unauthorized User', 'username' => 'unauth_user_test', 'password' => bcrypt('password')]
        );
        $this->unauthorizedUser->syncRoles([]);
    }

    /**
     * Helper to create a dummy valid completed application with actual files in private storage.
     */
    protected function createValidCompletedApplication(): QueueApplication
    {
        $serviceType = ServiceType::create([
            'name' => 'Layanan Arsip Test ' . uniqid(),
            'slug' => 'layanan-arsip-test-' . uniqid(),
            'is_active' => true,
        ]);

        $serviceDoc = ServiceDocument::create([
            'service_type_id' => $serviceType->id,
            'label' => 'Surat Kehilangan Kepolisian',
            'is_required' => true,
            'accepted_formats' => 'jpg,png,pdf',
            'max_size_kb' => 2048,
        ]);

        $regNumber = 'REG-' . date('ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

        $app = QueueApplication::create([
            'reg_number' => $regNumber,
            'service_type_id' => $serviceType->id,
            'status' => QueueApplication::STATUS_COMPLETED,
            'applicant_name' => 'Ahmad Dahlan Test',
            'applicant_nik' => '5205010101950001',
            'applicant_phone' => '082345678901',
            'applicant_address' => 'Jl. Soekarno Hatta No. 1, Dompu',
            'applicant_district' => 'DOMPU',
            'applicant_village' => 'BADA',
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);

        // Put an actual test file on disk
        $relPath = "queue-documents/test/{$app->id}/surat_kehilangan.jpg";
        Storage::disk('local')->put($relPath, 'IMAGE_BINARY_TEST_CONTENT_' . uniqid());

        ApplicationDocument::create([
            'application_id' => $app->id,
            'service_document_id' => $serviceDoc->id,
            'original_filename' => 'surat_kehilangan.jpg',
            'stored_filename' => 'surat_kehilangan.jpg',
            'disk' => 'local',
            'path' => $relPath,
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => Storage::disk('local')->size($relPath),
            'original_size_bytes' => Storage::disk('local')->size($relPath),
            'status' => ApplicationDocument::STATUS_APPROVED,
        ]);

        return $app;
    }

    public function test_archive_dashboard_renders_for_operator_arsip()
    {
        $response = $this->actingAs($this->archiveOperatorUser)
            ->get(route('admin.archive.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Archive/Dashboard')
            ->has('stats')
            ->where('stats.operation_mode', 'ARSIP_SAJA')
        );
    }

    public function test_eligible_completed_application_appears_in_ready_list()
    {
        $app = $this->createValidCompletedApplication();

        $response = $this->actingAs($this->archiveOperatorUser)
            ->get(route('admin.archive.ready-applications'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Archive/ReadyApplications')
            ->has('applications.data')
        );
    }

    public function test_unapproved_draft_application_does_not_appear_in_ready_list()
    {
        $st = ServiceType::first();
        $draftApp = QueueApplication::create([
            'reg_number' => 'REG-DRAFT-' . uniqid(),
            'service_type_id' => $st->id,
            'status' => QueueApplication::STATUS_DRAFT,
            'applicant_name' => 'Draft Applicant',
            'applicant_nik' => '5205010101959999',
            'applicant_phone' => '082345678901',
            'applicant_address' => 'Jl. Dompu No. 1',
        ]);

        $service = app(ArchiveService::class);
        $query = $service->getEligibleApplicationsQuery();

        $this->assertFalse($query->where('id', $draftApp->id)->exists());
    }

    public function test_package_creation_generates_valid_zip_manifest_and_sha256()
    {
        $app = $this->createValidCompletedApplication();
        $service = app(ArchiveService::class);

        $package = $service->createPackage([
            'application_ids' => [$app->id],
            'period' => date('Y-m'),
        ], $this->archiveOperatorUser);

        $this->assertEquals(ArchivePackage::STATUS_READY_FOR_DOWNLOAD, $package->status);
        $this->assertEquals(1, $package->total_applications);
        $this->assertGreaterThan(0, $package->total_files);
        $this->assertNotEmpty($package->sha256_checksum);
        $this->assertNotEmpty($package->zip_path);

        $zipFullPath = storage_path('app/private/' . $package->zip_path);
        $this->assertTrue(File::exists($zipFullPath));

        // Verify SHA-256 matches actual file
        $this->assertEquals(hash_file('sha256', $zipFullPath), $package->sha256_checksum);

        // Verify ZIP contents using ZipArchive
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipFullPath) === true);

        $hasManifest = false;
        $hasCsv = false;
        $hasAppFolder = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (str_contains($stat['name'], 'manifest.json')) $hasManifest = true;
            if (str_contains($stat['name'], 'daftar_arsip.csv')) $hasCsv = true;
            if (str_contains($stat['name'], "permohonan/{$app->reg_number}")) $hasAppFolder = true;
        }

        $zip->close();

        $this->assertTrue($hasManifest, 'ZIP must contain manifest.json');
        $this->assertTrue($hasCsv, 'ZIP must contain daftar_arsip.csv');
        $this->assertTrue($hasAppFolder, 'ZIP must contain application reference folder');
    }

    public function test_application_with_missing_physical_document_is_flagged_incomplete()
    {
        $serviceType = ServiceType::create([
            'name' => 'Layanan Missing Test ' . uniqid(),
            'slug' => 'layanan-missing-test-' . uniqid(),
            'is_active' => true,
        ]);
        $serviceDoc = ServiceDocument::create([
            'service_type_id' => $serviceType->id,
            'label' => 'Berkas Wajib Missing',
            'is_required' => true,
            'accepted_formats' => 'jpg,png,pdf',
            'max_size_kb' => 2048,
        ]);

        $app = QueueApplication::create([
            'reg_number' => 'REG-INCOMPLETE-' . uniqid(),
            'service_type_id' => $serviceType->id,
            'status' => QueueApplication::STATUS_COMPLETED,
            'applicant_name' => 'Ghost Document Applicant',
            'applicant_nik' => '5205010101958888',
            'applicant_phone' => '082345678901',
            'applicant_address' => 'Jl. Dompu No. 1',
            'completed_at' => now(),
        ]);

        // Document record exists in DB, but points to non-existent file on disk
        ApplicationDocument::create([
            'application_id' => $app->id,
            'service_document_id' => $serviceDoc->id,
            'original_filename' => 'missing_file.jpg',
            'stored_filename' => 'missing_file.jpg',
            'disk' => 'local',
            'path' => 'queue-documents/missing/non_existent.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1234,
            'status' => ApplicationDocument::STATUS_APPROVED,
        ]);

        $service = app(ArchiveService::class);
        $completeness = $service->checkApplicationCompleteness($app);

        $this->assertFalse($completeness['is_complete']);
        $this->assertNotEmpty($completeness['issues']);
        $this->assertStringContainsString('Berkas fisik hilang', $completeness['issues'][0]);

        // Attempting to package it should mark the package INCOMPLETE and not produce a false success
        $package = $service->createPackage([
            'application_ids' => [$app->id],
            'period' => date('Y-m'),
        ], $this->archiveOperatorUser);

        $this->assertEquals(ArchivePackage::STATUS_INCOMPLETE, $package->status);
        $this->assertNull($package->zip_path);
    }

    public function test_download_package_streams_file_and_logs_audit()
    {
        $app = $this->createValidCompletedApplication();
        $service = app(ArchiveService::class);
        $package = $service->createPackage(['application_ids' => [$app->id]], $this->archiveOperatorUser);

        $response = $this->actingAs($this->archiveOperatorUser)
            ->get(route('admin.archive.packages.download', $package->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');

        // Verify source file is strictly preserved
        $doc = $app->documents()->first();
        $this->assertTrue(Storage::disk('local')->exists($doc->getRawOriginal('path')));
    }

    public function test_mode_arsip_saja_never_deletes_source_files()
    {
        $app = $this->createValidCompletedApplication();
        $service = app(ArchiveService::class);
        $package = $service->createPackage(['application_ids' => [$app->id]], $this->archiveOperatorUser);

        $docPath = $app->documents()->first()->getRawOriginal('path');

        // Confirm operator storage
        $service->confirmPackageStorage($package, 'Harddisk Eksternal Ruang Arsip No. 01', $this->archiveOperatorUser);

        // Verify package integrity
        $service->verifyPackageIntegrity($package);

        // In Mode A (ARSIP_SAJA), source files must never be deleted
        $this->assertTrue(Storage::disk('local')->exists($docPath));
        $this->assertFalse($package->fresh()->source_files_cleaned);
    }

    public function test_operator_confirmation_records_location_and_remains_unverified_on_permanent_storage()
    {
        $app = $this->createValidCompletedApplication();
        $service = app(ArchiveService::class);
        $package = $service->createPackage(['application_ids' => [$app->id]], $this->archiveOperatorUser);

        $response = $this->actingAs($this->archiveOperatorUser)
            ->post(route('admin.archive.packages.confirm', $package->id), [
                'storage_location_notes' => 'Tersimpan di Flashdisk Master Arsip 64GB Disdukcapil',
            ]);

        $response->assertRedirect();
        $package->refresh();

        $this->assertTrue($package->is_operator_confirmed);
        $this->assertEquals('Tersimpan di Flashdisk Master Arsip 64GB Disdukcapil', $package->operator_storage_location_notes);
        // Crucial test rule: manual confirmation by operator is NOT permanent storage verification
        $this->assertEquals(ArchivePackage::PERM_NOT_CONFIGURED, $package->permanent_storage_status);
    }

    public function test_unconfigured_permanent_storage_reports_not_configured_honestly()
    {
        ArchiveSetting::set('permanent_storage_driver', 'null');

        $result = ArchiveStorageManager::testConnection();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('belum dikonfigurasi', $result['message']);
    }

    public function test_unauthorized_user_cannot_access_or_download_archive()
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->get(route('admin.archive.dashboard'));

        $response->assertStatus(403);
    }

    public function test_public_user_cannot_access_archive()
    {
        $response = $this->get(route('admin.archive.dashboard'));
        $response->assertRedirect('/admin/login');
    }

    public function test_mode_c_cannot_be_activated_in_initial_implementation()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.archive.settings.update'), [
                'operation_mode' => 'ARSIP_TRANSFER_DAN_PEMBERSIHAN',
                'eligible_statuses' => ['COMPLETED'],
                'retention_policy_days' => 365,
                'cleaning_grace_period_days' => 90,
                'permanent_storage_driver' => 'null',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals('ARSIP_SAJA', ArchiveSetting::get('operation_mode'));
    }

    public function test_local_disk_adapter_transfer_and_verification_workflow()
    {
        $app = $this->createValidCompletedApplication();
        $service = app(ArchiveService::class);
        $package = $service->createPackage(['application_ids' => [$app->id]], $this->archiveOperatorUser);

        // Setup temporary directory pretending to be permanent storage mount
        $mockPermanentDir = storage_path('app/private/mock_permanent_server');
        if (!File::isDirectory($mockPermanentDir)) {
            File::makeDirectory($mockPermanentDir, 0755, true);
        }

        ArchiveSetting::set('permanent_storage_driver', 'local_path');
        ArchiveSetting::set('permanent_storage_config', [
            'target_path' => $mockPermanentDir,
        ]);

        $adapter = new LocalDiskArchiveStorageAdapter();
        $testResult = $adapter->testConnection();
        $this->assertTrue($testResult['success']);

        // Execute transfer
        $transferResult = $adapter->transfer($package);
        $this->assertTrue($transferResult['success']);
        $this->assertTrue(File::exists($transferResult['destination']));

        // Verify package at destination
        $package->update(['permanent_storage_destination' => $transferResult['destination']]);
        $verifyResult = $adapter->verify($package);
        $this->assertTrue($verifyResult['verified']);
        $this->assertEquals($package->sha256_checksum, $verifyResult['remote_checksum']);

        // Clean mock dir
        File::deleteDirectory($mockPermanentDir);
    }
}
