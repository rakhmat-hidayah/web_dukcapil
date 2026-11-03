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
        $this->application->loadMissing(['serviceType']);
        $serviceName = $this->application->serviceType?->name ?: 'Pelayanan KTP-el';
        $reason = $this->application->rejection_reason ?: 'Dokumen persyaratan tidak memenuhi ketentuan peraturan administrasi kependudukan yang berlaku.';

        return (new MailMessage)
            ->subject("❌ Pemberitahuan: Permohonan Pelayanan KTP-el Ditolak [{$this->application->reg_number}] — Disdukcapil Dompu")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Setelah dilakukan pemeriksaan menyeluruh terhadap berkas yang diajukan, kami memberitahukan bahwa permohonan pendaftaran pelayanan KTP-el Anda **belum dapat disetujui**.")
            ->line("• **Nomor Registrasi:** `{$this->application->reg_number}`")
            ->line("• **Jenis Layanan:** {$serviceName}")
            ->line("• **Alasan Penolakan dari Petugas:**")
            ->line("> *\"{$reason}\"*")
            ->action('Ajukan Pendaftaran Baru', route('queue.index'))
            ->line("Apabila Anda membutuhkan informasi lebih lanjut mengenai ketentuan persyaratan yang sah, silakan datang langsung ke Loket Layanan Informasi Disdukcapil Kabupaten Dompu atau hubungi nomor WhatsApp helpdesk resmi kami.")
            ->salutation("Hormat kami,\nDisdukcapil Kabupaten Dompu");
    }
}
