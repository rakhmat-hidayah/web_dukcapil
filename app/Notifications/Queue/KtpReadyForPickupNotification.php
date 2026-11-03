<?php

namespace App\Notifications\Queue;

use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSetting;
use App\Services\ConfigurationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KtpReadyForPickupNotification extends Notification
{
    use Queueable;

    public function __construct(public QueueApplication $application) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->application->loadMissing(['serviceType', 'handover']);
        $statusUrl = route('queue.status', ['token' => $this->application->access_token]);
        $serviceName = $this->application->serviceType?->name ?: 'Pelayanan KTP-el';
        $notes = $this->application->handover?->ready_notes ?: $this->application->notes;

        $officeAddress = ConfigurationService::get('office_address')
            ?: QueueSetting::get('queue.office_address', 'Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211');
        $officeWorkHours = ConfigurationService::get('office_work_hours');

        $mail = (new MailMessage)
            ->subject("🪪 Fisik KTP-el Anda Selesai Dicetak & Siap Diambil — Disdukcapil Dompu")
            ->greeting("Kabar Baik, Bapak/Ibu {$this->application->applicant_name}!")
            ->line("Fisik kartu Tanda Penduduk Elektronik (KTP-el) Anda telah **selesai dicetak** dan saat ini **siap untuk diambil** di loket penyerahan kantor Disdukcapil Kabupaten Dompu.")
            ->line("Berikut rincian informasi pengambilan dokumen Anda:")
            ->line("• **Nama Pemilik KTP:** {$this->application->applicant_name}")
            ->line("• **Nomor Antrean:** `" . ($this->application->queue_number ?: $this->application->reg_number) . "`")
            ->line("• **Nomor Registrasi:** `{$this->application->reg_number}`")
            ->line("• **Jenis Layanan:** {$serviceName}")
            ->line("• **Lokasi Pengambilan:** Loket Penyerahan / Serah Terima KTP-el, Kantor Disdukcapil Kabupaten Dompu ({$officeAddress})");

        if (!empty($officeWorkHours)) {
            $hoursParts = array_filter(array_map('trim', explode('|', $officeWorkHours)));
            if (count($hoursParts) > 1) {
                $mail->line("• **Jam Operasional Loket Pengambilan:**");
                foreach ($hoursParts as $part) {
                    $mail->line("  - {$part}");
                }
            } else {
                $mail->line("• **Jam Operasional Loket Pengambilan:** {$officeWorkHours}");
            }
        } else {
            $mail->line("• **Jam Operasional Loket Pengambilan:**")
                ->line("  - **Senin s/d Kamis:** 08.00 - 16.00 WITA")
                ->line("  - **Jumat:** 08.00 - 16.30 WITA");
        }

        if ($notes) {
            $mail->line("• **Catatan Petugas:** *\"{$notes}\"*");
        }

        return $mail
            ->action('Lihat Status & Tanda Pengambilan', $statusUrl)
            ->line("**Dokumen Fisik yang Wajib Dibawa Saat Pengambilan:**")
            ->line("1. Menunjukkan email pemberitahuan ini atau bukti nomor antrean/tiket online.")
            ->line("2. Menyerahkan **KTP-el Lama Asli** (untuk layanan Perubahan Data / Cetak Rusak), atau **Surat Keterangan Kehilangan Asli dari Kepolisian** (untuk layanan Cetak Hilang).")
            ->line("3. Membawa **Kartu Keluarga (KK) Asli / Fotokopi**.")
            ->line("4. *Khusus jika pengambilan diwakilkan:* Penerima kuasa wajib membawa **Surat Kuasa Asli (Formulir F-1.07)** bermeterai Rp 10.000 dan **KTP-el Asli Penerima Kuasa**.")
            ->salutation("Salam hangat,\nTim Pelayanan KTP-el Disdukcapil Kabupaten Dompu");
    }
}
