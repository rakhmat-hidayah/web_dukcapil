<?php

namespace App\Services\Queue;

use App\Models\Queue\NotificationLog;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSetting;
use App\Notifications\Queue\ApplicationApprovedNotification;
use App\Notifications\Queue\ApplicationRejectedNotification;
use App\Notifications\Queue\ApplicationSubmittedNotification;
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
        $settingKey = "queue.notify_on_{$event}";
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
            'revision', 'revision_required' => new RevisionRequiredNotification($application),
            'approved', 'queue_booked' => new ApplicationApprovedNotification($application),
            'rejected' => new ApplicationRejectedNotification($application),
            default => null,
        };
    }
}
