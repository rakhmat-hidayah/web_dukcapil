<?php

namespace App\Console\Commands;

use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDeviceAuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixApplicantDoubleHashPasswords extends Command
{
    protected $signature   = 'app:fix-applicant-passwords';
    protected $description = 'Perbaiki bug double-hash password akun pemohon: re-set semua password dengan Hash::make secara langsung (bypass cast hashed), sehingga akun bisa login kembali setelah password direset via "Lupa Password".';

    public function handle(): int
    {
        $this->warn('=== Fix: Bug Double-Hash Password Akun Pemohon ===');
        $this->newLine();
        $this->line('Bug: Pada versi sebelumnya, password di-hash dua kali:');
        $this->line('  1. Hash::make() di controller register');
        $this->line('  2. Cast "hashed" di model ApplicantAccount');
        $this->line('Akibat: semua akun lama tidak bisa login meski NIK & password benar.');
        $this->newLine();

        // Ambil semua akun (termasuk soft-deleted) untuk diagnosis lengkap
        $accounts = ApplicantAccount::withTrashed()->get();
        $this->line("Total akun ditemukan: {$accounts->count()}");
        $this->newLine();

        if ($accounts->isEmpty()) {
            $this->info('Tidak ada akun pemohon di database. Tidak ada yang perlu diperbaiki.');
            return Command::SUCCESS;
        }

        // Tampilkan daftar akun
        $rows = $accounts->map(fn ($a) => [
            $a->id,
            $a->name,
            $a->email_normalized,
            $a->account_status,
            $a->identity_status,
            $a->created_at?->format('d/m/Y H:i'),
            $a->deleted_at ? 'Dihapus' : 'Aktif',
        ])->toArray();

        $this->table(
            ['ID', 'Nama', 'Email', 'Status Akun', 'Status Identitas', 'Dibuat', 'Kondisi'],
            $rows
        );

        $this->newLine();
        $this->warn('PERHATIAN: Semua akun di atas akan diset ulang passwordnya ke password sementara.');
        $this->warn('Password sementara: Dompu@2026');
        $this->warn('Minta setiap user untuk segera mengubah password via "Lupa Password" setelah login.');
        $this->newLine();

        if (!$this->confirm('Lanjutkan reset password semua akun di atas?', false)) {
            $this->line('Dibatalkan.');
            return Command::SUCCESS;
        }

        $tempPassword = 'Dompu@2026';
        $fixed = 0;
        $skipped = 0;

        foreach ($accounts as $acc) {
            if ($acc->deleted_at) {
                $this->line("→ Melewati akun ID {$acc->id} ({$acc->name}) - sudah dihapus (soft-deleted).");
                $skipped++;
                continue;
            }

            // Langsung forceFill ke raw password hash yang benar (TIDAK lewat cast)
            // Cast 'hashed' akan di-bypass karena kita pakai forceFill dengan nilai yang sudah di-hash
            $hashedPassword = Hash::make($tempPassword);
            $acc->forceFill(['password' => $hashedPassword])->saveQuietly();

            // Catat audit log
            ApplicantDeviceAuditLog::logEvent(
                applicantAccountId: $acc->id,
                eventType: 'password_reset_emergency',
                reason: 'Fix bug double-hash password oleh Admin via artisan app:fix-applicant-passwords. Password sementara disetel.'
            );

            $this->info("✅ ID {$acc->id} - {$acc->name} ({$acc->email_normalized}): password direset ke sementara.");
            $fixed++;
        }

        $this->newLine();
        $this->info("Selesai! Total akun diperbaiki: {$fixed}, dilewati: {$skipped}.");
        $this->newLine();
        $this->warn('Langkah berikutnya:');
        $this->line('  1. Hubungi setiap user yang terpengaruh dan minta mereka login dengan password sementara: Dompu@2026');
        $this->line('  2. Minta user segera ubah password melalui fitur Ganti HP / Lupa Password di portal.');
        $this->line('  3. PASTIKAN kode fix sudah di-deploy (pengiriman tanpa Hash::make di controller register).');

        return Command::SUCCESS;
    }
}
