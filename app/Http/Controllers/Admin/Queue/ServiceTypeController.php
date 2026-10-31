<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServiceTypeController extends Controller
{
    public function index(): Response
    {
        $serviceTypes = ServiceType::withCount(['documents', 'applications'])
            ->with(['documents' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Queue/ServiceTypes/Index', [
            'serviceTypes' => $serviceTypes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Queue/ServiceTypes/Form', [
            'serviceType' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'slug'                  => 'nullable|string|max:150|unique:queue_service_types,slug',
            'short_description'     => 'nullable|string|max:300',
            'long_description'      => 'nullable|string',
            'icon'                  => 'nullable|string|max:50',
            'color'                 => 'required|string|max:20',
            'quota_per_day'         => 'required|integer|min:1|max:500',
            'processing_days_label'        => 'required|string|max:50',
            'cost_label'                   => 'required|string|max:50',
            'requires_face_photo'          => 'boolean',
            'allow_self'                   => 'boolean',
            'allow_family_in_kk'           => 'boolean',
            'allow_family_out_kk'          => 'boolean',
            'allow_other'                  => 'boolean',
            'require_power_of_attorney'    => 'boolean',
            'require_absence_reason'       => 'boolean',
            'is_biometric_first_recording' => 'boolean',
            'policy_notes'                 => 'nullable|string',
            'sort_order'                   => 'integer',
            'is_active'                    => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $service = ServiceType::create($validated);

        return redirect()->route('admin.queue.service-types.edit', $service->id)
            ->with('success', "Jenis layanan {$service->name} berhasil dibuat. Silakan kelola berkas persyaratannya.");
    }

    public function edit(int $id): Response
    {
        $serviceType = ServiceType::with(['documents' => fn($q) => $q->orderBy('sort_order')])
            ->findOrFail($id);

        return Inertia::render('Admin/Queue/ServiceTypes/Form', [
            'serviceType' => $serviceType,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $serviceType = ServiceType::findOrFail($id);

        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'slug'                  => "required|string|max:150|unique:queue_service_types,slug,{$serviceType->id}",
            'short_description'     => 'nullable|string|max:300',
            'long_description'      => 'nullable|string',
            'icon'                  => 'nullable|string|max:50',
            'color'                 => 'required|string|max:20',
            'quota_per_day'         => 'required|integer|min:1|max:500',
            'processing_days_label'        => 'required|string|max:50',
            'cost_label'                   => 'required|string|max:50',
            'requires_face_photo'          => 'boolean',
            'allow_self'                   => 'boolean',
            'allow_family_in_kk'           => 'boolean',
            'allow_family_out_kk'          => 'boolean',
            'allow_other'                  => 'boolean',
            'require_power_of_attorney'    => 'boolean',
            'require_absence_reason'       => 'boolean',
            'is_biometric_first_recording' => 'boolean',
            'policy_notes'                 => 'nullable|string',
            'sort_order'                   => 'integer',
            'is_active'                    => 'boolean',
        ]);

        $serviceType->update($validated);

        return back()->with('success', "Jenis layanan {$serviceType->name} berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $serviceType = ServiceType::withCount('applications')->findOrFail($id);

        if ($serviceType->applications_count > 0) {
            return back()->with('error', "Layanan {$serviceType->name} tidak dapat dihapus karena sudah memiliki {$serviceType->applications_count} data pendaftaran.");
        }

        $name = $serviceType->name;
        $serviceType->delete();

        return redirect()->route('admin.queue.service-types.index')
            ->with('success', "Jenis layanan {$name} berhasil dihapus.");
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $serviceType = ServiceType::findOrFail($id);
        $serviceType->update(['is_active' => !$serviceType->is_active]);

        $statusStr = $serviceType->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Layanan {$serviceType->name} berhasil {$statusStr}.");
    }
}
