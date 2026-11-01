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
            'schedules'    => $schedules,
            'services'     => $services,
            'currentMonth' => $month,
        ]);
    }

    /**
     * Create or update a schedule for a single date.
     */
    public function store(Request $request): RedirectResponse
    {
        $normalizedDate = $this->normalizeDateInput($request->input('schedule_date'));
        $openTime = $this->normalizeTimeInput($request->input('open_time'), '08:00');
        $closeTime = $this->normalizeTimeInput($request->input('close_time'), '15:30');

        $request->merge([
            'schedule_date' => $normalizedDate,
            'open_time'     => $openTime,
            'close_time'    => $closeTime,
        ]);

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
        $validated['open_time'] = $openTime . ':00';
        $validated['close_time'] = $closeTime . ':00';

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

        $dateDisplay = $schedule->schedule_date_indonesia ?? Carbon::parse($schedule->schedule_date)->format('d/m/Y');
        return back()->with('success', "Jadwal dan kuota tanggal {$dateDisplay} berhasil disimpan.");
    }

    /**
     * Bulk generate schedules for a date range.
     */
    public function bulkCreate(Request $request): RedirectResponse
    {
        $startDate = $this->normalizeDateInput($request->input('start_date'));
        $endDate = $this->normalizeDateInput($request->input('end_date'));
        $openTime = $this->normalizeTimeInput($request->input('open_time'), '08:00');
        $closeTime = $this->normalizeTimeInput($request->input('close_time'), '15:30');

        $request->merge([
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'open_time'  => $openTime,
            'close_time' => $closeTime,
        ]);

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'open_time'  => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i',
            'skip_weekends' => 'boolean',
        ]);

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $skipWeekends = $request->boolean('skip_weekends', true);
        $openTimeDb = $openTime . ':00';
        $closeTimeDb = $closeTime . ':00';

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

            $schedule = QueueSchedule::updateOrCreate(
                ['schedule_date' => $dateStr],
                [
                    'open_time'   => $openTimeDb,
                    'close_time'  => $closeTimeDb,
                    'total_quota' => $totalDailyQuota,
                    'is_open'     => true,
                ]
            );

            foreach ($services as $service) {
                QueueSlot::updateOrCreate(
                    [
                        'schedule_id'     => $schedule->id,
                        'service_type_id' => $service->id,
                    ],
                    [
                        'quota' => $service->quota_per_day,
                    ]
                );
            }

            $createdCount++;
            $current->addDay();
        }

        return back()->with('success', "Berhasil menyimpan jadwal untuk {$createdCount} hari operasional pelayanan.");
    }

    /**
     * Delete / close a schedule.
     */
    public function destroy(int $id): RedirectResponse
    {
        $schedule = QueueSchedule::with('slots.applications')->findOrFail($id);

        $hasBooked = $schedule->slots->sum('booked_count') > 0;
        $dateStr = $schedule->schedule_date_indonesia ?? Carbon::parse($schedule->schedule_date)->format('d/m/Y');

        if ($hasBooked) {
            return back()->with('error', "Jadwal tanggal {$dateStr} tidak dapat dihapus karena sudah ada pemohon yang memiliki nomor antrian. Anda dapat menandainya sebagai Hari Libur atau Menutupnya.");
        }

        $schedule->delete();

        return back()->with('success', "Jadwal tanggal {$dateStr} berhasil dihapus.");
    }

    /**
     * Normalize date inputs from various formats (e.g. DD/MM/YYYY or YYYY-MM-DD or ISO string).
     */
    protected function normalizeDateInput(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        $value = trim($value);

        // DD/MM/YYYY format
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // ISO format containing 'T'
        if (str_contains($value, 'T')) {
            return substr($value, 0, 10);
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    /**
     * Normalize time input to strict HH:MM format.
     */
    protected function normalizeTimeInput(?string $value, string $default = '08:00'): string
    {
        if (!$value) {
            return $default;
        }
        $value = trim($value);
        $value = str_replace('.', ':', $value);
        return substr($value, 0, 5);
    }
}
