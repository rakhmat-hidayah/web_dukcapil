<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QueueDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $today = Carbon::today()->format('Y-m-d');

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $serviceId = $request->query('service_id');
        $statusFilter = $request->query('status');
        $attendanceType = $request->query('attendance_type');

        // Base query with optional filters
        $baseQuery = QueueApplication::query();

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        if ($serviceId) {
            $baseQuery->where('service_type_id', $serviceId);
        }

        if ($statusFilter) {
            $baseQuery->where('status', $statusFilter);
        }

        if ($attendanceType) {
            if ($attendanceType === 'self') {
                $baseQuery->where('attendance_type', 'self');
            } elseif ($attendanceType === 'represented') {
                $baseQuery->whereIn('attendance_type', ['family_in_kk', 'family_out_kk', 'other']);
            }
        }

        // Metrics from filtered baseQuery
        $totalApplications = (clone $baseQuery)->count();
        $todayApplicationsCount = (clone $baseQuery)->whereDate('created_at', $today)->count();
        $waitingReview = (clone $baseQuery)->whereIn('status', [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED])->count();
        $underReview = (clone $baseQuery)->where('status', QueueApplication::STATUS_UNDER_REVIEW)->count();
        $revisionRequired = (clone $baseQuery)->where('status', QueueApplication::STATUS_REVISION_REQUIRED)->count();
        $preVerifiedCount = (clone $baseQuery)->where('status', QueueApplication::STATUS_PRE_VERIFIED)->count();
        $queueBookedCount = (clone $baseQuery)->where('status', QueueApplication::STATUS_QUEUE_BOOKED)->count();
        $arrivedCount = (clone $baseQuery)->whereIn('status', [QueueApplication::STATUS_ARRIVED, QueueApplication::STATUS_PHYSICAL_VERIFICATION])->count();
        $processingCount = (clone $baseQuery)->whereIn('status', [QueueApplication::STATUS_APPROVED_FOR_SERVICE, QueueApplication::STATUS_PROCESSING])->count();
        $readyPickupCount = (clone $baseQuery)->where('status', QueueApplication::STATUS_READY_FOR_PICKUP)->count();
        $completedCount = (clone $baseQuery)->where('status', QueueApplication::STATUS_COMPLETED)->count();
        $rejectedCount = (clone $baseQuery)->where('status', QueueApplication::STATUS_REJECTED)->count();
        $expiredCount = (clone $baseQuery)->where('status', QueueApplication::STATUS_EXPIRED)->count();
        $cancelledCount = (clone $baseQuery)->where('status', QueueApplication::STATUS_CANCELLED)->count();

        // Total KTP-el Fisik Dicetak (Status Siap Diambil & Selesai Diserahkan)
        $totalPrintedCount = (clone $baseQuery)->whereIn('status', [
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_COMPLETED,
        ])->count();

        // KTP-el Fisik Dicetak Hari Ini
        $todayPrintedCount = (clone $baseQuery)->whereIn('status', [
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_COMPLETED,
        ])->where(function ($q) use ($today) {
            $q->whereDate('updated_at', $today)
              ->orWhereDate('handover_at', $today)
              ->orWhereDate('completed_at', $today);
        })->count();

        // Representation breakdown
        $selfCount = (clone $baseQuery)->where('attendance_type', 'self')->count();
        $representedCount = (clone $baseQuery)->whereIn('attendance_type', ['family_in_kk', 'family_out_kk', 'other'])->count();

        // Today's counter metrics
        $todaySchedule = QueueSchedule::where('schedule_date', $today)->first();
        $todaySlots = $todaySchedule ? QueueSlot::where('schedule_id', $todaySchedule->id)->get() : collect();

        $todayTotalQuota = $todaySlots->sum('quota');
        $todayBooked = $todaySlots->sum('booked_count');
        $todayRemaining = max(0, $todayTotalQuota - $todayBooked);

        $todayApplications = QueueApplication::whereHas('slot.schedule', fn($s) => $s->where('schedule_date', $today))->get();

        $todayCheckedIn = $todayApplications->whereIn('status', [
            QueueApplication::STATUS_ARRIVED,
            QueueApplication::STATUS_PHYSICAL_VERIFICATION,
            QueueApplication::STATUS_APPROVED_FOR_SERVICE,
            QueueApplication::STATUS_PROCESSING,
            QueueApplication::STATUS_READY_FOR_PICKUP,
            QueueApplication::STATUS_COMPLETED
        ])->count();

        $attendanceRate = $todayBooked > 0 ? round(($todayCheckedIn / $todayBooked) * 100, 1) : 0;
        $printRate = $totalApplications > 0 ? round(($totalPrintedCount / $totalApplications) * 100, 1) : 0;
        $completionRate = $totalApplications > 0 ? round(($completedCount / $totalApplications) * 100, 1) : 0;

        // Service distribution (actual counts)
        $services = ServiceType::active()->withCount(['applications' => function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ]);
            }
        }])->get()->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'color' => $s->color,
            'applications_count' => $s->applications_count,
            'is_biometric_first_recording' => $s->is_biometric_first_recording,
        ]);

        // Recent applications waiting for review
        $recentPending = QueueApplication::with('serviceType')
            ->whereIn('status', [QueueApplication::STATUS_SUBMITTED, QueueApplication::STATUS_RESUBMITTED, QueueApplication::STATUS_UNDER_REVIEW])
            ->latest('submitted_at')
            ->limit(8)
            ->get();

        // Today's live queue list
        $todayQueue = QueueApplication::with('serviceType')
            ->whereHas('slot.schedule', fn($s) => $s->where('schedule_date', $today))
            ->orderBy('queue_number')
            ->get();

        return Inertia::render('Admin/Queue/Dashboard', [
            'stats' => [
                'total_applications'       => $totalApplications,
                'today_applications_count' => $todayApplicationsCount,
                'waiting_review'           => $waitingReview,
                'under_review'             => $underReview,
                'revision_required'        => $revisionRequired,
                'pre_verified'             => $preVerifiedCount,
                'queue_booked'             => $queueBookedCount,
                'arrived'                  => $arrivedCount,
                'processing'               => $processingCount,
                'ready_pickup'             => $readyPickupCount,
                'completed'                => $completedCount,
                'rejected'                 => $rejectedCount,
                'expired'                  => $expiredCount,
                'cancelled'                => $cancelledCount,
                'total_printed'            => $totalPrintedCount,
                'today_printed'            => $todayPrintedCount,
                'print_rate'               => $printRate,
                'self_count'               => $selfCount,
                'represented_count'        => $representedCount,
                'completion_rate'          => $completionRate,
                'today_quota'              => $todayTotalQuota,
                'today_booked'             => $todayBooked,
                'today_remaining'          => $todayRemaining,
                'today_checked_in'         => $todayCheckedIn,
                'attendance_rate'          => $attendanceRate,
            ],
            'services'      => $services,
            'recentPending' => $recentPending,
            'todayQueue'    => $todayQueue,
            'todayDate'     => Carbon::today()->translatedFormat('l, d F Y'),
            'filters'       => [
                'start_date'      => $startDate,
                'end_date'        => $endDate,
                'service_id'      => $serviceId,
                'status'          => $statusFilter,
                'attendance_type' => $attendanceType,
            ],
        ]);
    }
}
