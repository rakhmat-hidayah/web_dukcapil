<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminAlertNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Dispatch a notification to users holding specific roles.
     */
    public static function sendToRoles(array $roles, string $title, string $message, string $url = '', string $type = 'info', array $metadata = []): void
    {
        try {
            // Find users who have any of the specified roles
            $users = User::role($roles)->get();

            if ($users->isNotEmpty()) {
                Notification::send($users, new AdminAlertNotification($title, $message, $url, $type, $metadata));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send role notifications: ' . $e->getMessage());
        }
    }

    /**
     * Notify about a new public complaint submission.
     */
    public static function notifyComplaintCreated($complaint): void
    {
        $title = 'Pengaduan Baru';
        $message = "Pengaduan baru dari {$complaint->name} dengan nomor tiket {$complaint->ticket_number} telah diterima.";
        $url = "/admin/complaints/{$complaint->id}";
        
        self::sendToRoles(
            ['Super Administrator', 'Website Administrator', 'Complaint Officer'],
            $title,
            $message,
            $url,
            'warning',
            ['complaint_id' => $complaint->id, 'ticket_number' => $complaint->ticket_number]
        );
    }

    /**
     * Notify about new news/article creations or updates.
     */
    public static function notifyNewsCreated($news, string $status = 'published'): void
    {
        $statusLabels = [
            'draft' => 'Draft Berita Baru',
            'scheduled' => 'Jadwal Publish Berita',
            'published' => 'Berita Baru Dipublish',
        ];

        $title = $statusLabels[$status] ?? 'Berita Baru';
        $message = "Berita baru '{$news->title}' telah dibuat dengan status: " . strtoupper($status);
        $url = "/admin/news/{$news->id}/edit";

        self::sendToRoles(
            ['Super Administrator', 'Website Administrator', 'Content Editor'],
            $title,
            $message,
            $url,
            'info',
            ['news_id' => $news->id]
        );
    }

    /**
     * Notify about new download uploads.
     */
    public static function notifyDownloadCreated($download): void
    {
        $title = 'File Download Baru';
        $message = "Operator mengupload file download baru: '{$download->title}' dalam kategori {$download->category->name}.";
        $url = '/admin/downloads';

        self::sendToRoles(
            ['Super Administrator', 'Website Administrator', 'Publication Operator'],
            $title,
            $message,
            $url,
            'success',
            ['download_id' => $download->id]
        );
    }

    /**
     * Notify about suspicious admin login events.
     */
    public static function notifySuspiciousLogin($user, string $ip, string $reason = 'IP tidak dikenal'): void
    {
        $title = 'Login Mencurigakan';
        $message = "Percobaan login terdeteksi pada akun {$user->email} dari IP {$ip}. Alasan: {$reason}.";
        $url = '/admin/audit-logs';

        self::sendToRoles(
            ['Super Administrator', 'Website Administrator'],
            $title,
            $message,
            $url,
            'danger',
            ['user_id' => $user->id, 'ip' => $ip, 'reason' => $reason]
        );
    }

    /**
     * Notify about new comments (if enabled).
     */
    public static function notifyCommentCreated($comment): void
    {
        $title = 'Komentar Baru';
        $message = "Komentar baru dari {$comment->name} pada berita '{$comment->news->title}'.";
        $url = "/admin/comments";

        self::sendToRoles(
            ['Super Administrator', 'Website Administrator', 'Content Editor'],
            $title,
            $message,
            $url,
            'info',
            ['comment_id' => $comment->id]
        );
    }
}
