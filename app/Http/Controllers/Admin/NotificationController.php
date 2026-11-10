<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Queue\QueueApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get recent notifications and pending verifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->limit(20)->get()->map(function ($notif) {
            return [
                'id' => $notif->id,
                'data' => $notif->data,
                'read_at' => $notif->read_at,
                'created_at' => $notif->created_at->diffForHumans(),
            ];
        });

        $unreadCount = $user->unreadNotifications()->count();

        $pendingApplicants = ApplicantAccount::where('identity_status', ApplicantAccount::IDENTITY_PENDING)->count();
        $pendingApplications = QueueApplication::whereIn('status', [
            QueueApplication::STATUS_SUBMITTED,
            QueueApplication::STATUS_RESUBMITTED,
            QueueApplication::STATUS_UNDER_REVIEW,
        ])->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'pending_verifications' => [
                'applicants_count' => $pendingApplicants,
                'applications_count' => $pendingApplications,
                'total' => $pendingApplicants + $pendingApplications,
            ],
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
