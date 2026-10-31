<?php

namespace App\Notifications;

use App\Models\Complaint;
use App\Models\ComplaintNotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class NewComplaintNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Complaint $complaint;
    public ?int $logId;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [30, 120, 300];

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new notification instance.
     */
    public function __construct(Complaint $complaint, ?int $logId = null)
    {
        $this->complaint = $complaint;
        $this->logId     = $logId;

        // Ensure notification is dispatched only AFTER database transaction commits
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticket   = $this->complaint->ticket_number;
        $subject  = "🔔 Pengaduan Baru — {$ticket}";
        $cmsUrl   = rtrim(config('app.url'), '/') . "/admin/complaints/{$this->complaint->id}";
        $reporter = $this->complaint->is_anonymous ? 'Anonim' : ($this->complaint->submitter_name ?? 'Masyarakat');
        $category = $this->complaint->category ? $this->complaint->category->name : 'Umum / Lainnya';
        $dateStr  = $this->complaint->created_at ? $this->complaint->created_at->format('d F Y H:i') : date('d F Y H:i');

        // Update notification log status to SENT if log ID exists
        if ($this->logId) {
            try {
                ComplaintNotificationLog::where('id', $this->logId)->update([
                    'status'  => ComplaintNotificationLog::STATUS_SENT,
                    'sent_at' => now(),
                ]);
            } catch (Throwable $e) {}
        }

        return (new MailMessage)
            ->subject($subject)
            ->from(
                config('complaint.notifications.from_address', 'dukcapilkabdompu@gmail.com'),
                config('complaint.notifications.from_name', 'Dukcapil Kabupaten Dompu')
            )
            ->greeting('PENGADUAN BARU')
            ->line('Telah masuk pengaduan baru pada Portal Pengaduan Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu.')
            ->line("Nomor Tiket: **{$ticket}**")
            ->line("Pelapor: **{$reporter}**")
            ->line("Subjek: **{$this->complaint->subject}**")
            ->line("Kategori: **{$category}**")
            ->line("Tanggal Masuk: **{$dateStr} WITA**")
            ->line("Status: **Menunggu Tindakan**")
            ->action('BUKA PENGADUAN DI CMS', $cmsUrl)
            ->line('Catatan Keamanan: Email ini merupakan notifikasi otomatis dari sistem. Untuk menjaga privasi dan keamanan data kependudukan, detail dokumen dan tindakan penanganan dilakukan di CMS.')
            ->salutation("Salam hormat,\n**Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu**");
    }

    /**
     * Handle job failure safely without leaking SMTP passwords.
     */
    public function failed(Throwable $exception): void
    {
        if ($this->logId) {
            try {
                $sanitizedError = preg_replace('/:[^:@\s]+@/', ':****@', $exception->getMessage());

                ComplaintNotificationLog::where('id', $this->logId)->update([
                    'status'        => ComplaintNotificationLog::STATUS_FAILED,
                    'failed_at'     => now(),
                    'error_message' => substr($sanitizedError, 0, 1000),
                ]);
            } catch (Throwable $e) {}
        }
    }
}
