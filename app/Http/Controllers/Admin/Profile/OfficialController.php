<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use App\Models\Profile\Official;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OfficialController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Official::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('position_title', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%");
            });
        }

        $officials = $query->orderBy('sort_order')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Profile/Officials/Index', [
            'officials' => $officials,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Admin/Profile/Officials/Form', [
            'official' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'position_title' => 'required|string|max:255',
            'rank_golongan' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'main_duties' => 'nullable|string',
            'office_address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'office_hours' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,retired,transferred',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('officials', 'public');
        }

        $official = Official::create($data);

        return redirect()->route('admin.profile.officials.index')->with('success', 'Data pejabat ' . $official->name . ' berhasil ditambahkan.');
    }

    public function edit(Official $official): InertiaResponse
    {
        $official->load(['educations', 'histories', 'achievements', 'socialLinks', 'documents']);

        return Inertia::render('Admin/Profile/Officials/Form', [
            'official' => $official,
        ]);
    }

    public function update(Request $request, Official $official): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'position_title' => 'required|string|max:255',
            'rank_golongan' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'main_duties' => 'nullable|string',
            'office_address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'office_hours' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,retired,transferred',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('officials', 'public');
        }

        $official->update($data);

        return redirect()->route('admin.profile.officials.index')->with('success', 'Data pejabat ' . $official->name . ' berhasil diperbarui.');
    }

    public function destroy(Official $official): RedirectResponse
    {
        $name = $official->name;
        $official->delete();

        return redirect()->back()->with('success', 'Data pejabat ' . $name . ' berhasil dihapus.');
    }
}
