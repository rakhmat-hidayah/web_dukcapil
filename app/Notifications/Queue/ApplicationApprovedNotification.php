<?php

namespace App\Notifications\Queue;

use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSetting;
use App\Services\ConfigurationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public QueueApplication $application) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->application->loadMissing(['serviceType', 'slot.schedule']);
        $statusUrl = route('queue.status', ['token' => $this->application->access_token]);
        $slot = $this->application->slot;
        $dateLabel = $slot && $slot->schedule ? $slot->schedule->schedule_date->translatedFormat('l, d F Y') : '-';
        $timeWindow = $slot && $slot->schedule ? substr($slot->schedule->open_time, 0, 5) . ' - ' . substr($slot->schedule->close_time, 0, 5) . ' WITA' : '08:00 - 15:30 WITA';
        $serviceName = $this->application->serviceType?->name ?: 'Pelayanan KTP-el';

        $officeAddress = ConfigurationService::get('office_address')
            ?: QueueSetting::get('queue.office_address', 'Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211');

        return (new MailMessage)
            ->subject("🎫 Nomor Antrean Terbit: {$this->application->queue_number} — Disdukcapil Kabupaten Dompu")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Permohonan pelayanan KTP-el Anda telah diverifikasi dan **Nomor Antrean Anda telah berhasil diterbitkan**.")
            ->line("Berikut rincian jadwal dan tiket antrean pelayanan Anda:")
            ->line("• **Nomor Antrean:** `{$this->application->queue_number}`")
            ->line("• **Nomor Registrasi:** `{$this->application->reg_number}`")
            ->line("• **Jenis Layanan:** {$serviceName}")
            ->line("• **Tanggal Kedatangan:** {$dateLabel}")
            ->line("• **Jam Operasional Loket:** {$timeWindow}")
            ->line("• **Lokasi Pelayanan:** Kantor Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu ({$officeAddress}).")
            ->action('Lihat & Cetak Tiket Antrean (QR Code)', $statusUrl)
            ->line("**Petunjuk Kehadiran di Loket:**")
            ->line("1. Harap hadir sesuai jadwal dan jam operasional loket di atas.")
            ->line("2. Tunjukkan QR code tiket antrean ini kepada petugas loket.")
            ->line("3. Wajib membawa dokumen fisik asli persyaratan untuk pencocokan berkas.")
            ->line("4. *Khusus jika diwakilkan:* Penerima kuasa wajib membawa Surat Kuasa Asli (Formulir F-1.07) bermeterai Rp 10.000 dan KTP-el asli penerima kuasa.")
            ->salutation("Salam hangat,\nTim Pelayanan Disdukcapil Kabupaten Dompu");
    }
}
