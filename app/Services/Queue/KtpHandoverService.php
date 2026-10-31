<?php

namespace App\Services\Queue;

use App\Models\Queue\KtpHandover;
use App\Models\Queue\KtpHandoverLog;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\VerificationHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KtpHandoverService
{
    /**
     * Get accurate dashboard statistics calculated entirely from database.
     */
    public function getDashboardStats(): array
    {
        $todayMakassar = Carbon::now('Asia/Makassar')->toDateString();
        $startOfMonth = Carbon::now('Asia/Makassar')->startOfMonth();

        $countPending = KtpHandover::pending()->count();
        $countReady = KtpHandover::ready()->count();
        $countException = KtpHandover::exception()->count();

        $countTodayHandedOver = KtpHandover::handedOver()
            ->whereDate('handover_at', $todayMakassar)
            ->count();

        $countMonthHandedOver = KtpHandover::handedOver()
            ->where('handover_at', '>=', $startOfMonth)
            ->count();

        // 7-day weekly trend
        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Makassar')->subDays($i);
            $dateString = $date->toDateString();
            $label = $date->translatedFormat('D, d M');

            $count = KtpHandover::handedOver()
                ->whereDate('handover_at', $dateString)
                ->count();

            $weeklyTrend[] = [
                'date'  => $dateString,
                'label' => $label,
                'total' => $count,
            ];
        }

        // Recent handovers
        $recentHandovers = KtpHandover::with(['application.serviceType', 'handoverUser'])
            ->handedOver()
            ->orderBy('handover_at', 'desc')
            ->limit(8)
            ->get();

        return [
            'count_pending'            => $countPending,
            'count_ready'              => $countReady,
            'count_today_handed_over'  => $countTodayHandedOver,
            'count_month_handed_over'  => $countMonthHandedOver,
            'count_exception'          => $countException,
            'weekly_trend'             => $weeklyTrend,
            'recent_handovers'         => $recentHandovers,
        ];
    }

    /**
     * Mark physical card as examined and ready for pickup (SIAP_DIAMBIL).
     */
    public function markReady(int $handoverId, ?User $officer, ?string $notes = null): KtpHandover
    {
        return DB::transaction(function () use ($handoverId, $officer, $notes) {
            $handover = KtpHandover::where('id', $handoverId)->lockForUpdate()->firstOrFail();
            $app = QueueApplication::where('id', $handover->application_id)->lockForUpdate()->firstOrFail();

            if (!in_array($handover->status, [KtpHandover::STATUS_PENDING, KtpHandover::STATUS_EXCEPTION])) {
                throw new \InvalidArgumentException("Hanya transaksi berstatus Menunggu Ketersediaan atau Kendala yang dapat ditandai Siap Diambil.");
            }

            $fromStatus = $handover->status;
            $handover->update([
                'status'          => KtpHandover::STATUS_READY,
                'ready_marked_at' => now(),
                'ready_marked_by' => $officer?->id,
                'ready_notes'     => $notes,
                'has_exception'   => false,
            ]);

            // Sync application status
            $app->update([
                'status' => QueueApplication::STATUS_READY_FOR_PICKUP,
                'notes'  => $notes ?: $app->notes,
            ]);

            // Audit log
            KtpHandoverLog::create([
                'handover_id'    => $handover->id,
                'application_id' => $app->id,
                'user_id'        => $officer?->id,
                'action'         => 'MARKED_READY',
                'from_status'    => $fromStatus,
                'to_status'      => KtpHandover::STATUS_READY,
                'notes'          => 'Petugas memastikan fisik kartu KTP-el tersedia dan siap diserahkan kepada pemohon.' . ($notes ? " Catatan: {$notes}" : ''),
            ]);

            // Record verification history for public tracking
            VerificationHistory::create([
                'application_id' => $app->id,
                'from_status'    => $fromStatus,
                'to_status'      => QueueApplication::STATUS_READY_FOR_PICKUP,
                'action'         => 'SIAP_DIAMBIL',
                'user_id'        => $officer?->id,
                'notes'          => 'Fisik KTP-el telah diperiksa dan siap diambil di Disdukcapil Kabupaten Dompu.' . ($notes ? " Catatan: {$notes}" : ''),
            ]);

            return $handover->fresh(['application.serviceType', 'readyMarkedUser']);
        });
    }

    /**
     * Report an issue / exception during pickup (KENDALA_PENGAMBILAN).
     */
    public function reportException(int $handoverId, string $reason, ?User $officer): KtpHandover
    {
        return DB::transaction(function () use ($handoverId, $reason, $officer) {
            $handover = KtpHandover::where('id', $handoverId)->lockForUpdate()->firstOrFail();
            $app = QueueApplication::where('id', $handover->application_id)->lockForUpdate()->firstOrFail();

            $fromStatus = $handover->status;
            $handover->update([
                'status'                => KtpHandover::STATUS_EXCEPTION,
                'has_exception'         => true,
                'exception_reason'      => $reason,
                'exception_reported_at' => now(),
                'exception_reported_by' => $officer?->id,
            ]);

            // Audit log
            KtpHandoverLog::create([
                'handover_id'    => $handover->id,
                'application_id' => $app->id,
                'user_id'        => $officer?->id,
                'action'         => 'EXCEPTION_REPORTED',
                'from_status'    => $fromStatus,
                'to_status'      => KtpHandover::STATUS_EXCEPTION,
                'notes'          => "Kendala pengambilan dilaporkan: {$reason}",
            ]);

            VerificationHistory::create([
                'application_id' => $app->id,
                'from_status'    => $fromStatus,
                'to_status'      => 'KENDALA_PENGAMBILAN',
                'action'         => 'KENDALA_PENGAMBILAN',
                'user_id'        => $officer?->id,
                'notes'          => "Kendala pengambilan KTP-el: {$reason}",
            ]);

            return $handover->fresh(['application.serviceType', 'exceptionReportedUser']);
        });
    }

    /**
     * Resolve exception and return card to ready list (SIAP_DIAMBIL).
     */
    public function resolveException(int $handoverId, string $resolutionNotes, ?User $officer): KtpHandover
    {
        return DB::transaction(function () use ($handoverId, $resolutionNotes, $officer) {
            $handover = KtpHandover::where('id', $handoverId)->lockForUpdate()->firstOrFail();
            $app = QueueApplication::where('id', $handover->application_id)->lockForUpdate()->firstOrFail();

            if ($handover->status !== KtpHandover::STATUS_EXCEPTION) {
                throw new \InvalidArgumentException("Hanya transaksi berkendala yang dapat diselesaikan.");
            }

            $fromStatus = $handover->status;
            $handover->update([
                'status'                     => KtpHandover::STATUS_READY,
                'has_exception'              => false,
                'exception_resolved_at'      => now(),
                'exception_resolved_by'      => $officer?->id,
                'exception_resolution_notes' => $resolutionNotes,
            ]);

            $app->update([
                'status' => QueueApplication::STATUS_READY_FOR_PICKUP,
            ]);

            // Audit log preserves old exception reason while logging resolution
            KtpHandoverLog::create([
                'handover_id'    => $handover->id,
                'application_id' => $app->id,
                'user_id'        => $officer?->id,
                'action'         => 'EXCEPTION_RESOLVED',
                'from_status'    => $fromStatus,
                'to_status'      => KtpHandover::STATUS_READY,
                'notes'          => "Kendala diselesaikan: {$resolutionNotes}",
            ]);

            VerificationHistory::create([
                'application_id' => $app->id,
                'from_status'    => 'KENDALA_PENGAMBILAN',
                'to_status'      => QueueApplication::STATUS_READY_FOR_PICKUP,
                'action'         => 'KENDALA_DISELESAIKAN',
                'user_id'        => $officer?->id,
                'notes'          => "Kendala serah terima diselesaikan: {$resolutionNotes}",
            ]);

            return $handover->fresh(['application.serviceType', 'exceptionResolvedUser']);
        });
    }

    /**
     * Complete physical card handover with photo proof upload.
     */
    public function completeHandover(int $handoverId, array $data, UploadedFile $photoFile, ?User $officer): KtpHandover
    {
        return DB::transaction(function () use ($handoverId, $data, $photoFile, $officer) {
            $handover = KtpHandover::where('id', $handoverId)->lockForUpdate()->firstOrFail();
            $app = QueueApplication::where('id', $handover->application_id)->lockForUpdate()->firstOrFail();

            if ($handover->status === KtpHandover::STATUS_HANDED_OVER) {
                throw new \InvalidArgumentException("KTP-el untuk permohonan ini sudah pernah diserahkan sebelumnya.");
            }

            // Save photo to private storage
            $yearMonth = date('Y/m');
            $filename = sprintf('handover_%d_%s_%s.jpg', $handover->id, now()->format('Ymd_His'), Str::random(8));
            $directory = "handover_proofs/{$yearMonth}";
            
            $contents = file_get_contents($photoFile->getRealPath());
            $photoHash = hash('sha256', $contents);
            $photoSize = strlen($contents);
            $photoMime = $photoFile->getClientMimeType() ?: 'image/jpeg';

            $storedPath = Storage::disk('local')->putFileAs($directory, $photoFile, $filename);

            if (!$storedPath) {
                throw new \RuntimeException("Gagal menyimpan file foto bukti penyerahan ke penyimpanan privat server.");
            }

            $fromStatus = $handover->status;
            $receiverType = $data['actual_receiver_type'];
            $receiverName = trim($data['actual_receiver_name']);
            $receiverNik = trim($data['actual_receiver_nik']);
            $receiverPhone = isset($data['actual_receiver_phone']) ? trim($data['actual_receiver_phone']) : null;
            $relationship = trim($data['receiver_relationship']);
            $handoverNotes = $data['handover_notes'] ?? null;

            // Update Handover entity
            $handover->update([
                'status'                => KtpHandover::STATUS_HANDED_OVER,
                'actual_receiver_type'  => $receiverType,
                'actual_receiver_name'  => $receiverName,
                'actual_receiver_nik'   => $receiverNik,
                'actual_receiver_phone' => $receiverPhone,
                'receiver_relationship' => $relationship,
                'handover_at'           => now(),
                'handover_by'           => $officer?->id,
                'handover_notes'        => $handoverNotes,
                'photo_path'            => $storedPath,
                'photo_mime'            => $photoMime,
                'photo_size_bytes'      => $photoSize,
                'photo_hash'            => $photoHash,
                'has_exception'         => false,
            ]);

            // Update parent application
            $app->update([
                'status'           => QueueApplication::STATUS_COMPLETED,
                'completed_at'     => now(),
                'handover_at'      => now(),
                'handover_by'      => $officer?->id,
                'received_by_type' => $receiverType,
                'receiver_name'    => $receiverName,
                'receiver_nik'     => $receiverNik,
                'receiver_phone'   => $receiverPhone,
                'handover_notes'   => $handoverNotes,
            ]);

            // Audit log
            KtpHandoverLog::create([
                'handover_id'    => $handover->id,
                'application_id' => $app->id,
                'user_id'        => $officer?->id,
                'action'         => 'HANDOVER_COMPLETED',
                'from_status'    => $fromStatus,
                'to_status'      => KtpHandover::STATUS_HANDED_OVER,
                'notes'          => "KTP-el resmi diserahkan kepada {$receiverName} ({$relationship}). Bukti foto tersimpan.",
                'metadata'       => [
                    'receiver_type' => $receiverType,
                    'receiver_name' => $receiverName,
                    'receiver_nik'  => substr($receiverNik, 0, 4) . '****' . substr($receiverNik, -4),
                    'relationship'  => $relationship,
                    'photo_path'    => $storedPath,
                    'photo_hash'    => $photoHash,
                    'photo_size'    => $photoSize,
                ],
            ]);

            VerificationHistory::create([
                'application_id' => $app->id,
                'from_status'    => $fromStatus,
                'to_status'      => QueueApplication::STATUS_COMPLETED,
                'action'         => 'KTP_DISERAHKAN',
                'user_id'        => $officer?->id,
                'notes'          => "Fisik KTP-el telah diserahkan kepada {$receiverName} ({$relationship}) dengan dokumentasi foto penyerahan terverifikasi. Pelayanan selesai.",
            ]);

            return $handover->fresh(['application.serviceType', 'handoverUser']);
        });
    }

    /**
     * Securely stream private photo proof to authorized officer.
     */
    public function servePhoto(KtpHandover $handover): StreamedResponse
    {
        if (!$handover->photo_path || !Storage::disk('local')->exists($handover->photo_path)) {
            abort(404, 'File foto bukti penyerahan tidak ditemukan di server.');
        }

        $stream = Storage::disk('local')->readStream($handover->photo_path);
        $mime = $handover->photo_mime ?: 'image/jpeg';
        $size = $handover->photo_size_bytes ?: Storage::disk('local')->size($handover->photo_path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'              => $mime,
            'Content-Length'            => $size,
            'Cache-Control'             => 'private, no-cache, no-store, must-revalidate',
            'Pragma'                    => 'no-cache',
            'Expires'                   => '0',
            'X-Content-Type-Options'    => 'nosniff',
            'Content-Disposition'       => 'inline; filename="bukti_penyerahan_' . $handover->id . '.jpg"',
        ]);
    }
}
