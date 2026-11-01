<?php

namespace App\Http\Controllers\Admin\Archive;

use App\Http\Controllers\Controller;
use App\Models\Archive\ArchiveSetting;
use App\Models\Queue\QueueApplication;
use App\Services\Archive\ArchiveAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'operation_mode' => ArchiveSetting::get('operation_mode', 'ARSIP_SAJA'),
            'eligible_statuses' => ArchiveSetting::get('eligible_statuses', ['COMPLETED']),
            'retention_policy_days' => ArchiveSetting::get('retention_policy_days', 365),
            'cleaning_grace_period_days' => ArchiveSetting::get('cleaning_grace_period_days', 90),
            'permanent_storage_driver' => ArchiveSetting::get('permanent_storage_driver', 'null'),
            'permanent_storage_config' => ArchiveSetting::get('permanent_storage_config', [
                'driver' => 'null',
                'name' => 'Penyimpanan Permanen Instansi',
                'host' => '',
                'port' => 22,
                'username' => '',
                'target_path' => '',
            ]),
        ];

        // Mask password if present
        if (isset($settings['permanent_storage_config']['password'])) {
            $settings['permanent_storage_config']['password'] = !empty($settings['permanent_storage_config']['password']) ? '••••••••' : '';
        }

        $allStatuses = [
            QueueApplication::STATUS_COMPLETED => 'Pelayanan Selesai (KTP Diserahkan)',
            QueueApplication::STATUS_APPROVED_FOR_SERVICE => 'Fisik Cocok (Disetujui Layanan)',
            QueueApplication::STATUS_PRE_VERIFIED => 'Terverifikasi Online (Siap Pilih Jadwal)',
        ];

        return Inertia::render('Admin/Archive/Settings', [
            'settings' => $settings,
            'allStatuses' => $allStatuses,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operation_mode' => 'required|in:ARSIP_SAJA,ARSIP_DAN_TRANSFER,ARSIP_TRANSFER_DAN_PEMBERSIHAN',
            'eligible_statuses' => 'required|array|min:1',
            'eligible_statuses.*' => 'string|in:' . implode(',', QueueApplication::STATUSES),
            'retention_policy_days' => 'required|integer|min:30|max:3650',
            'cleaning_grace_period_days' => 'required|integer|min:30|max:1825',
            'permanent_storage_driver' => 'required|in:null,local_path,sftp',
            'permanent_storage_config' => 'nullable|array',
            'permanent_storage_config.host' => 'nullable|string|max:255',
            'permanent_storage_config.port' => 'nullable|integer|min:1|max:65535',
            'permanent_storage_config.username' => 'nullable|string|max:100',
            'permanent_storage_config.password' => 'nullable|string|max:255',
            'permanent_storage_config.target_path' => 'nullable|string|max:500',
        ]);

        // Safety lock: Mode C (ARSIP_TRANSFER_DAN_PEMBERSIHAN) is strictly disabled in initial implementation
        if ($validated['operation_mode'] === 'ARSIP_TRANSFER_DAN_PEMBERSIHAN') {
            return back()->with('error', 'Kebijakan Keamanan: Mode [ARSIP_TRANSFER_DAN_PEMBERSIHAN] dinonaktifkan pada tahap implementasi awal demi mencegah kehilangan data sebelum audit pemulihan instansi selesai.');
        }

        $currentConfig = ArchiveSetting::get('permanent_storage_config', []);
        $newConfig = $validated['permanent_storage_config'] ?? [];

        // Preserve existing password if placeholder was sent
        if (isset($newConfig['password']) && $newConfig['password'] === '••••••••') {
            $newConfig['password'] = $currentConfig['password'] ?? '';
        }

        ArchiveSetting::set('operation_mode', $validated['operation_mode']);
        ArchiveSetting::set('eligible_statuses', $validated['eligible_statuses']);
        ArchiveSetting::set('retention_policy_days', $validated['retention_policy_days']);
        ArchiveSetting::set('cleaning_grace_period_days', $validated['cleaning_grace_period_days']);
        ArchiveSetting::set('permanent_storage_driver', $validated['permanent_storage_driver']);
        ArchiveSetting::set('permanent_storage_config', array_merge($currentConfig, $newConfig));

        ArchiveAuditService::log('SETTINGS_UPDATED', null, null, [
            'operation_mode' => $validated['operation_mode'],
            'driver' => $validated['permanent_storage_driver'],
            'eligible_statuses' => $validated['eligible_statuses'],
        ]);

        return back()->with('success', 'Konfigurasi modul pengarsipan berhasil diperbarui.');
    }
}
