<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ServiceRequirementController extends Controller
{
    /**
     * Display a listing of the service requirements in admin dashboard.
     */
    public function index(): InertiaResponse
    {
        $services = ServiceRequirement::orderBy('sort_order')->get();

        return Inertia::render('ServiceRequirements/Index', [
            'services' => $services,
        ]);
    }

    /**
     * Store a newly created service requirement in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'requirements' => 'required|string',
            'processing_time' => 'required|string|max:100',
            'cost' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        ServiceRequirement::create($data);

        return back()->with('success', 'Persyaratan layanan berhasil ditambahkan.');
    }

    /**
     * Update the specified service requirement in storage.
     */
    public function update(Request $request, ServiceRequirement $serviceRequirement): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'requirements' => 'required|string',
            'processing_time' => 'required|string|max:100',
            'cost' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $serviceRequirement->update($data);

        return back()->with('success', 'Persyaratan layanan berhasil diperbarui.');
    }

    /**
     * Remove the specified service requirement from storage.
     */
    public function destroy(ServiceRequirement $serviceRequirement): RedirectResponse
    {
        $serviceRequirement->delete();

        return back()->with('success', 'Persyaratan layanan berhasil dihapus.');
    }
}
