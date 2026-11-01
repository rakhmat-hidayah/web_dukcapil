<?php

namespace App\Http\Controllers\Admin\Archive;

use App\Http\Controllers\Controller;
use App\Models\Archive\ArchiveAuditLog;
use App\Models\Archive\ArchivePackage;
use App\Models\Archive\ArchiveSetting;
use App\Services\Archive\ArchiveAuditService;
use App\Services\Archive\Storage\ArchiveStorageManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArchivePermanentStorageController extends Controller
{
    public function index(Request $request): Response
    {
        $driver = ArchiveSetting::get('permanent_storage_driver', 'null');
        $config = ArchiveSetting::get('permanent_storage_config', []);

        // Hide sensitive credentials in view
        if (isset($config['password'])) {
            $config['password'] = !empty($config['password']) ? '••••••••' : '';
        }

        $adapter = ArchiveStorageManager::resolve();
        $isConfigured = $adapter->isConfigured();

        // Recent permanent storage transfers
        $packages = ArchivePackage::with(['serviceType', 'creator'])
            ->where('permanent_storage_status', '!=', ArchivePackage::PERM_NOT_CONFIGURED)
            ->latest()
            ->paginate(15);

        // Packages ready for transfer
        $readyForTransfer = ArchivePackage::where('status', ArchivePackage::STATUS_READY_FOR_DOWNLOAD)
            ->whereIn('permanent_storage_status', [
                ArchivePackage::PERM_NOT_CONFIGURED,
                ArchivePackage::PERM_PENDING_TRANSFER,
                ArchivePackage::PERM_TRANSFER_FAILED,
            ])
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Archive/PermanentStorage', [
            'driver' => $driver,
            'config' => $config,
            'isConfigured' => $isConfigured,
            'packages' => $packages,
            'readyForTransfer' => $readyForTransfer,
        ]);
    }

    public function testConnection(): JsonResponse|RedirectResponse
    {
        $result = ArchiveStorageManager::testConnection();

        // Update last test result in setting
        $config = ArchiveSetting::get('permanent_storage_config', []);
        $config['last_tested_at'] = now()->toIso8601String();
        $config['last_test_result'] = $result;
        ArchiveSetting::set('permanent_storage_config', $config);

        ArchiveAuditService::log('STORAGE_CONNECTION_TESTED', null, null, [
            'result' => $result,
        ]);

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function transfer(int $packageId): RedirectResponse
    {
        $package = ArchivePackage::findOrFail($packageId);

        $result = ArchiveStorageManager::transfer($package);

        if ($result['success']) {
            $package->update([
                'permanent_storage_status' => ArchivePackage::PERM_TRANSFERRED,
                'permanent_storage_destination' => $result['destination'],
            ]);

            ArchiveAuditService::log('TRANSFER_EXECUTED', $package->id, null, [
                'destination' => $result['destination'],
                'bytes_transferred' => $result['bytes_transferred'],
            ]);

            return back()->with('success', $result['message']);
        }

        $package->update([
            'permanent_storage_status' => ArchivePackage::PERM_TRANSFER_FAILED,
        ]);

        ArchiveAuditService::log('TRANSFER_FAILED', $package->id, null, [
            'error' => $result['message'],
        ]);

        return back()->with('error', $result['message']);
    }

    public function verify(int $packageId): RedirectResponse
    {
        $package = ArchivePackage::findOrFail($packageId);

        $result = ArchiveStorageManager::verify($package);

        if ($result['verified']) {
            $package->update([
                'permanent_storage_status' => ArchivePackage::PERM_VERIFIED,
                'permanent_storage_verified_at' => now(),
                'permanent_storage_checksum' => $result['remote_checksum'],
            ]);

            ArchiveAuditService::log('TRANSFER_VERIFIED', $package->id, null, [
                'remote_checksum' => $result['remote_checksum'],
            ]);

            return back()->with('success', $result['message']);
        }

        $package->update([
            'permanent_storage_status' => ArchivePackage::PERM_VERIFICATION_FAILED,
        ]);

        ArchiveAuditService::log('TRANSFER_VERIFICATION_FAILED', $package->id, null, [
            'error' => $result['message'],
        ]);

        return back()->with('error', $result['message']);
    }
}
