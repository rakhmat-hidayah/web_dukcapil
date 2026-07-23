<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Innovation;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InnovationController extends Controller
{
    /**
     * Display all active innovations.
     */
    public function index(): InertiaResponse
    {
        $innovations = Innovation::active()->get(['id', 'title', 'slug', 'icon', 'color', 'description']);

        return Inertia::render('Public/Innovations', [
            'innovations' => $innovations,
        ]);
    }

    /**
     * Display a specific innovation detail.
     */
    public function show(string $slug): InertiaResponse
    {
        $innovation = Innovation::active()->where('slug', $slug)->firstOrFail();
        $otherInnovations = Innovation::active()->where('id', '!=', $innovation->id)->get(['id', 'title', 'slug', 'icon', 'color']);

        return Inertia::render('Public/InnovationShow', [
            'innovation' => $innovation,
            'otherInnovations' => $otherInnovations,
        ]);
    }
}
