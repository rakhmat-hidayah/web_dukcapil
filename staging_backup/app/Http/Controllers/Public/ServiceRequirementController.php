<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequirement;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ServiceRequirementController extends Controller
{
    /**
     * Display all active services.
     */
    public function index(): InertiaResponse
    {
        $services = ServiceRequirement::active()->get(['id', 'title', 'slug', 'icon', 'color', 'description', 'processing_time', 'cost']);

        return Inertia::render('Public/Services', [
            'services' => $services,
        ]);
    }

    /**
     * Display requirements of a specific service.
     */
    public function show(string $slug): InertiaResponse
    {
        $service = ServiceRequirement::active()->where('slug', $slug)->firstOrFail();
        $otherServices = ServiceRequirement::active()->where('id', '!=', $service->id)->get(['id', 'title', 'slug', 'icon', 'color']);

        return Inertia::render('Public/ServiceShow', [
            'service' => $service,
            'otherServices' => $otherServices,
        ]);
    }
}
