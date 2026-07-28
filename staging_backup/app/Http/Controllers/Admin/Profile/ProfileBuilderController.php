<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use App\Models\Profile\ProfileSection;
use App\Services\Profile\ProfileBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProfileBuilderController extends Controller
{
    public function index(): InertiaResponse
    {
        $sections = ProfileSection::with('setting')->orderBy('sort_order')->get();

        return Inertia::render('Admin/Profile/Builder', [
            'sections' => $sections,
        ]);
    }

    public function reorder(Request $request, ProfileBuilderService $builderService): RedirectResponse
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:profile_sections,id',
            'sections.*.is_enabled' => 'boolean',
        ]);

        $builderService->reorderSections($request->sections);

        return redirect()->back()->with('success', 'Urutan dan status modul profil berhasil disimpan.');
    }

    public function updateSetting(Request $request, ProfileSection $section, ProfileBuilderService $builderService): RedirectResponse
    {
        $data = $request->validate([
            'layout_type' => 'nullable|string',
            'bg_color' => 'nullable|string',
            'animation_type' => 'nullable|string',
            'visible_desktop' => 'boolean',
            'visible_tablet' => 'boolean',
            'visible_mobile' => 'boolean',
            'content_data' => 'nullable|array',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
        ]);

        $builderService->updateSectionSetting($section->id, $data);

        return redirect()->back()->with('success', 'Pengaturan modul ' . $section->name . ' berhasil diperbarui.');
    }
}
