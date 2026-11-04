<?php

namespace App\Notifications\Queue;

use App\Models\Queue\QueueApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public QueueApplication $application) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusUrl = route('queue.status', ['token' => $this->application->access_token]);
        $serviceName = $this->application->serviceType ? $this->application->serviceType->name : 'Pelayanan KTP-el';
        $slot = $this->application->slot;
        $schedule = $slot?->schedule;

        $mail = (new MailMessage)
            ->subject("Nomor Antrean Terbit & Berkas Diterima [{$this->application->reg_number}]")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Pendaftaran pelayanan kependudukan Anda telah berhasil diterima di sistem Disdukcapil Kabupaten Dompu.")
            ->line("**Nomor Registrasi Permohonan:** `{$this->application->reg_number}`")
            ->line("**Jenis Pelayanan:** {$serviceName}");

        if ($this->application->queue_number) {
            $scheduleDate = $schedule?->schedule_date_formatted ?? '-';
            $hours = $schedule?->operational_hours_formatted ?? '08:00 - 15:30 WITA';
            $mail->line("**Nomor Antrean Resmi:** `{$this->application->queue_number}`")
                 ->line("**Jadwal Kedatangan Pelayanan:** {$scheduleDate} ({$hours})")
                 ->line("Nomor antrean resmi Anda telah diterbitkan terlebih dahulu agar Anda mendapatkan kepastian nomor dan jadwal pelayanan.")
                 ->line("Saat ini berkas dokumen persyaratan digital Anda sedang dalam tahap **Pra-Verifikasi Petugas**.");
        } else {
            $mail->line("Saat ini berkas dokumen persyaratan digital Anda sedang berada dalam tahap **Pra-Verifikasi Petugas**.");
        }

        return $mail->action('Lihat Tiket Antrean & Pantau Dokumen', $statusUrl)
            ->line("Harap simpan bukti pendaftaran dan nomor antrean ini. Bawa dokumen fisik asli saat datang ke loket Disdukcapil sesuai jadwal yang ditentukan.")
            ->line("Anda juga dapat mengakses seluruh riwayat permohonan Anda kapan saja melalui portal Disdukcapil.")
            ->salutation("Salam hangat,\nTim Pelayanan Disdukcapil Kabupaten Dompu");
    }
}
