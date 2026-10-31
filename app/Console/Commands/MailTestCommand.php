<?php

namespace App\Console\Commands;

use App\Services\ComplaintNotificationService;
use Illuminate\Console\Command;

class MailTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complaint:mail-test {recipient? : Optional target email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely test SMTP configuration and send a test complaint notification email without exposing credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("====================================================");
        $this->info("   DIAGNOSTIK KONFIGURASI MAIL & NOTIFIKASI EMAIL   ");
        $this->info("====================================================");

        $mailer     = config('mail.default', 'log');
        $host       = config('mail.mailers.smtp.host', 'N/A');
        $port       = config('mail.mailers.smtp.port', 'N/A');
        $encryption = config('mail.mailers.smtp.scheme', 'tls');
        $username   = config('mail.mailers.smtp.username', 'N/A');
        $hasPassword= !empty(config('mail.mailers.smtp.password'));

        $enabled    = config('complaint.notifications.enabled', false);
        $recipient  = $this->argument('recipient') ?: config('complaint.notifications.email');
        $fromAddr   = config('complaint.notifications.from_address', config('mail.from.address'));

        $this->line("Mailer Driver       : <comment>{$mailer}</comment>");
        $this->line("SMTP Host           : <comment>{$host}</comment>");
        $this->line("SMTP Port           : <comment>{$port}</comment>");
        $this->line("SMTP Username       : <comment>{$username}</comment>");
        $this->line("SMTP Password Config: <comment>" . ($hasPassword ? 'TERSEDIA (Secret)' : 'KOSONG') . "</comment>");
        $this->line("From Address        : <comment>{$fromAddr}</comment>");
        $this->line("Notifikasi Enabled  : <comment>" . ($enabled ? 'YA (Active)' : 'TIDAK (Disabled)') . "</comment>");
        $this->line("Target Recipient    : <comment>" . ($recipient ?: 'BELUM DIKONFIGURASI (KOSONG)') . "</comment>");
        $this->info("----------------------------------------------------");

        if (empty($recipient)) {
            $this->error("❌ GAGAL: COMPLAINT_NOTIFICATION_EMAIL belum dikonfigurasi di environment.");
            return Command::FAILURE;
        }

        $this->info("Mengirim email uji coba ke {$recipient}...");

        $res = ComplaintNotificationService::sendTestNotification($recipient);

        if ($res['success']) {
            $this->info("✅ BERHASIL: " . $res['message']);
            return Command::SUCCESS;
        } else {
            $this->error("❌ GAGAL: " . $res['message']);
            return Command::FAILURE;
        }
    }
}
