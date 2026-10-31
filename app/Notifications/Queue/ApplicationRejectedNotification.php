<?php

namespace App\Notifications\Queue;

use App\Models\Queue\QueueApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public QueueApplication $application) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $reason = $this->application->rejection_reason ?: 'Dokumen tidak memenuhi persyaratan peraturan administrasi kependudukan.';

        return (new MailMessage)
            ->subject("Pemberitahuan Penolakan Pengajuan [{$this->application->reg_number}]")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Setelah dilakukan pemeriksaan menyeluruh, kami memberitahukan bahwa pengajuan pelayanan KTP-el Anda **tidak dapat disetujui**.")
            ->line("**Alasan Penolakan:**")
            ->line("_{$reason}_")
            ->action('Ajukan Pendaftaran Baru', route('queue.index'))
            ->line("Apabila Anda membutuhkan bantuan lebih lanjut, silakan datang langsung ke loket informasi Dinas Dukcapil Kabupaten Dompu.")
            ->salutation("Hormat kami,\nDisdukcapil Kabupaten Dompu");
    }
}
