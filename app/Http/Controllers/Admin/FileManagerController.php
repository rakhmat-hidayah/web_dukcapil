<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FileEntry;
use App\Models\FileFolder;
use App\Services\ActivityLogger;
use App\Services\FileManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FileManagerController extends Controller
{
    /**
     * Display the file manager contents.
     */
    public function index(Request $request): InertiaResponse
    {
        $folderId = $request->input('folder_id') ? (int) $request->input('folder_id') : null;
        $search = $request->input('search', '');

        // Fetch directory contents
        $contents = FileManagerService::getContents($folderId, $search);

        // Fetch breadcrumbs
        $breadcrumbs = [];
        if ($folderId) {
            $currentFolder = FileFolder::find($folderId);
            while ($currentFolder) {
                array_unshift($breadcrumbs, [
                    'id' => $currentFolder->id,
                    'name' => $currentFolder->name,
                ]);
                $currentFolder = $currentFolder->parent;
            }
        }

        return Inertia::render('Admin/FileManager/Index', [
            'folders' => $contents['folders'],
            'files' => $contents['files'],
            'breadcrumbs' => $breadcrumbs,
            'currentFolderId' => $folderId,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * Create a new folder.
     */
    public function createFolder(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:file_folders,id',
        ]);

        $folder = FileManagerService::createFolder(
            $request->name,
            $request->parent_id,
            $request->user()->id
        );

        ActivityLogger::log("Membuat folder baru: {$folder->name}", $folder, 'create_folder');

        return redirect()->back()->with('success', 'Folder berhasil dibuat.');
    }

    /**
     * Upload a new file.
     */
    public function uploadFile(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:20480', // limit 20MB
            'folder_id' => 'nullable|exists:file_folders,id',
        ]);

        if ($request->hasFile('file')) {
            $file = FileManagerService::uploadFile(
                $request->file('file'),
                $request->folder_id,
                $request->user()->id
            );

            ActivityLogger::log("Mengupload file baru: {$file->original_name}", $file, 'upload_file');
            return redirect()->back()->with('success', 'File berhasil diupload.');
        }

        return redirect()->back()->with('error', 'Gagal mengupload file.');
    }

    /**
     * Upload a new version for an existing file.
     */
    public function uploadVersion(Request $request, FileEntry $file): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB
        ]);

        if ($request->hasFile('file')) {
            $newVersion = FileManagerService::uploadNewVersion(
                $file->id,
                $request->file('file'),
                $request->user()->id
            );

            ActivityLogger::log("Mengupload versi baru untuk file: {$file->original_name} (v{$newVersion->version})", $newVersion, 'upload_version');
            return redirect()->back()->with('success', 'Versi baru berhasil diupload.');
        }

        return redirect()->back()->with('error', 'Gagal mengupload versi baru.');
    }

    /**
     * Delete file entry (soft-delete).
     */
    public function destroyFile(FileEntry $file): RedirectResponse
    {
        $fileName = $file->name;
        FileManagerService::softDeleteFile($file->id);

        ActivityLogger::log("Menghapus file (ke tempat sampah): {$fileName}", null, 'delete_file');

        return redirect()->back()->with('success', 'File berhasil dihapus.');
    }

    /**
     * Delete folder (cascading).
     */
    public function destroyFolder(FileFolder $folder): RedirectResponse
    {
        $folderName = $folder->name;
        FileManagerService::deleteFolder($folder->id);

        ActivityLogger::log("Menghapus folder dan seluruh isinya: {$folderName}", null, 'delete_folder');

        return redirect()->back()->with('success', 'Folder berhasil dihapus.');
    }
}
