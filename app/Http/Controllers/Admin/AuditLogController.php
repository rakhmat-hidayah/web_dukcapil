<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request): InertiaResponse
    {
        $search = $request->input('search', '');
        $event = $request->input('event', '');

        $query = Activity::with('causer')->latest();

        if ($search) {
            $query->whereHas('causer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('description', 'like', "%{$search}%");
        }

        if ($event) {
            $query->where('event', $event);
        }

        $logs = $query->paginate(25)->through(function ($log) {
            return [
                'id' => $log->id,
                'description' => $log->description,
                'event' => $log->event,
                'operator' => $log->causer ? $log->causer->name : 'System',
                'properties' => $log->properties,
                'date' => $log->created_at->format('d M Y, H:i'),
                'relative_time' => $log->created_at->diffForHumans(),
            ];
        });

        // Get unique events list for filter dropdown
        $events = Activity::distinct()->pluck('event')->filter()->values();

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'events' => $events,
            'filters' => [
                'search' => $search,
                'event' => $event,
            ]
        ]);
    }
}
