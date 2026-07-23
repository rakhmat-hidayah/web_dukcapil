<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PageController extends Controller
{
    /**
     * Display a list of dynamic pages.
     */
    public function index(Request $request): InertiaResponse
    {
        $query = Page::with('author')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pages = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show creation form.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('Admin/Pages/Form', [
            'pageData' => null,
        ]);
    }

    /**
     * Store a newly created page.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'template' => 'required|in:default,full-width,sidebar',
            'status' => 'required|in:draft,published',
            'og_image' => 'nullable|image|max:3072',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except('og_image');
        $data['slug'] = Str::slug($request->title);
        $data['user_id'] = auth()->id();

        if ($request->status === 'published' && !$request->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('og_image')) {
            $img = Image::decode($request->file('og_image'))->scale(width: 1200);
            $path = 'pages/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($path, $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));
            $data['og_image'] = $path;
        }

        $page = Page::create($data);

        ActivityLogger::log("Membuat Halaman Dinamis: {$page->title}", $page, 'create_page');

        Cache::forget('published_pages');

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil disimpan.');
    }

    /**
     * Show edit form.
     */
    public function edit(Page $page): InertiaResponse
    {
        return Inertia::render('Admin/Pages/Form', [
            'pageData' => $page,
        ]);
    }

    /**
     * Update page details.
     */
    public function update(Request $request, Page $page): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'template' => 'required|in:default,full-width,sidebar',
            'status' => 'required|in:draft,published',
            'og_image' => 'nullable|image|max:3072',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->except('og_image');
        $data['slug'] = Str::slug($request->title);

        if ($request->status === 'published' && !$page->published_at && !$request->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('og_image')) {
            if ($page->og_image) {
                Storage::disk('public')->delete($page->og_image);
            }
            $img = Image::decode($request->file('og_image'))->scale(width: 1200);
            $path = 'pages/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($path, $img->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 80)));
            $data['og_image'] = $path;
        }

        $page->update($data);

        ActivityLogger::log("Memperbarui Halaman Dinamis: {$page->title}", $page, 'update_page');

        Cache::forget('published_pages');

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    /**
     * Delete page (soft delete).
     */
    public function destroy(Page $page): RedirectResponse
    {
        $title = $page->title;
        $page->delete();

        ActivityLogger::log("Menghapus Halaman Dinamis: {$title}", null, 'delete_page');

        Cache::forget('published_pages');

        return redirect()->back()->with('success', 'Halaman berhasil dihapus.');
    }
}

