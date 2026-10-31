<?php

namespace Database\Seeders;

use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QueueScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $services = ServiceType::active()->get();
        if ($services->isEmpty()) return;

        // Generate next 14 days (skip Saturday & Sunday)
        $today = Carbon::today();
        $daysAdded = 0;
        $current = $today->copy();

        while ($daysAdded < 10) {
            $current->addDay();
            // Skip weekend (6 = Saturday, 0 = Sunday)
            if ($current->isWeekend()) {
                continue;
            }

            $totalQuota = $services->sum('quota_per_day');

            $schedule = QueueSchedule::firstOrCreate(
                ['schedule_date' => $current->format('Y-m-d')],
                [
                    'open_time' => '08:00:00',
                    'close_time' => '15:30:00',
                    'total_quota' => $totalQuota,
                    'is_holiday' => false,
                    'is_open' => true,
                ]
            );

            foreach ($services as $service) {
                QueueSlot::firstOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'service_type_id' => $service->id,
                    ],
                    [
                        'quota' => $service->quota_per_day,
                        'booked_count' => 0,
                    ]
                );
            }

            $daysAdded++;
        }
    }
}
