<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * Stream the secure download file from local storage using temporary signed URL.
     */
    public function stream(Request $request, int $id): BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        // 1. Verify signed signature (Laravel handles this via signed middleware, but we check here too for safety)
        if (!$request->hasValidSignature()) {
            abort(403, 'Tautan unduhan kedaluwarsa atau tanda tangan tidak valid.');
        }

        $doc = Download::findOrFail($id);

        if (!Storage::disk('public')->exists($doc->file_path)) {
            abort(404, 'File fisik tidak ditemukan di server.');
        }

        // 2. Increment download counter
        $doc->increment('download_count');

        // 3. Stream file securely without exposing physical paths
        return response()->download(Storage::disk('public')->path($doc->file_path), $doc->file_name);
    }
}
