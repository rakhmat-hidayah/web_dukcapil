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

        return (new MailMessage)
            ->subject("Pengajuan Pelayanan KTP-el Diterima [{$this->application->reg_number}]")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Pendaftaran pelayanan kependudukan Anda telah berhasil diterima di sistem Disdukcapil Kabupaten Dompu.")
            ->line("**Nomor Registrasi Permohonan:** `{$this->application->reg_number}`")
            ->line("**Jenis Pelayanan:** {$serviceName}")
            ->line("Saat ini berkas dokumen persyaratan digital Anda sedang berada dalam tahap **Pra-Verifikasi Petugas**.")
            ->action('Pantau Status & Dokumen', $statusUrl)
            ->line("Nomor antrean dan jadwal kedatangan akan diterbitkan secara otomatis setelah seluruh dokumen diverifikasi dan disetujui.")
            ->line("Anda juga dapat mengakses seluruh riwayat permohonan Anda kapan saja melalui website Disdukcapil.")
            ->salutation("Salam hangat,\nTim Pelayanan Disdukcapil Kabupaten Dompu");
    }
}
