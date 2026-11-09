<?php

namespace App\Jobs\Queue;

use App\Models\Queue\QueueSelfieVerification;
use App\Services\Queue\QueueSelfieVerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteSelfieFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 60;

    public function __construct(
        public int $verificationId
    ) {}

    public function handle(QueueSelfieVerificationService $service): void
    {
        $verification = QueueSelfieVerification::with(['application.documents', 'document'])
            ->find($this->verificationId);

        if (!$verification || $verification->deletion_status === QueueSelfieVerification::DELETION_DELETED) {
            return;
        }

        $success = $service->deletePhysicalSelfie($verification);

        if (!$success) {
            Log::warning("DeleteSelfieFileJob retry attempted for verification ID {$this->verificationId}");
            if ($this->attempts() >= $this->tries) {
                Log::critical("DeleteSelfieFileJob reached maximum retries for verification ID {$this->verificationId}. Manual admin intervention required.");
            }
        }
    }
}
