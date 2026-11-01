<?php

namespace App\Console\Commands;

use App\Services\Queue\QueueApplicationService;
use Illuminate\Console\Command;

class PurgeExpiredDraftApplicationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:purge-expired-drafts {--hours=24 : Batas usia permohonan berstatus draft dalam jam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus otomatis permohonan pendaftaran online KTP-el berstatus DRAFT yang berusia lebih dari 1x24 jam beserta berkas fisiknya.';

    /**
     * Execute the console command.
     */
    public function handle(QueueApplicationService $service): int
    {
        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $hours = 24;
        }

        $this->info("Memulai pembersihan permohonan draft KTP-el yang berusia lebih dari {$hours} jam...");

        $count = $service->purgeExpiredDrafts($hours);

        $this->info("Selesai. Sebanyak {$count} permohonan draft berhasil dihapus otomatis dan ruang penyimpanan dibersihkan.");

        return Command::SUCCESS;
    }
}
