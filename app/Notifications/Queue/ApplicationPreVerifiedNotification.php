<?php

namespace App\Notifications\Queue;

use App\Models\Queue\QueueApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationPreVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(public QueueApplication $application) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->application->loadMissing('serviceType');
        $bookingUrl = route('queue.booking', ['token' => $this->application->access_token]);
        $serviceName = $this->application->serviceType?->name ?: 'Pelayanan KTP-el';

        $mail = (new MailMessage)
            ->subject("✅ Berkas Disetujui — Silakan Pilih Jadwal Antrean [{$this->application->reg_number}] — Disdukcapil Kab. Dompu")
            ->greeting("Yth. Bapak/Ibu {$this->application->applicant_name},")
            ->line("Kabar baik! Berkas dokumen digital permohonan Anda telah diverifikasi oleh petugas Disdukcapil Kabupaten Dompu dan **dinyatakan Lolos Pra-Verifikasi (Lengkap & Sah)**.")
            ->line("• **Nomor Registrasi:** `{$this->application->reg_number}`")
            ->line("• **Jenis Layanan:** {$serviceName}");

        if (!empty($this->application->notes)) {
            $mail->line("• **Catatan Verifikator:** _{$this->application->notes}_");
        }

        return $mail
            ->line("**Langkah Selanjutnya (PENTING):**")
            ->line("Silakan klik tombol di bawah untuk **langsung memilih hari dan sesi jam kedatangan** ke kantor Disdukcapil. Nomor Antrean Anda akan otomatis diterbitkan begitu Anda memilih jadwal.")
            ->action('📅 Pilih Jadwal Antrean Sekarang', $bookingUrl)
            ->line("Catatan: Harap segera memilih jadwal sebelum kuota sesi pelayanan pada hari yang diinginkan terisi penuh.")
            ->salutation("Salam hangat,\nTim Pelayanan Disdukcapil Kabupaten Dompu");
    }
}
