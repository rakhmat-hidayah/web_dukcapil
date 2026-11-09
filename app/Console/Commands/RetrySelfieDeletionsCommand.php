<?php

namespace App\Console\Commands;

use App\Services\Queue\QueueSelfieVerificationService;
use Illuminate\Console\Command;

class RetrySelfieDeletionsCommand extends Command
{
    protected $signature = 'queue:retry-selfie-deletions';
    protected $description = 'Retry any pending or failed applicant selfie file physical deletions from storage';

    public function handle(QueueSelfieVerificationService $service): int
    {
        $this->info('Memulai pengecekan berkas foto selfie yang tertunda dihapus...');
        $count = $service->retryFailedDeletions();
        $this->info("Berhasil memproses pemusnahan {$count} berkas foto selfie.");

        return Command::SUCCESS;
    }
}
