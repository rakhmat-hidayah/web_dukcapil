<?php

namespace App\Services\Queue;

use App\Models\Queue\NotificationLog;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSetting;
use App\Notifications\Queue\ApplicationApprovedNotification;
use App\Notifications\Queue\ApplicationPreVerifiedNotification;
use App\Notifications\Queue\ApplicationRejectedNotification;
use App\Notifications\Queue\ApplicationSubmittedNotification;
use App\Notifications\Queue\KtpReadyForPickupNotification;
use App\Notifications\Queue\RevisionRequiredNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class QueueNotificationService
{
    /**
     * Dispatch notification with full fail-safe isolation:
     * Mail failure will NEVER roll back or abort the database transaction.
     */
    public function dispatch(QueueApplication $application, string $event): void
    {
        $settingMap = [
            'submit'           => 'queue.notify_on_submit',
            'submitted'        => 'queue.notify_on_submit',
            'pre_verified'     => 'queue.notify_on_pre_verified',
            'revision'         => 'queue.notify_on_revision',
            'revision_required'=> 'queue.notify_on_revision',
            'approved'         => 'queue.notify_on_approved',
            'queue_booked'     => 'queue.notify_on_approved',
            'number_issued'    => 'queue.notify_on_approved',
            'rejected'         => 'queue.notify_on_rejected',
            'ready_for_pickup' => 'queue.notify_on_ready_for_pickup',
            'ready'            => 'queue.notify_on_ready_for_pickup',
        ];

        $settingKey = $settingMap[$event] ?? "queue.notify_on_{$event}";
        // Fallback pre_verified notification to default enabled ('1')
        $isEnabled = QueueSetting::get($settingKey, '1') === '1';

        $recipient = $application->applicant_email;

        // Create log record first
        $log = NotificationLog::create([
            'application_id' => $application->id,
            'channel'        => 'email',
            'recipient'      => $recipient ?: 'N/A (No Email)',
            'event_type'     => $event,
            'status'         => 'skipped',
        ]);

        if (!$isEnabled || empty($recipient)) {
            return;
        }

        try {
            $notification = $this->resolveNotification($event, $application);
            if (!$notification) {
                return;
            }

            Notification::route('mail', $recipient)->notify($notification);

            $log->update(['status' => 'sent']);
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);

            // Safely log error without aborting execution
            Log::warning("Queue notification failure [{$application->reg_number}][{$event}]: " . $e->getMessage());
        }
    }

    private function resolveNotification(string $event, QueueApplication $application): mixed
    {
        return match ($event) {
            'submit', 'submitted' => new ApplicationSubmittedNotification($application),
            'pre_verified' => new ApplicationPreVerifiedNotification($application),
            'revision', 'revision_required' => new RevisionRequiredNotification($application),
            'approved', 'queue_booked', 'number_issued' => new ApplicationApprovedNotification($application),
            'rejected' => new ApplicationRejectedNotification($application),
            'ready_for_pickup', 'ready' => new KtpReadyForPickupNotification($application),
            default => null,
        };
    }
}
