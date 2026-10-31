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

    /**
     * Pre-Flight Validation API Endpoint for Admin Downloads.
     */
    public function check(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $result = \App\Services\DownloadPolicyService::validateDownload($id, auth()->user());

        if (!$result['allowed']) {
            \App\Services\DownloadPolicyService::logDownloadAttempt($result, $request, auth()->user());
        }

        return response()->json([
            'allowed'     => $result['allowed'],
            'reason'      => $result['reason'],
            'reason_text' => $result['reason_text'],
            'size_bytes'  => $result['size_bytes'],
            'size_human'  => $result['size_human'],
            'file_name'   => $result['file_name'],
            'file_type'   => $result['file_type'],
        ]);
    }

    /**
     * Memory-Safe Admin File Streaming Download.
     */
    public function download(Request $request, int $id)
    {
        $result = \App\Services\DownloadPolicyService::validateDownload($id, auth()->user());

        if (!$result['allowed']) {
            \App\Services\DownloadPolicyService::logDownloadAttempt($result, $request, auth()->user());
            return redirect()->back()->with('error', $result['reason_text']);
        }

        \App\Services\DownloadPolicyService::logDownloadAttempt($result, $request, auth()->user());

        return \App\Services\DownloadPolicyService::createStreamResponse($result);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'download_category_id' => 'nullable',
            'status' => 'required|in:draft,published',
        ]);

        $path = null;
        $originalName = null;
        $fileType = null;
        $fileSize = 0;

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileType = strtolower($file->getClientOriginalExtension());
            $fileSize = $file->getSize();

            if ($fileSize > 3 * 1024 * 1024) {
                return redirect()->back()->withErrors(['file' => 'Ukuran berkas dokumen tidak boleh lebih dari 3 MB.']);
            }

            $path = \App\Services\MediaStorage::store($file, 'downloads', Str::slug($request->title));
        } elseif ($request->filled('file_base64')) {
            $base64Data = $request->file_base64;
            $originalName = $request->file_name ?? 'document.pdf';
            $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'pdf');

            if (str_contains($base64Data, ';base64,')) {
                $base64Data = explode(';base64,', $base64Data)[1];
            }

            $binaryData = base64_decode($base64Data);
            $fileSize = strlen($binaryData);

            if ($fileSize > 3 * 1024 * 1024) {
                return redirect()->back()->withErrors(['file' => 'Ukuran berkas dokumen tidak boleh lebih dari 3 MB.']);
            }

            $filename = Str::slug($request->title) . '-' . date('Ymd-His') . '-' . Str::random(6) . '.' . $fileType;
            $path = \App\Services\MediaStorage::put("downloads/{$filename}", $binaryData);
        } else {
            return redirect()->back()->withErrors(['file' => 'Berkas dokumen gagal diunggah atau belum dipilih. Maksimal ukuran file 3 MB.']);
        }

        $catId = is_numeric($request->download_category_id) ? (int)$request->download_category_id : null;

        $download = Download::create([
            'user_id' => auth()->id(),
            'download_category_id' => $catId,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
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
            'download_category_id' => 'nullable',
        ]);

        $data = $request->except(['file', 'file_base64', 'file_name', 'remove_file']);
        $data['download_category_id'] = is_numeric($request->download_category_id) ? (int)$request->download_category_id : null;

        if ($request->boolean('remove_file')) {
            if ($download->file_path) {
                \App\Services\MediaStorage::delete($download->file_path);
            }
            $data['file_path'] = null;
            $data['file_name'] = null;
            $data['file_type'] = null;
            $data['file_size'] = null;
        } elseif ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileType = strtolower($file->getClientOriginalExtension());
            $fileSize = $file->getSize();

            if ($fileSize > 3 * 1024 * 1024) {
                return redirect()->back()->withErrors(['file' => 'Ukuran berkas dokumen tidak boleh lebih dari 3 MB.']);
            }

            if ($download->file_path) {
                \App\Services\MediaStorage::delete($download->file_path);
            }

            $path = \App\Services\MediaStorage::store($file, 'downloads', Str::slug($request->title));
            $data['file_path'] = $path;
            $data['file_name'] = $originalName;
            $data['file_type'] = $fileType;
            $data['file_size'] = $fileSize;
        } elseif ($request->filled('file_base64')) {
            $base64Data = $request->file_base64;
            $originalName = $request->file_name ?? 'document.pdf';
            $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'pdf');

            if (str_contains($base64Data, ';base64,')) {
                $base64Data = explode(';base64,', $base64Data)[1];
            }

            $binaryData = base64_decode($base64Data);
            $fileSize = strlen($binaryData);

            if ($fileSize > 3 * 1024 * 1024) {
                return redirect()->back()->withErrors(['file' => 'Ukuran berkas dokumen tidak boleh lebih dari 3 MB.']);
            }

            if ($download->file_path) {
                \App\Services\MediaStorage::delete($download->file_path);
            }

            $filename = Str::slug($request->title) . '-' . date('Ymd-His') . '-' . Str::random(6) . '.' . $fileType;
            $path = \App\Services\MediaStorage::put("downloads/{$filename}", $binaryData);
            $data['file_path'] = $path;
            $data['file_name'] = $originalName;
            $data['file_type'] = $fileType;
            $data['file_size'] = $fileSize;
        }

        $download->update($data);

        ActivityLogger::log("Memperbarui dokumen: {$download->title}", $download, 'update_download');

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Download $download): RedirectResponse
    {
        if ($download->file_path) {
            \App\Services\MediaStorage::delete($download->file_path);
        }
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
