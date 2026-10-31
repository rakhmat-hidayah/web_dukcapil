<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueScheduleController extends Controller
{
    /**
     * List schedules and manage daily quotas.
     */
    public function index(Request $request): Response
    {
        $month = $request->query('month', date('Y-m'));

        $startOfMonth = Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::parse($month)->endOfMonth()->format('Y-m-d');

        $schedules = QueueSchedule::with(['slots.serviceType'])
            ->whereBetween('schedule_date', [$startOfMonth, $endOfMonth])
            ->orderBy('schedule_date')
            ->get();

        $services = ServiceType::active()->orderBy('sort_order')->get();

        return Inertia::render('Admin/Queue/Schedules/Index', [
            'schedules' => $schedules,
            'services' => $services,
            'currentMonth' => $month,
        ]);
    }

    /**
     * Create or update a schedule for a single date.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule_date' => 'required|date',
            'open_time'     => 'required|date_format:H:i',
            'close_time'    => 'required|date_format:H:i',
            'is_holiday'    => 'boolean',
            'holiday_note'  => 'nullable|string|max:200',
            'is_open'       => 'boolean',
            'slots'         => 'nullable|array',
            'slots.*.service_type_id' => 'required|exists:queue_service_types,id',
            'slots.*.quota' => 'required|integer|min:0|max:500',
        ]);

        $slotsData = $validated['slots'] ?? [];
        unset($validated['slots']);

        $totalQuota = collect($slotsData)->sum('quota');
        $validated['total_quota'] = $totalQuota;

        $schedule = QueueSchedule::updateOrCreate(
            ['schedule_date' => $validated['schedule_date']],
            $validated
        );

        foreach ($slotsData as $slotItem) {
            QueueSlot::updateOrCreate(
                [
                    'schedule_id'     => $schedule->id,
                    'service_type_id' => $slotItem['service_type_id'],
                ],
                [
                    'quota' => $slotItem['quota'],
                ]
            );
        }

        return back()->with('success', "Jadwal dan kuota tanggal {$schedule->schedule_date->format('d/m/Y')} berhasil disimpan.");
    }

    /**
     * Bulk generate schedules for a date range.
     */
    public function bulkCreate(Request $request): RedirectResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'open_time'  => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
            'skip_weekends' => 'boolean',
        ]);

        $start = Carbon::parse($request->input('start_date'));
        $end = Carbon::parse($request->input('end_date'));
        $skipWeekends = $request->boolean('skip_weekends', true);
        $openTime = $request->input('open_time') . ':00';
        $closeTime = $request->input('close_time') . ':00';

        $services = ServiceType::active()->get();
        $totalDailyQuota = $services->sum('quota_per_day');

        $createdCount = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($skipWeekends && $current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $dateStr = $current->format('Y-m-d');

            $schedule = QueueSchedule::firstOrCreate(
                ['schedule_date' => $dateStr],
                [
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'total_quota' => $totalDailyQuota,
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

            $createdCount++;
            $current->addDay();
        }

        return back()->with('success', "Berhasil menjadwalkan {$createdCount} hari operasional pelayanan.");
    }

    /**
     * Delete / close a schedule.
     */
    public function destroy(int $id): RedirectResponse
    {
        $schedule = QueueSchedule::with('slots.applications')->findOrFail($id);

        $hasBooked = $schedule->slots->sum('booked_count') > 0;
        if ($hasBooked) {
            return back()->with('error', "Jadwal tanggal {$schedule->schedule_date->format('d/m/Y')} tidak dapat dihapus karena sudah ada pemohon yang memiliki nomor antrian. Anda dapat menandainya sebagai Hari Libur atau Menutupnya.");
        }

        $dateStr = $schedule->schedule_date->format('d/m/Y');
        $schedule->delete();

        return back()->with('success', "Jadwal tanggal {$dateStr} berhasil dihapus.");
    }
}
