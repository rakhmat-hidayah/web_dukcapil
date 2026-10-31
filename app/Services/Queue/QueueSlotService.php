<?php

namespace App\Services\Queue;

use App\Exceptions\Queue\QuotaExceededException;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QueueSlotService
{
    /**
     * Get open and available slots for a given service type from today onwards.
     */
    public function getAvailableSlots(int $serviceTypeId, ?Carbon $fromDate = null, int $daysAhead = 14): array
    {
        $fromDate = $fromDate ?? Carbon::today();
        $toDate = $fromDate->copy()->addDays($daysAhead);

        return QueueSlot::with(['schedule'])
            ->where('service_type_id', $serviceTypeId)
            ->whereHas('schedule', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('schedule_date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])
                  ->where('is_open', true)
                  ->where('is_holiday', false);
            })
            ->whereRaw('booked_count < quota')
            ->get()
            ->sortBy(fn($slot) => $slot->schedule->schedule_date)
            ->map(function ($slot) {
                $schedDate = $slot->schedule->schedule_date;
                $dateFormatted = $schedDate instanceof Carbon
                    ? $schedDate->translatedFormat('l, d F Y')
                    : Carbon::parse($schedDate)->translatedFormat('l, d F Y');
                $openTime = substr($slot->schedule->open_time, 0, 5);
                $closeTime = substr($slot->schedule->close_time, 0, 5);
                $remaining = max(0, $slot->quota - $slot->booked_count);

                return [
                    'id'              => $slot->id,
                    'schedule_id'     => $slot->schedule_id,
                    'date'            => $schedDate instanceof Carbon ? $schedDate->format('Y-m-d') : (string) $schedDate,
                    'date_label'      => $dateFormatted,
                    'open_time'       => $openTime,
                    'close_time'      => $closeTime,
                    'quota'           => $slot->quota,
                    'booked_count'    => $slot->booked_count,
                    'remaining_quota' => $remaining,
                    'label'           => "{$dateFormatted} ({$openTime} - {$closeTime} WITA) — Sisa Kuota: {$remaining} dari {$slot->quota} (Terisi: {$slot->booked_count})",
                    'schedule'        => [
                        'schedule_date' => $schedDate instanceof Carbon ? $schedDate->format('Y-m-d') : (string) $schedDate,
                        'open_time'     => $openTime,
                        'close_time'    => $closeTime,
                    ],
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Securely book a slot using pessimistic row locking to prevent race conditions.
     *
     * @throws QuotaExceededException
     */
    public function bookSlot(int $slotId, QueueApplication $application): QueueSlot
    {
        return DB::transaction(function () use ($slotId, $application) {
            // lockForUpdate prevents concurrent transactions from reading stale booked_count
            $slot = QueueSlot::with(['schedule', 'serviceType'])
                ->lockForUpdate()
                ->findOrFail($slotId);

            if ($slot->booked_count >= $slot->quota) {
                throw new QuotaExceededException(
                    "Kuota antrian untuk tanggal " .
                    ($slot->schedule ? $slot->schedule->schedule_date->format('d/m/Y') : '') .
                    " sudah penuh. Silakan pilih tanggal jadwal lain."
                );
            }

            if (!$slot->schedule || !$slot->schedule->isAvailable()) {
                throw new QuotaExceededException("Jadwal pelayanan pada tanggal ini sedang ditutup atau hari libur.");
            }

            // Increment booked count
            $slot->increment('booked_count');

            // Generate verified final queue number
            $queueNumber = QueueNumberGenerator::generateQueueNumber($slot);

            $application->update([
                'queue_slot_id' => $slot->id,
                'queue_number'  => $queueNumber,
            ]);

            return $slot->fresh(['schedule', 'serviceType']);
        });
    }

    /**
     * Release a slot booking and return quota if an application is cancelled or re-slotted.
     */
    public function releaseSlot(QueueApplication $application): void
    {
        if (!$application->queue_slot_id) {
            return;
        }

        DB::transaction(function () use ($application) {
            $slot = QueueSlot::lockForUpdate()->find($application->queue_slot_id);
            if ($slot && $slot->booked_count > 0) {
                $slot->decrement('booked_count');
            }

            $application->update([
                'queue_slot_id' => null,
                'queue_number'  => null,
            ]);
        });
    }
}
