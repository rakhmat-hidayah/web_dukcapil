<?php

namespace App\Http\Controllers\Admin\Archive;

use App\Http\Controllers\Controller;
use App\Models\Archive\ArchivePackage;
use App\Models\Archive\ArchivePackageItem;
use App\Models\Archive\ArchiveSetting;
use App\Models\Queue\ServiceType;
use App\Services\Archive\ArchiveService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveApplicationController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['period', 'service_type_id', 'search', 'start_date', 'end_date', 'include_archived']);

        $query = $this->archiveService->getEligibleApplicationsQuery($filters);

        $applications = $query->paginate(20)->withQueryString();

        // Attach completeness check details to each item on the current page
        $applications->getCollection()->transform(function ($app) {
            $completeness = $this->archiveService->checkApplicationCompleteness($app);
            $app->is_archive_ready = $completeness['is_complete'];
            $app->archive_issues = $completeness['issues'];
            $app->valid_doc_count = $completeness['document_count'];
            $app->total_doc_size_bytes = $completeness['total_size_bytes'];
            return $app;
        });

        $serviceTypes = ServiceType::where('is_active', true)->select('id', 'name', 'slug')->get();

        // Get monthly periods available for quick selection
        $eligibleStatuses = ArchiveSetting::get('eligible_statuses', [
            \App\Models\Queue\QueueApplication::STATUS_READY_FOR_PICKUP,
            \App\Models\Queue\QueueApplication::STATUS_COMPLETED,
        ]);

        $availablePeriods = \App\Models\Queue\QueueApplication::whereIn('status', $eligibleStatuses)
            ->selectRaw("DATE_FORMAT(COALESCE(completed_at, handover_at, updated_at), '%Y-%m') as period, count(*) as count")
            ->whereNotNull('updated_at')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->pluck('count', 'period')
            ->toArray();

        return Inertia::render('Admin/Archive/ReadyApplications', [
            'applications' => $applications,
            'filters' => $filters,
            'serviceTypes' => $serviceTypes,
            'availablePeriods' => $availablePeriods,
        ]);
    }
}
