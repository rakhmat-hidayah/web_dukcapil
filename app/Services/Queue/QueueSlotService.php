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
                $dateFormatted = Carbon::parse($schedDate)->locale('id')->translatedFormat('l, d F Y');
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
     * Get or create the earliest available open schedule slot for a given service type.
     */
    public function getEarliestAvailableSlot(int $serviceTypeId): ?QueueSlot
    {
        $today = Carbon::today();

        // 1. Find existing open slot with remaining quota starting from today
        $slot = QueueSlot::with(['schedule', 'serviceType'])
            ->where('queue_slots.service_type_id', $serviceTypeId)
            ->whereHas('schedule', function ($q) use ($today) {
                $q->where('schedule_date', '>=', $today->format('Y-m-d'))
                  ->where('is_open', true)
                  ->where('is_holiday', false);
            })
            ->whereRaw('queue_slots.booked_count < queue_slots.quota')
            ->join('queue_schedules', 'queue_slots.schedule_id', '=', 'queue_schedules.id')
            ->orderBy('queue_schedules.schedule_date', 'asc')
            ->orderBy('queue_schedules.open_time', 'asc')
            ->select('queue_slots.*')
            ->first();

        if ($slot) {
            return $slot;
        }

        // 2. If no available slot has remaining quota, check if any upcoming open schedule exists
        $upcomingSchedule = QueueSchedule::where('schedule_date', '>=', $today->format('Y-m-d'))
            ->where('is_open', true)
            ->where('is_holiday', false)
            ->orderBy('schedule_date', 'asc')
            ->first();

        if ($upcomingSchedule) {
            $service = \App\Models\Queue\ServiceType::find($serviceTypeId);
            $quota = $service ? $service->quota_per_day : 25;

            return QueueSlot::firstOrCreate(
                [
                    'schedule_id'     => $upcomingSchedule->id,
                    'service_type_id' => $serviceTypeId,
                ],
                [
                    'quota'        => $quota,
                    'booked_count' => 0,
                ]
            );
        }

        // 3. Fallback: create next workday schedule if no schedules exist
        $nextDate = $today->copy();
        do {
            $nextDate->addDay();
        } while ($nextDate->isWeekend());

        $service = \App\Models\Queue\ServiceType::find($serviceTypeId);
        $quota = $service ? $service->quota_per_day : 25;

        $newSchedule = QueueSchedule::firstOrCreate(
            ['schedule_date' => $nextDate->format('Y-m-d')],
            [
                'open_time'   => '08:00:00',
                'close_time'  => '15:30:00',
                'total_quota' => 100,
                'is_holiday'  => false,
                'is_open'     => true,
            ]
        );

        return QueueSlot::firstOrCreate(
            [
                'schedule_id'     => $newSchedule->id,
                'service_type_id' => $serviceTypeId,
            ],
            [
                'quota'        => $quota,
                'booked_count' => 0,
            ]
        );
    }

    /**
     * Securely book a slot using pessimistic row locking to prevent race conditions.
     *
     * @throws QuotaExceededException
     */
    public function bookSlot(int $slotId, QueueApplication $application): QueueSlot
    {
        return DB::transaction(function () use ($slotId, $application) {
            // Lock application row first to guarantee idempotency across concurrent requests
            $app = QueueApplication::lockForUpdate()->findOrFail($application->id);

            // If already booked to this exact slot and already has queue_number, return existing slot idempotently
            if ($app->queue_number && $app->queue_slot_id === $slotId) {
                return QueueSlot::with(['schedule', 'serviceType'])->findOrFail($slotId);
            }

            // If booked to another slot, release old slot quota first
            if ($app->queue_slot_id && $app->queue_slot_id !== $slotId) {
                $oldSlot = QueueSlot::lockForUpdate()->find($app->queue_slot_id);
                if ($oldSlot && $oldSlot->booked_count > 0) {
                    $oldSlot->decrement('booked_count');
                }
            }

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

            $app->update([
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
