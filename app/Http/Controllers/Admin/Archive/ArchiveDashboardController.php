<?php

namespace App\Http\Controllers\Admin\Archive;

use App\Http\Controllers\Controller;
use App\Models\Archive\ArchivePackage;
use App\Services\Archive\ArchiveService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveDashboardController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService
    ) {}

    public function index(Request $request): Response
    {
        // Automatically purge any packages exceeding configured retention policy
        try {
            $this->archiveService->cleanupExpiredArchives();
        } catch (\Throwable $e) {
            \Log::warning("Auto archive retention cleanup on dashboard warning: " . $e->getMessage());
        }

        $stats = $this->archiveService->getDashboardStatistics();

        $recentPackages = ArchivePackage::with(['serviceType', 'creator'])
            ->latest()
            ->limit(8)
            ->get();

        $recentProblems = ArchivePackage::with(['serviceType'])
            ->whereIn('status', [ArchivePackage::STATUS_INCOMPLETE, ArchivePackage::STATUS_FAILED, ArchivePackage::STATUS_CORRUPTED])
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Archive/Dashboard', [
            'stats' => $stats,
            'recentPackages' => $recentPackages,
            'recentProblems' => $recentProblems,
        ]);
    }
}
