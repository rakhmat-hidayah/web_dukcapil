<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintNotificationLog;
use App\Notifications\NewComplaintNotification;
use App\Services\ConfigurationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ComplaintNotificationService
{
    /**
     * Get resolved admin notification settings from ConfigurationService (WebsiteSetting table).
     */
    public static function getConfig(): array
    {
        $enabled          = ConfigurationService::get('complaint_notification_email_enabled') === '1';
        $primaryEmail     = trim((string) ConfigurationService::get('complaint_notification_email_primary', ''));
        $additionalEmails = trim((string) ConfigurationService::get('complaint_notification_email_additional', ''));
        $onCreated        = ConfigurationService::get('complaint_notification_email_on_created', '1') === '1';
        $onStatusChanged  = ConfigurationService::get('complaint_notification_email_on_status_changed', '0') === '1';

        // Resolve recipients list safely
        $recipients = [];
        if (filter_var($primaryEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = $primaryEmail;
        }

        if (!empty($additionalEmails)) {
            $parts = array_map('trim', explode(',', $additionalEmails));
            foreach ($parts as $p) {
                if (filter_var($p, FILTER_VALIDATE_EMAIL) && !in_array($p, $recipients)) {
                    $recipients[] = $p;
                }
            }
        }

        return [
            'enabled'           => $enabled,
            'primary_email'     => $primaryEmail,
            'additional_emails' => $additionalEmails,
            'recipients'        => $recipients,
            'on_created'        => $onCreated,
            'on_status_changed' => $onStatusChanged,
        ];
    }

    /**
     * Dispatch asynchronous email notifications for a newly created complaint.
     * Governed by Admin-Managed Configuration via ConfigurationService.
     * Guarantees COMPLAINT FIRST, NOTIFICATION SECOND principle.
     */
    public static function dispatchNewComplaintNotification(Complaint $complaint): bool
    {
        try {
            $config = self::getConfig();

            // 1. Check if notification is enabled in Admin CMS Settings
            if (!$config['enabled']) {
                Log::info("Complaint Notification Disabled in Admin Settings: Ticket {$complaint->ticket_number}");
                return true;
            }

            // 2. Check event trigger setting
            if (!$config['on_created']) {
                Log::info("Complaint Notification Skipped: 'on_created' event disabled for ticket {$complaint->ticket_number}");
                return true;
            }

            // 3. Resolve recipients list
            $recipients = $config['recipients'];
            if (empty($recipients)) {
                Log::warning("Complaint Notification Skipped: No valid recipient emails configured in Admin CMS for ticket {$complaint->ticket_number}");
                return false;
            }

            $successCount = 0;

            foreach ($recipients as $recipient) {
                // Application-level Idempotency Check
                $existingLog = ComplaintNotificationLog::where('complaint_id', $complaint->id)
                    ->where('channel', 'mail')
                    ->where('recipient', $recipient)
                    ->where('notification_type', 'new_complaint')
                    ->whereIn('status', [ComplaintNotificationLog::STATUS_PENDING, ComplaintNotificationLog::STATUS_SENT])
                    ->first();

                if ($existingLog) {
                    Log::info("Complaint Notification Idempotency: Skipped duplicate for ticket {$complaint->ticket_number} -> {$recipient}");
                    $successCount++;
                    continue;
                }

                // Create Audit Log Record with DB Unique Constraint Protection
                try {
                    $log = ComplaintNotificationLog::create([
                        'complaint_id'      => $complaint->id,
                        'channel'           => 'mail',
                        'recipient'         => $recipient,
                        'notification_type' => 'new_complaint',
                        'status'            => ComplaintNotificationLog::STATUS_PENDING,
                    ]);
                } catch (QueryException $qe) {
                    Log::info("Complaint Notification DB Idempotency Guard: Unique constraint prevented duplicate log for ticket {$complaint->ticket_number} -> {$recipient}");
                    $successCount++;
                    continue;
                }

                // Dispatch Queueable Notification with Transaction Awareness (afterCommit)
                Notification::route('mail', $recipient)
                    ->notify(new NewComplaintNotification($complaint, $log->id));

                Log::info("Complaint Notification Dispatched: Ticket {$complaint->ticket_number} -> {$recipient}");
                $successCount++;
            }

            return $successCount > 0;

        } catch (Throwable $e) {
            // NEVER let notification failures break complaint submission
            $sanitizedError = preg_replace('/:[^:@\s]+@/', ':****@', $e->getMessage());
            Log::error("Complaint Notification Exception: {$sanitizedError}", [
                'complaint_id' => $complaint->id ?? null,
                'ticket'       => $complaint->ticket_number ?? null,
            ]);

            return false;
        }
    }

    /**
     * Diagnostic test email execution for target recipient.
     */
    public static function sendTestNotification(?string $recipient = null): array
    {
        $config = self::getConfig();
        $targetEmail = $recipient ?: ($config['primary_email'] ?? null);

        if (empty($targetEmail) || !filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Alamat email penerima tes belum dikonfigurasi atau format tidak valid.',
            ];
        }

        try {
            $complaint = Complaint::latest()->first();

            if (!$complaint) {
                $complaint = Complaint::create([
                    'ticket_number'  => 'DKP-TEST-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'submitter_name' => 'Sistem Pengujian SMTP CMS',
                    'subject'        => 'Uji Coba Notifikasi Email CMS Pengaduan',
                    'message'        => 'Ini adalah email uji coba untuk memverifikasi bahwa konfigurasi Notifikasi Pengaduan di Admin CMS dan SMTP server berjalan dengan lancar.',
                    'status'         => 'pending',
                ]);
            }

            Notification::route('mail', $targetEmail)
                ->notify(new NewComplaintNotification($complaint));

            return [
                'success'   => true,
                'recipient' => $targetEmail,
                'message'   => "Email uji coba berhasil dikirim ke {$targetEmail}.",
            ];
        } catch (Throwable $e) {
            $sanitizedError = preg_replace('/:[^:@\s]+@/', ':****@', $e->getMessage());
            return [
                'success' => false,
                'message' => "Gagal mengirim email tes: {$sanitizedError}",
            ];
        }
    }
}
