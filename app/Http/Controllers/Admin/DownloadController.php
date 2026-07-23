<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DownloadController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Download::with(['author', 'category'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->where('download_category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = DownloadCategory::whereNull('parent_id')->with('children')->orderBy('sort_order')->get();

        return Inertia::render('Admin/Downloads/Index', [
            'downloads' => $query->paginate(20)->withQueryString(),
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:51200',
            'download_category_id' => 'nullable|exists:download_categories,id',
            'status' => 'required|in:draft,published',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = 'downloads/' . date('Y/m') . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));

        $download = Download::create([
            'user_id' => auth()->id(),
            'download_category_id' => $request->download_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'file_size' => $file->getSize(),
            'document_number' => $request->document_number,
            'document_date' => $request->document_date,
            'status' => $request->status,
            'published_at' => $request->status === 'published' ? now() : null,
        ]);

        ActivityLogger::log("Upload dokumen unduhan: {$download->title}", $download, 'create_download');

        return redirect()->back()->with('success', 'Dokumen berhasil diupload.');
    }

    public function update(Request $request, Download $download): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:draft,published',
        ]);

        $data = $request->except('file');

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($download->file_path);
            $file = $request->file('file');
            $path = 'downloads/' . date('Y/m') . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = strtolower($file->getClientOriginalExtension());
            $data['file_size'] = $file->getSize();
        }

        $download->update($data);

        ActivityLogger::log("Memperbarui dokumen: {$download->title}", $download, 'update_download');

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Download $download): RedirectResponse
    {
        Storage::disk('public')->delete($download->file_path);
        $download->delete();

        ActivityLogger::log("Menghapus dokumen: {$download->title}", null, 'delete_download');

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }

    // Category management
    public function storeCategory(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100']);
        DownloadCategory::create([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
        ]);
        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroyCategory(DownloadCategory $downloadCategory): RedirectResponse
    {
        $downloadCategory->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }
}
