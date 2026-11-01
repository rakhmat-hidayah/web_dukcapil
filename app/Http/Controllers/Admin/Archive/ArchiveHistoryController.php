<?php

namespace App\Http\Controllers\Admin\Archive;

use App\Http\Controllers\Controller;
use App\Models\Archive\ArchiveAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ArchiveAuditLog::with(['package', 'application', 'user'])
            ->latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('package_id')) {
            $query->where('archive_package_id', $request->input('package_id'));
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $logs = $query->paginate(25)->withQueryString();

        $actions = ArchiveAuditLog::select('action')->distinct()->pluck('action');
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['operator_arsip', 'Super Administrator', 'Website Administrator']);
        })->select('id', 'name')->get();

        return Inertia::render('Admin/Archive/History', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'user_id', 'package_id', 'date']),
            'availableActions' => $actions,
            'users' => $users,
        ]);
    }
}
