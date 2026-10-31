<?php

namespace App\Notifications\Queue;

use App\Models\Queue\QueueApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionRequiredNotification extends Notification
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
        $notes = $this->application->revision_notes ?: 'Terdapat dokumen yang buram, terpotong, atau tidak sesuai ketentuan.';

        return (new MailMessage)
            ->subject("Perhatian: Dokumen Pengajuan Memerlukan Perbaikan [{$this->application->reg_number}]")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Petugas verifikator kami telah memeriksa berkas pendaftaran pelayanan KTP-el Anda.")
            ->line("Ditemukan beberapa dokumen yang **memerlukan perbaikan / unggah ulang**.")
            ->line("**Catatan Petugas Verifikator:**")
            ->line("_{$notes}_")
            ->action('Unggah Ulang Dokumen Sekarang', $statusUrl)
            ->line("Anda tidak perlu membuat pendaftaran baru. Cukup buka tautan di atas dan unggah ulang dokumen yang ditandai.")
            ->line("Harap perbaiki dalam waktu 3x24 jam agar permohonan Anda dapat segera diproses.")
            ->salutation("Hormat kami,\nTim Verifikasi Disdukcapil Kabupaten Dompu");
    }
}
