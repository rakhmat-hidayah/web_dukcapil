<?php

namespace App\Console\Commands;

use App\Mail\ApplicantLoginApologyMail;
use App\Models\Applicant\ApplicantAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLoginApologyEmailCommand extends Command
{
    protected $signature = 'app:send-login-apology
                            {--email= : Kirim ke satu alamat email pemohon tertentu}
                            {--nik= : Kirim ke akun dengan NIK tertentu}
                            {--unbound : Kirim ke semua akun aktif yang belum terikat perangkat browser}
                            {--all : Kirim ke seluruh akun pemohon yang berstatus ACTIVE}
                            {--temp-password= : Sertakan kata sandi sementara dalam email (opsional, misal Dompu@2026)}
                            {--dry-run : Simulasi pengiriman tanpa mengirim email fisik}';

    protected $description = 'Kirim email pemberitahuan akun aktif & permohonan maaf atas kendala login kepada pemohon terdaftar';

    /**
     * Daftar email default pemohon yang terdampak (dari verifikasi terkini di sistem)
     */
    protected array $defaultTargetEmails = [
        'myra.al.putra@gmail.com',
        'dahland439@gmail.com',
        'tasnimasrarudin77@gmail.com',
        'ajunzulfar@gmail.com',
        'desasoro22@gmail.com',
        'burhanudinridwan94@gmail.com',
        'ranggawadu@gmail.com',
    ];

    public function handle(): int
    {
        $this->info('========================================================================');
        $this->info('  DISDUKCAPIL KABUPATEN DOMPU - PENGIRIMAN EMAIL INFORMASI & PANDUAN LOGIN');
        $this->info('========================================================================');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $tempPassword = $this->option('temp-password');
        $singleEmail = $this->option('email');
        $singleNik = $this->option('nik');
        $onlyUnbound = $this->option('unbound');
        $allActive = $this->option('all');

        if ($isDryRun) {
            $this->warn('MODE SIMULASI (DRY-RUN): Tidak ada email yang benar-benar dikirim keluar.');
            $this->newLine();
        }

        // Tentukan target akun
        $accounts = collect();

        if ($singleEmail) {
            $acc = ApplicantAccount::where('email_normalized', strtolower(trim($singleEmail)))->first();
            if ($acc) {
                $accounts->push($acc);
            } else {
                // Buat dummy object jika akun di DB belum ada tapi ingin dikirim langsung ke email tersebut
                $accounts->push([
                    'name'       => 'Warga Pemohon',
                    'email'      => strtolower(trim($singleEmail)),
                    'masked_nik' => '5205************',
                ]);
            }
        } elseif ($singleNik) {
            $nikHash = ApplicantAccount::computeNikHash(preg_replace('/\D/', '', $singleNik));
            $acc = ApplicantAccount::where('nik_hash', $nikHash)->first();
            if ($acc) {
                $accounts->push($acc);
            } else {
                $this->error("Akun dengan NIK {$singleNik} tidak ditemukan.");
                return Command::FAILURE;
            }
        } elseif ($allActive) {
            $accounts = ApplicantAccount::where('account_status', ApplicantAccount::STATUS_ACTIVE)->get();
        } elseif ($onlyUnbound) {
            $accounts = ApplicantAccount::where('account_status', ApplicantAccount::STATUS_ACTIVE)
                ->whereDoesntHave('devices', function ($q) {
                    $q->whereNull('revoked_at');
                })->get();
        } else {
            // Mode Default: Cari akun berdasarkan daftar email terdampak
            $dbAccounts = ApplicantAccount::whereIn('email_normalized', $this->defaultTargetEmails)->get();
            $foundEmails = $dbAccounts->pluck('email_normalized')->toArray();

            foreach ($dbAccounts as $acc) {
                $accounts->push($acc);
            }

            // Jika ada email di list default yang belum ada di DB lokal/staging, tambahkan fallback
            foreach ($this->defaultTargetEmails as $email) {
                if (!in_array($email, $foundEmails)) {
                    $accounts->push([
                        'name'       => $this->guessNameFromEmail($email),
                        'email'      => $email,
                        'masked_nik' => '5205************',
                    ]);
                }
            }
        }

        if ($accounts->isEmpty()) {
            $this->warn('Tidak ada akun pemohon yang cocok dengan kriteria yang dipilih.');
            return Command::SUCCESS;
        }

        $this->info("Total target penerima email: {$accounts->count()} pemohon");
        $this->newLine();

        // Tampilkan tabel ringkasan
        $tableRows = [];
        foreach ($accounts as $acc) {
            $isObj = $acc instanceof ApplicantAccount;
            $tableRows[] = [
                $isObj ? $acc->name : $acc['name'],
                $isObj ? $acc->email_normalized : $acc['email'],
                $isObj ? $acc->masked_nik : $acc['masked_nik'],
                $isObj ? $acc->account_status : 'STANDBY',
                $isObj ? ($acc->activeDevice ? 'Terikat' : 'Belum Terikat') : '-',
            ];
        }

        $this->table(['Nama Pemohon', 'Alamat Email', 'NIK Masked', 'Status Akun', 'Perangkat'], $tableRows);
        $this->newLine();

        if (!$isDryRun && !$this->confirm('Kirim email informasi & permohonan maaf sekarang?', true)) {
            $this->warn('Pengiriman dibatalkan.');
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        foreach ($accounts as $acc) {
            $isObj = $acc instanceof ApplicantAccount;
            $recipientEmail = $isObj ? $acc->email_normalized : $acc['email'];
            $recipientName = $isObj ? $acc->name : $acc['name'];

            if ($isDryRun) {
                $this->line(" [DRY-RUN] Disimulasikan kirim ke: {$recipientEmail} ({$recipientName})");
                $successCount++;
                $bar->advance();
                continue;
            }

            try {
                Mail::to($recipientEmail)->send(new ApplicantLoginApologyMail($acc, $tempPassword));
                $successCount++;
                Log::info("Email informasi login & permohonan maaf berhasil dikirim ke pemohon: {$recipientEmail}");
            } catch (\Throwable $e) {
                $failCount++;
                $this->error(" Gagal kirim ke {$recipientEmail}: " . $e->getMessage());
                Log::error("Gagal mengirim email permohonan maaf ke {$recipientEmail}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("========================================================================");
        $this->info("HASIL PENGIRIMAN: {$successCount} Berhasil | {$failCount} Gagal");
        $this->info("========================================================================");

        return Command::SUCCESS;
    }

    protected function guessNameFromEmail(string $email): string
    {
        return match ($email) {
            'myra.al.putra@gmail.com'       => 'MIRA FATRIANA',
            'dahland439@gmail.com'          => 'DAHLAN',
            'tasnimasrarudin77@gmail.com'   => 'TASNIM ASRARUDIN',
            'ajunzulfar@gmail.com'          => 'ZULFARD QAIZAR',
            'desasoro22@gmail.com'          => 'ARIS PERDANA',
            'burhanudinridwan94@gmail.com'  => 'BURHANUDIN',
            'ranggawadu@gmail.com'          => 'TESTING',
            default                         => 'Warga Pemohon',
        };
    }
}
