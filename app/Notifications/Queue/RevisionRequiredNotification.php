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
        $this->application->loadMissing(['serviceType']);
        $statusUrl = route('queue.status', ['token' => $this->application->access_token]);
        $serviceName = $this->application->serviceType?->name ?: 'Pelayanan KTP-el';
        $notes = $this->application->revision_notes ?: 'Terdapat dokumen yang buram, terpotong, atau belum memenuhi ketentuan administrasi kependudukan.';

        return (new MailMessage)
            ->subject("⚠️ Permohonan Perbaikan Dokumen Persyaratan KTP-el [{$this->application->reg_number}] — Disdukcapil Dompu")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Petugas verifikator Disdukcapil Kabupaten Dompu telah memeriksa dokumen permohonan pelayanan KTP-el Anda.")
            ->line("Ditemukan beberapa berkas persyaratan yang **memerlukan perbaikan / unggah ulang** sebelum antrean dapat diproses lebih lanjut:")
            ->line("• **Nomor Registrasi:** `{$this->application->reg_number}`")
            ->line("• **Jenis Layanan:** {$serviceName}")
            ->line("• **Catatan & Instruksi Perbaikan dari Petugas:**")
            ->line("> *\"{$notes}\"*")
            ->action('Unggah Dokumen Perbaikan Sekarang', $statusUrl)
            ->line("**Informasi Penting:**")
            ->line("1. Anda **tidak perlu mengulang pendaftaran dari awal**. Cukup klik tombol di atas dan unggah kembali dokumen yang diminta.")
            ->line("2. Harap selesaikan perbaikan berkas sesegera mungkin agar permohonan Anda dapat langsung disetujui petugas.")
            ->salutation("Hormat kami,\nTim Verifikasi Disdukcapil Kabupaten Dompu");
    }
}
