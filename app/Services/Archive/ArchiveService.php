<?php

namespace App\Services\Archive;

use App\Models\Archive\ArchiveAuditLog;
use App\Models\Archive\ArchivePackage;
use App\Models\Archive\ArchivePackageItem;
use App\Models\Archive\ArchiveSetting;
use App\Models\Queue\ApplicationDocument;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\ServiceType;
use App\Models\User;
use App\Services\Archive\Storage\ArchiveStorageManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ArchiveService
{
    /**
     * Get query builder for applications eligible for archiving.
     */
    public function getEligibleApplicationsQuery(array $filters = []): Builder
    {
        $eligibleStatuses = ArchiveSetting::get('eligible_statuses', ['COMPLETED']);

        $query = QueueApplication::with(['serviceType', 'documents.serviceDocument'])
            ->whereIn('status', $eligibleStatuses);

        // Exclude applications that are already part of an active/complete archive package
        $alreadyArchivedIds = ArchivePackageItem::whereHas('package', function ($q) {
            $q->whereIn('status', [
                ArchivePackage::STATUS_READY_FOR_DOWNLOAD,
                ArchivePackage::STATUS_PROCESSING,
                ArchivePackage::STATUS_QUEUED,
            ]);
        })->pluck('application_id')->toArray();

        if (!empty($alreadyArchivedIds) && !($filters['include_archived'] ?? false)) {
            $query->whereNotIn('id', $alreadyArchivedIds);
        }

        // Filter by date range (completion date or submission date)
        if (!empty($filters['start_date'])) {
            $query->whereDate('completed_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('completed_at', '<=', $filters['end_date']);
        }

        // Filter by period (YYYY-MM)
        if (!empty($filters['period'])) {
            $query->where('completed_at', 'like', $filters['period'] . '%');
        }

        // Filter by service type
        if (!empty($filters['service_type_id'])) {
            $query->where('service_type_id', $filters['service_type_id']);
        }

        // Search by keyword
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('reg_number', 'like', "%{$s}%")
                  ->orWhere('applicant_name', 'like', "%{$s}%")
                  ->orWhere('applicant_nik', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('completed_at', 'desc');
    }

    /**
     * Check application completeness and physical presence of all required documents on disk.
     */
    public function checkApplicationCompleteness(QueueApplication $application): array
    {
        $isRepresented = $application->is_represented;
        if ($application->handover && in_array(strtolower((string) $application->handover->receiver_type), ['self', 'sendiri'])) {
            $isRepresented = false;
        }

        $requiredDocs = ($application->serviceType?->documents()->where('is_required', true)->get() ?? collect())
            ->filter(function ($req) use ($isRepresented) {
                if ($req->applicable_to === 'representative_only' && !$isRepresented) {
                    return false;
                }
                if ($req->applicable_to === 'self_only' && $isRepresented) {
                    return false;
                }
                if (!$isRepresented) {
                    $label = strtolower($req->label ?? '');
                    if (str_contains($label, 'f-1.07') || str_contains($label, 'f1.07') || str_contains($label, 'kuasa') || str_contains($label, 'dikuasakan') || str_contains($label, 'diwakilkan')) {
                        return false;
                    }
                }
                return true;
            });

        $requiredDocIds = $requiredDocs->pluck('id')->toArray();
        $uploadedDocs = $application->documents;

        $issues = [];
        $validDocuments = [];
        $totalBytes = 0;

        foreach ($requiredDocs as $req) {
            $uploaded = $uploadedDocs->firstWhere('service_document_id', $req->id);

            if (!$uploaded) {
                $issues[] = "Berkas wajib belum diunggah: [{$req->label}]";
                continue;
            }

            $rawPath = $uploaded->getRawOriginal('path');
            if (empty($rawPath) || !Storage::disk('local')->exists($rawPath)) {
                $issues[] = "Berkas fisik hilang dari storage: [{$req->label}] ({$uploaded->original_filename})";
                continue;
            }

            $size = Storage::disk('local')->size($rawPath);
            if ($size === 0) {
                $issues[] = "Berkas fisik rusak/kosong (0 bytes): [{$req->label}]";
                continue;
            }

            $totalBytes += $size;
            $validDocuments[] = [
                'document' => $uploaded,
                'requirement' => $req,
                'size' => $size,
                'path' => $rawPath,
            ];
        }

        // Check optional / additional docs as well
        foreach ($uploadedDocs as $upDoc) {
            if ($upDoc->service_document_id && in_array($upDoc->service_document_id, $requiredDocIds)) {
                continue; // Already handled above
            }

            $rawPath = $upDoc->getRawOriginal('path');
            if (!empty($rawPath) && Storage::disk('local')->exists($rawPath)) {
                $size = Storage::disk('local')->size($rawPath);
                $totalBytes += $size;
                $validDocuments[] = [
                    'document' => $upDoc,
                    'requirement' => $upDoc->serviceDocument,
                    'size' => $size,
                    'path' => $rawPath,
                ];
            }
        }

        return [
            'is_complete' => empty($issues),
            'issues' => $issues,
            'documents' => $validDocuments,
            'total_size_bytes' => $totalBytes,
            'document_count' => count($validDocuments),
        ];
    }

    /**
     * Generate an archive package from selected application IDs or criteria.
     */
    public function createPackage(array $params, ?User $user = null): ArchivePackage
    {
        $user = $user ?? auth()->user();
        $period = $params['period'] ?? now()->format('Y-m');
        $serviceTypeId = !empty($params['service_type_id']) ? (int) $params['service_type_id'] : null;

        // Collect applications
        if (!empty($params['application_ids']) && is_array($params['application_ids'])) {
            $applications = QueueApplication::with(['serviceType.documents', 'documents.serviceDocument'])
                ->whereIn('id', $params['application_ids'])
                ->get();
        } else {
            $query = $this->getEligibleApplicationsQuery([
                'period' => $params['period'] ?? null,
                'start_date' => $params['start_date'] ?? null,
                'end_date' => $params['end_date'] ?? null,
                'service_type_id' => $serviceTypeId,
            ]);
            $applications = $query->limit($params['limit'] ?? 100)->get();
        }

        if ($applications->isEmpty()) {
            throw new \InvalidArgumentException('Tidak ada permohonan yang memenuhi syarat untuk dibuatkan paket arsip.');
        }

        // Determine package code: if single application, include reg_number & applicant_nik
        if ($applications->count() === 1) {
            $singleApp = $applications->first();
            $nikPart = !empty($singleApp->applicant_nik) ? "_{$singleApp->applicant_nik}" : '';
            $baseCode = "ARSIP_{$period}_{$singleApp->reg_number}{$nikPart}";
            $packageCode = $baseCode;
            $c = 1;
            while (ArchivePackage::where('package_code', $packageCode)->exists()) {
                $packageCode = "{$baseCode}_V{$c}";
                $c++;
            }
        } else {
            // Determine next batch sequence for the month
            $currentMonthCount = ArchivePackage::where('archive_period', $period)->count();
            $seq = str_pad($currentMonthCount + 1, 4, '0', STR_PAD_LEFT);
            $packageCode = "ARSIP_PERMOHONAN_{$period}_BATCH-{$seq}";
            while (ArchivePackage::where('package_code', $packageCode)->exists()) {
                $seq = str_pad(++$currentMonthCount, 4, '0', STR_PAD_LEFT);
                $packageCode = "ARSIP_PERMOHONAN_{$period}_BATCH-{$seq}";
            }
        }

        // Validate completeness of all applications
        $incompleteApps = [];
        $appAnalysis = [];

        foreach ($applications as $app) {
            $analysis = $this->checkApplicationCompleteness($app);
            $appAnalysis[$app->id] = $analysis;

            if (!$analysis['is_complete']) {
                $incompleteApps[] = [
                    'reg_number' => $app->reg_number,
                    'issues' => $analysis['issues'],
                ];
            }
        }

        // Initial package record
        $package = ArchivePackage::create([
            'package_code' => $packageCode,
            'archive_period' => $period,
            'service_type_id' => $serviceTypeId,
            'filter_criteria' => $params,
            'status' => !empty($incompleteApps) ? ArchivePackage::STATUS_INCOMPLETE : ArchivePackage::STATUS_PROCESSING,
            'total_applications' => $applications->count(),
            'total_files' => 0,
            'total_uncompressed_bytes' => 0,
            'package_size_bytes' => 0,
            'created_by' => $user?->id,
            'permanent_storage_status' => ArchivePackage::PERM_NOT_CONFIGURED,
            'source_files_cleaned' => false,
            'error_message' => !empty($incompleteApps) 
                ? 'Paket ditandai TIDAK LENGKAP karena terdapat dokumen wajib yang hilang/rusak pada permohonan terkait.' 
                : null,
        ]);

        // Record items in DB
        foreach ($applications as $app) {
            $analysis = $appAnalysis[$app->id];
            ArchivePackageItem::create([
                'archive_package_id' => $package->id,
                'application_id' => $app->id,
                'reg_number' => $app->reg_number,
                'applicant_name_masked' => $app->applicant_name,
                'document_count' => $analysis['document_count'],
                'total_size_bytes' => $analysis['total_size_bytes'],
                'status' => $analysis['is_complete'] ? 'INCLUDED' : 'FAILED',
                'notes' => !empty($analysis['issues']) ? implode('; ', $analysis['issues']) : 'Dokumen lengkap & valid',
            ]);
        }

        // If incomplete, stop packaging into ZIP and return with INCOMPLETE status
        if (!empty($incompleteApps)) {
            ArchiveAuditService::log('PACKAGE_CREATION_FAILED_INCOMPLETE', $package->id, null, [
                'package_code' => $packageCode,
                'reason' => 'Terdapat berkas wajib yang hilang atau rusak pada storage.',
                'incomplete_applications' => $incompleteApps,
            ], $user);

            return $package;
        }

        // Build ZIP Package
        try {
            $this->buildZipArchive($package, $applications, $appAnalysis);

            ArchiveAuditService::log('PACKAGE_CREATED', $package->id, null, [
                'package_code' => $package->package_code,
                'total_applications' => $package->total_applications,
                'total_files' => $package->total_files,
                'package_size_bytes' => $package->package_size_bytes,
                'sha256_checksum' => $package->sha256_checksum,
            ], $user);

            return $package->fresh();
        } catch (\Throwable $e) {
            Log::error("Archive packaging error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $package->update([
                'status' => ArchivePackage::STATUS_FAILED,
                'error_message' => 'Kesalahan pembuatan ZIP: ' . $e->getMessage(),
            ]);

            ArchiveAuditService::log('PACKAGE_CREATION_FAILED', $package->id, null, [
                'package_code' => $packageCode,
                'error' => $e->getMessage(),
            ], $user);

            throw $e;
        }
    }

    /**
     * Build the physical ZIP archive and manifest.
     */
    protected function buildZipArchive(ArchivePackage $package, $applications, array $appAnalysis): void
    {
        $tempDir = storage_path("app/private/temp_archives/{$package->package_code}");
        $archiveDir = storage_path("app/private/archives");

        if (!File::isDirectory($archiveDir)) {
            File::makeDirectory($archiveDir, 0755, true);
        }
        if (File::isDirectory($tempDir)) {
            File::deleteDirectory($tempDir);
        }
        File::makeDirectory($tempDir, 0755, true);

        $zipFileName = "{$package->package_code}.zip";
        $zipFilePath = "{$archiveDir}/{$zipFileName}";

        $manifestFiles = [];
        $manifestApplications = [];
        $csvRows = [];
        $totalFiles = 0;
        $totalUncompressedBytes = 0;

        // CSV Header
        $csvRows[] = [
            'Nomor Registrasi',
            'Nama Pemohon',
            'NIK Ter-masking',
            'Jenis Layanan',
            'Tanggal Selesai',
            'Nama Berkas Arsip',
            'Nama Berkas Asli',
            'Ukuran (Bytes)',
            'SHA-256 File',
        ];

        // Process each application
        foreach ($applications as $app) {
            $appFolderName = !empty($app->applicant_nik)
                ? "{$app->reg_number}_{$app->applicant_nik}"
                : $app->reg_number;
            $appFolderRelative = "permohonan/{$appFolderName}";
            $appFolderFull = "{$tempDir}/{$appFolderRelative}";
            File::makeDirectory($appFolderFull, 0755, true);

            $analysis = $appAnalysis[$app->id];
            $appDocManifests = [];

            $docIndex = 1;
            foreach ($analysis['documents'] as $docItem) {
                /** @var ApplicationDocument $doc */
                $doc = $docItem['document'];
                $sourcePath = storage_path('app/private/' . $docItem['path']);

                if (!File::exists($sourcePath)) {
                    continue;
                }

                $ext = pathinfo($docItem['path'], PATHINFO_EXTENSION) ?: 'jpg';
                $cleanLabel = Str::slug($doc->serviceDocument?->label ?? 'dokumen', '_');
                $destFileName = sprintf('%02d_%s.%s', $docIndex++, $cleanLabel, $ext);
                $destPath = "{$appFolderFull}/{$destFileName}";

                File::copy($sourcePath, $destPath);

                $fileSize = File::size($destPath);
                $fileSha256 = hash_file('sha256', $destPath);
                $zipRelativePath = "{$package->package_code}/{$appFolderRelative}/{$destFileName}";

                $fileInfo = [
                    'relative_path' => $zipRelativePath,
                    'application_ref' => $app->reg_number,
                    'applicant_nik' => $app->applicant_nik,
                    'original_filename' => $doc->original_filename,
                    'archive_filename' => $destFileName,
                    'mime_type' => $doc->mime_type,
                    'size_bytes' => $fileSize,
                    'sha256' => $fileSha256,
                ];

                $manifestFiles[] = $fileInfo;
                $appDocManifests[] = $fileInfo;

                $csvRows[] = [
                    $app->reg_number,
                    $app->applicant_name,
                    $app->masked_nik,
                    $app->serviceType?->name ?? 'KTP-el',
                    $app->completed_at ? $app->completed_at->format('Y-m-d H:i:s') : '-',
                    $destFileName,
                    $doc->original_filename,
                    $fileSize,
                    $fileSha256,
                ];

                $totalFiles++;
                $totalUncompressedBytes += $fileSize;
            }

            // Write metadata.json for this application
            $appMetadata = [
                'reg_number' => $app->reg_number,
                'service_type' => $app->serviceType?->name,
                'service_type_code' => $app->serviceType?->slug,
                'applicant_name' => $app->applicant_name,
                'applicant_nik' => $app->applicant_nik,
                'applicant_nik_masked' => $app->masked_nik,
                'applicant_district' => $app->applicant_district,
                'applicant_village' => $app->applicant_village,
                'attendance_type' => $app->attendance_type,
                'attendance_label' => $app->attendance_label,
                'is_represented' => $app->is_represented,
                'representative_name' => $app->representative_name,
                'representative_relationship' => $app->representative_relationship,
                'status' => $app->status,
                'status_label' => $app->status_label,
                'submitted_at' => $app->submitted_at?->toIso8601String(),
                'approved_at' => $app->approved_at?->toIso8601String(),
                'physical_verified_at' => $app->physical_verified_at?->toIso8601String(),
                'completed_at' => $app->completed_at?->toIso8601String(),
                'archived_at' => now()->toIso8601String(),
                'documents' => $appDocManifests,
            ];

            $metaJsonString = json_encode($appMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            File::put("{$appFolderFull}/metadata.json", $metaJsonString);

            $manifestApplications[] = [
                'reg_number' => $app->reg_number,
                'applicant_name' => $app->applicant_name,
                'applicant_nik' => $app->applicant_nik,
                'applicant_nik_masked' => $app->masked_nik,
                'folder_name' => $appFolderName,
                'service_type' => $app->serviceType?->name,
                'document_count' => count($appDocManifests),
                'completed_at' => $app->completed_at?->toIso8601String(),
            ];
        }

        // Generate Root Manifest JSON
        $manifest = [
            'manifest_version' => '1.0.0',
            'package_code' => $package->package_code,
            'institution' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu',
            'generated_at' => now()->toIso8601String(),
            'archive_period' => $package->archive_period,
            'summary' => [
                'total_applications' => count($manifestApplications),
                'total_document_files' => $totalFiles,
                'total_uncompressed_bytes' => $totalUncompressedBytes,
            ],
            'applications' => $manifestApplications,
            'files' => $manifestFiles,
        ];

        $manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        File::put("{$tempDir}/manifest.json", $manifestJson);

        // Generate Root CSV
        $csvFileHandle = fopen("{$tempDir}/daftar_arsip.csv", 'w');
        fputs($csvFileHandle, "\xEF\xBB\xBF"); // UTF-8 BOM for Microsoft Excel
        foreach ($csvRows as $row) {
            fputcsv($csvFileHandle, $row);
        }
        fclose($csvFileHandle);

        // Create the ZIP archive using native ZipArchive
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Gagal membuka berkas ZIP untuk ditulis pada [{$zipFilePath}].");
        }

        // Recursively add all files in $tempDir to the ZIP archive
        $filesInTemp = File::allFiles($tempDir);
        foreach ($filesInTemp as $file) {
            $relativePath = $package->package_code . '/' . str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($file->getRealPath(), $relativePath);
        }

        $zip->close();

        // Calculate package hash & size
        $packageSizeBytes = File::size($zipFilePath);
        $packageSha256 = hash_file('sha256', $zipFilePath);

        // Clean temporary directory
        File::deleteDirectory($tempDir);

        // Update package in DB
        $package->update([
            'status' => ArchivePackage::STATUS_READY_FOR_DOWNLOAD,
            'total_files' => $totalFiles,
            'total_uncompressed_bytes' => $totalUncompressedBytes,
            'package_size_bytes' => $packageSizeBytes,
            'sha256_checksum' => $packageSha256,
            'zip_path' => "archives/{$zipFileName}",
            'manifest_data' => $manifest,
        ]);
    }

    /**
     * Download an archive package securely and log the activity.
     */
    public function downloadPackage(ArchivePackage $package, User $user): BinaryFileResponse
    {
        $zipFullPath = storage_path('app/private/' . $package->zip_path);

        if (!File::exists($zipFullPath)) {
            abort(404, 'Berkas arsip ZIP tidak ditemukan di server penyimpanan hosting.');
        }

        // Verify hash integrity before download
        $currentHash = hash_file('sha256', $zipFullPath);
        if ($currentHash !== $package->sha256_checksum) {
            $package->update(['status' => ArchivePackage::STATUS_CORRUPTED]);
            ArchiveAuditService::log('PACKAGE_INTEGRITY_COMPROMISED', $package->id, null, [
                'expected_sha256' => $package->sha256_checksum,
                'detected_sha256' => $currentHash,
            ], $user);

            abort(500, 'Integritas berkas arsip rusak (Checksum SHA-256 tidak cocok). Unduhan dibatalkan demi keamanan.');
        }

        // Audit log download
        ArchiveAuditService::log('PACKAGE_DOWNLOADED', $package->id, null, [
            'package_code' => $package->package_code,
            'package_size_bytes' => $package->package_size_bytes,
            'sha256_checksum' => $package->sha256_checksum,
        ], $user);

        $downloadFileName = basename($zipFullPath);

        // If package is for a single application and filename does not yet have NIK, dynamically include it
        if ($package->total_applications === 1) {
            $item = $package->items()->with('application')->first();
            $app = $item?->application;
            if ($app && !empty($app->applicant_nik) && !str_contains($downloadFileName, $app->applicant_nik)) {
                $ext = pathinfo($downloadFileName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($downloadFileName, PATHINFO_FILENAME);
                $downloadFileName = "{$nameWithoutExt}_{$app->applicant_nik}.{$ext}";
            }
        }

        return response()->download($zipFullPath, $downloadFileName, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $downloadFileName . '"',
        ]);
    }

    /**
     * Verify integrity of a package file against the recorded SHA-256 checksum.
     */
    public function verifyPackageIntegrity(ArchivePackage $package): array
    {
        $zipFullPath = storage_path('app/private/' . $package->zip_path);

        if (!File::exists($zipFullPath)) {
            return [
                'valid' => false,
                'message' => 'Berkas ZIP tidak ditemukan pada disk hosting.',
                'calculated_sha256' => null,
            ];
        }

        $calculatedHash = hash_file('sha256', $zipFullPath);
        $isValid = ($calculatedHash === $package->sha256_checksum);

        if (!$isValid) {
            $package->update(['status' => ArchivePackage::STATUS_CORRUPTED]);
        }

        ArchiveAuditService::log('INTEGRITY_VERIFIED', $package->id, null, [
            'is_valid' => $isValid,
            'recorded_sha256' => $package->sha256_checksum,
            'calculated_sha256' => $calculatedHash,
        ]);

        return [
            'valid' => $isValid,
            'recorded_sha256' => $package->sha256_checksum,
            'calculated_sha256' => $calculatedHash,
            'message' => $isValid 
                ? 'Integritas berkas ZIP terverifikasi valid 100% (SHA-256 cocok).' 
                : 'Peringatan: Checksum SHA-256 tidak cocok! Berkas telah mengalami perubahan.',
        ];
    }

    /**
     * Record operator manual confirmation of archiving.
     */
    public function confirmPackageStorage(ArchivePackage $package, string $notes, User $user): void
    {
        $package->update([
            'is_operator_confirmed' => true,
            'operator_confirmed_at' => now(),
            'operator_confirmed_by' => $user->id,
            'operator_storage_location_notes' => trim($notes),
        ]);

        ArchiveAuditService::log('OPERATOR_CONFIRMED', $package->id, null, [
            'package_code' => $package->package_code,
            'notes' => $notes,
            'operator_id' => $user->id,
            'operator_name' => $user->name,
        ], $user);
    }

    /**
     * Compute actual dashboard statistics directly from database and disk.
     */
    public function getDashboardStatistics(): array
    {
        $eligibleStatuses = ArchiveSetting::get('eligible_statuses', ['COMPLETED']);

        // Eligible applications total
        $totalEligibleApplications = QueueApplication::whereIn('status', $eligibleStatuses)->count();

        // Already packaged application IDs
        $archivedAppIds = ArchivePackageItem::whereHas('package', function ($q) {
            $q->where('status', ArchivePackage::STATUS_READY_FOR_DOWNLOAD);
        })->pluck('application_id')->unique()->toArray();

        $archivedApplicationsCount = count($archivedAppIds);
        $unarchivedApplicationsCount = max(0, $totalEligibleApplications - $archivedApplicationsCount);

        // Package counts
        $totalPackages = ArchivePackage::count();
        $readyPackages = ArchivePackage::where('status', ArchivePackage::STATUS_READY_FOR_DOWNLOAD)->count();
        $incompletePackages = ArchivePackage::where('status', ArchivePackage::STATUS_INCOMPLETE)->count();
        $failedPackages = ArchivePackage::where('status', ArchivePackage::STATUS_FAILED)->count();

        // Operator confirmation status
        $awaitingConfirmationCount = ArchivePackage::where('status', ArchivePackage::STATUS_READY_FOR_DOWNLOAD)
            ->where('is_operator_confirmed', false)
            ->count();
        $confirmedCount = ArchivePackage::where('is_operator_confirmed', true)->count();

        // Permanent storage status
        $verifiedPermanentCount = ArchivePackage::where('permanent_storage_status', ArchivePackage::PERM_VERIFIED)->count();
        $pendingTransferCount = ArchivePackage::where('permanent_storage_status', ArchivePackage::PERM_PENDING_TRANSFER)->count();

        // Storage size metrics
        $totalPackageSizeBytes = (int) ArchivePackage::sum('package_size_bytes');
        $totalUncompressedBytes = (int) ArchivePackage::sum('total_uncompressed_bytes');

        // Cleaned source files
        $cleanedFilesCount = 0; // Strictly 0 in ARSIP_SAJA mode

        // Current Operation Mode
        $operationMode = ArchiveSetting::get('operation_mode', 'ARSIP_SAJA');
        $permanentStorageDriver = ArchiveSetting::get('permanent_storage_driver', 'null');

        return [
            'total_eligible_applications' => $totalEligibleApplications,
            'archived_applications_count' => $archivedApplicationsCount,
            'unarchived_applications_count' => $unarchivedApplicationsCount,
            'total_packages' => $totalPackages,
            'ready_packages' => $readyPackages,
            'incomplete_packages' => $incompletePackages,
            'failed_packages' => $failedPackages,
            'awaiting_confirmation_count' => $awaitingConfirmationCount,
            'confirmed_count' => $confirmedCount,
            'verified_permanent_count' => $verifiedPermanentCount,
            'pending_transfer_count' => $pendingTransferCount,
            'total_package_size_bytes' => $totalPackageSizeBytes,
            'total_package_size_formatted' => $this->formatBytes($totalPackageSizeBytes),
            'total_uncompressed_bytes' => $totalUncompressedBytes,
            'total_uncompressed_formatted' => $this->formatBytes($totalUncompressedBytes),
            'cleaned_files_count' => $cleanedFilesCount,
            'operation_mode' => $operationMode,
            'permanent_storage_driver' => $permanentStorageDriver,
            'permanent_storage_configured' => ArchiveStorageManager::resolve()->isConfigured(),
        ];
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}
