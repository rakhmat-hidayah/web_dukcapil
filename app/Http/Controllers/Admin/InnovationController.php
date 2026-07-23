<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Innovation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class InnovationController extends Controller
{
    /**
     * Display a listing of the innovations in admin dashboard.
     */
    public function index(): InertiaResponse
    {
        $innovations = Innovation::orderBy('sort_order')->get();

        return Inertia::render('Innovations/Index', [
            'innovations' => $innovations,
        ]);
    }

    /**
     * Store a newly created innovation in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'youtube_url' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Standardize YouTube URL to embed format if applicable
        if (!empty($data['youtube_url'])) {
            $data['youtube_url'] = $this->convertToEmbedUrl($data['youtube_url']);
        }

        Innovation::create($data);

        return back()->with('success', 'Inovasi baru berhasil ditambahkan.');
    }

    /**
     * Update the specified innovation in storage.
     */
    public function update(Request $request, Innovation $innovation): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'youtube_url' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (!empty($data['youtube_url'])) {
            $data['youtube_url'] = $this->convertToEmbedUrl($data['youtube_url']);
        }

        $innovation->update($data);

        return back()->with('success', 'Data inovasi berhasil diperbarui.');
    }

    /**
     * Remove the specified innovation from storage.
     */
    public function destroy(Innovation $innovation): RedirectResponse
    {
        $innovation->delete();

        return back()->with('success', 'Inovasi berhasil dihapus.');
    }

    /**
     * Helper method to convert standard watch URLs to embed URLs.
     */
    private function convertToEmbedUrl(string $url): string
    {
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/" . $matches[1];
        }
        if (preg_match('/youtu\.be\/([a-zA-Z0-9_\-]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/" . $matches[1];
        }
        return $url;
    }
}
