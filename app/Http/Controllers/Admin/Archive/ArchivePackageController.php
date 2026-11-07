<?php

namespace App\Http\Controllers\Admin\Archive;

use App\Http\Controllers\Controller;
use App\Models\Archive\ArchivePackage;
use App\Models\Queue\ServiceType;
use App\Services\Archive\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArchivePackageController extends Controller
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
            \Log::warning("Auto archive retention cleanup on package index warning: " . $e->getMessage());
        }

        $query = ArchivePackage::with(['serviceType', 'creator', 'confirmedByUser'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('period')) {
            $query->where('archive_period', $request->input('period'));
        }
        if ($request->filled('service_type_id')) {
            $query->where('service_type_id', $request->input('service_type_id'));
        }
        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where('package_code', 'like', "%{$s}%");
        }

        $packages = $query->paginate(15)->withQueryString();
        $serviceTypes = ServiceType::where('is_active', true)->select('id', 'name')->get();

        return Inertia::render('Admin/Archive/Packages/Index', [
            'packages' => $packages,
            'filters' => $request->only(['status', 'period', 'service_type_id', 'search']),
            'serviceTypes' => $serviceTypes,
        ]);
    }

    public function show(int $id): Response
    {
        $package = ArchivePackage::with([
            'serviceType',
            'creator',
            'confirmedByUser',
            'items.application.serviceType',
            'auditLogs.user',
        ])->findOrFail($id);

        return Inertia::render('Admin/Archive/Packages/Show', [
            'package' => $package,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
            'service_type_id' => 'nullable|exists:queue_service_types,id',
            'application_ids' => 'nullable|array',
            'application_ids.*' => 'integer|exists:queue_applications,id',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        try {
            $package = $this->archiveService->createPackage($validated, $request->user());

            if ($package->status === ArchivePackage::STATUS_INCOMPLETE) {
                return redirect()->route('admin.archive.packages.show', $package->id)
                    ->with('error', 'Paket arsip berhasil dicatat, namun ditandai TIDAK LENGKAP karena ada dokumen fisik yang hilang/rusak.');
            }

            return redirect()->route('admin.archive.packages.show', $package->id)
                ->with('success', "Paket arsip [{$package->package_code}] berhasil dibuat dan siap diunduh.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat paket arsip: ' . $e->getMessage());
        }
    }

    public function download(int $id, Request $request): BinaryFileResponse
    {
        $package = ArchivePackage::findOrFail($id);
        return $this->archiveService->downloadPackage($package, $request->user());
    }

    public function confirm(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'storage_location_notes' => 'required|string|min:5|max:1000',
        ]);

        $package = ArchivePackage::findOrFail($id);

        $this->archiveService->confirmPackageStorage(
            $package,
            $request->input('storage_location_notes'),
            $request->user()
        );

        return back()->with('success', "Konfirmasi penyimpanan arsip oleh operator untuk paket [{$package->package_code}] berhasil dicatat.");
    }

    public function verifyIntegrity(int $id): JsonResponse|RedirectResponse
    {
        $package = ArchivePackage::findOrFail($id);
        $result = $this->archiveService->verifyPackageIntegrity($package);

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        if ($result['valid']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function rebuild(int $id, Request $request): RedirectResponse
    {
        $package = ArchivePackage::findOrFail($id);

        try {
            $updated = $this->archiveService->rebuildPackage($package, $request->user());

            if ($updated->status === ArchivePackage::STATUS_INCOMPLETE) {
                return back()->with('error', 'Proses ulang selesai, namun tidak ditemukan berkas fisik yang lengkap di storage server untuk permohonan pada paket ini.');
            }

            return back()->with('success', "Paket arsip [{$updated->package_code}] berhasil dikemas ulang ({$updated->total_files} berkas) dan siap diunduh.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses ulang paket arsip: ' . $e->getMessage());
        }
    }

    public function destroy(int $id, Request $request): RedirectResponse
    {
        $package = ArchivePackage::findOrFail($id);
        $code = $package->package_code;

        try {
            $this->archiveService->deletePackage($package, $request->user());
            return redirect()->route('admin.archive.packages.index')
                ->with('success', "Paket arsip [{$code}] berhasil dihapus.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus paket arsip: ' . $e->getMessage());
        }
    }
}
