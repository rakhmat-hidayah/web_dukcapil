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
    protected $signature = 'queue:purge-expired-drafts {--minutes= : Batas usia permohonan draft dalam menit (default dari pengaturan sistem)} {--hours= : Batas usia permohonan draft dalam jam}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus otomatis permohonan pendaftaran online KTP-el berstatus DRAFT yang melebihi batas waktu tunggu pendaftaran (dari pengaturan) beserta berkas fisiknya.';

    /**
     * Execute the console command.
     */
    public function handle(QueueApplicationService $service): int
    {
        $minutes = null;
        if ($this->option('minutes') !== null) {
            $minutes = (int) $this->option('minutes');
        } elseif ($this->option('hours') !== null) {
            $minutes = ((int) $this->option('hours')) * 60;
        }

        $effectiveMinutes = $minutes ?? (int) \App\Models\Queue\QueueSetting::get('queue.draft_timeout_minutes', 15);

        $this->info("Memulai pembersihan permohonan draft KTP-el yang melebihi batas waktu tunggu {$effectiveMinutes} menit...");

        $count = $service->purgeExpiredDrafts($minutes);

        $this->info("Selesai. Sebanyak {$count} permohonan draft berhasil dihapus otomatis dan ruang penyimpanan dibersihkan.");

        return Command::SUCCESS;
    }
}
