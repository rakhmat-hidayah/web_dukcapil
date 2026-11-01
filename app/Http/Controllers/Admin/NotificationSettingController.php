<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintNotificationLog;
use App\Services\ActivityLogger;
use App\Services\ComplaintNotificationService;
use App\Services\ConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class NotificationSettingController extends Controller
{
    /**
     * Display the Notification & Integration System Settings page.
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        
        $config = ComplaintNotificationService::getConfig();

        $logs = ComplaintNotificationLog::with(['complaint:id,ticket_number,subject,submitter_name,is_anonymous,status'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Safe Non-Sensitive SMTP Diagnostic Information
        $smtpInfo = [
            'mailer'         => config('mail.default', 'smtp'),
            'host'           => config('mail.mailers.smtp.host', '127.0.0.1'),
            'port'           => config('mail.mailers.smtp.port', 587),
            'encryption'     => config('mail.mailers.smtp.scheme', 'tls'),
            'username'       => config('mail.mailers.smtp.username', '-'),
            'has_password'   => !empty(config('mail.mailers.smtp.password')),
            'from_address'   => config('mail.from.address', '-'),
            'from_name'      => config('mail.from.name', 'Dukcapil Kabupaten Dompu'),
        ];

        $permissions = [
            'can_manage' => $user->hasAnyRole(['Super Administrator', 'Administrator Utama', 'Website Administrator', 'Administrator Website']) || $user->hasPermissionTo('notifications.manage'),
            'can_test'   => $user->hasAnyRole(['Super Administrator', 'Administrator Utama', 'Website Administrator', 'Administrator Website']) || $user->hasPermissionTo('notifications.test'),
            'can_logs'   => $user->hasAnyRole(['Super Administrator', 'Administrator Utama', 'Website Administrator', 'Administrator Website']) || $user->hasPermissionTo('notifications.logs'),
        ];

        return Inertia::render('Admin/NotificationSettings/Index', [
            'config'      => $config,
            'smtpInfo'    => $smtpInfo,
            'logs'        => $logs,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update Notification System Settings with Spatie ActivityLog audit trail.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Super Administrator', 'Administrator Utama', 'Website Administrator', 'Administrator Website']) && !$user->hasPermissionTo('notifications.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola pengaturan notifikasi.');
        }

        $data = $request->validate([
            'complaint_notification_email_enabled'           => 'required|boolean',
            'complaint_notification_email_primary'           => 'required_if:complaint_notification_email_enabled,true,1|nullable|email|max:255',
            'complaint_notification_email_additional'        => 'nullable|string|max:500',
            'complaint_notification_email_on_created'        => 'nullable|boolean',
            'complaint_notification_email_on_status_changed'  => 'nullable|boolean',
        ], [
            'complaint_notification_email_primary.required_if' => 'Email Penerima Utama wajib diisi jika notifikasi email diaktifkan.',
            'complaint_notification_email_primary.email'       => 'Format email penerima utama tidak valid.',
        ]);

        try {
            ConfigurationService::set('complaint_notification_email_enabled', $data['complaint_notification_email_enabled'] ? '1' : '0', 'notifications', $user->id, 'Update Status Notifikasi Pengaduan');
            ConfigurationService::set('complaint_notification_email_primary', $data['complaint_notification_email_primary'] ?? '', 'notifications', $user->id, 'Update Email Utama Notifikasi');
            ConfigurationService::set('complaint_notification_email_additional', $data['complaint_notification_email_additional'] ?? '', 'notifications', $user->id, 'Update Email Tambahan Notifikasi');
            ConfigurationService::set('complaint_notification_email_on_created', ($data['complaint_notification_email_on_created'] ?? true) ? '1' : '0', 'notifications', $user->id, 'Update Event Notifikasi Pengaduan Baru');
            ConfigurationService::set('complaint_notification_email_on_status_changed', ($data['complaint_notification_email_on_status_changed'] ?? false) ? '1' : '0', 'notifications', $user->id, 'Update Event Notifikasi Ubah Status');

            ActivityLogger::log("Memperbarui Pengaturan Notifikasi Email Pengaduan CMS", null, 'update_notification_settings');

            return redirect()->back()->with('success', 'Pengaturan Notifikasi & Integrasi Email berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Trigger a safe diagnostic test email to the configured recipient.
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Super Administrator', 'Administrator Utama', 'Website Administrator', 'Administrator Website']) && !$user->hasPermissionTo('notifications.test')) {
            abort(403, 'Anda tidak memiliki hak akses untuk menguji notifikasi email.');
        }

        $recipient = $request->input('recipient');
        $res = ComplaintNotificationService::sendTestNotification($recipient);

        if ($res['success']) {
            ActivityLogger::log("Mengirim Email Uji Coba Notifikasi ke [{$res['recipient']}]", null, 'test_email_notification');
            return redirect()->back()->with('success', $res['message']);
        } else {
            return redirect()->back()->withErrors(['test_email' => $res['message']]);
        }
    }
}
