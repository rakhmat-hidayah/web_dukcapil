<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceDocumentController extends Controller
{
    /**
     * Store a new requirement document for a service type.
     */
    public function store(Request $request, int $serviceTypeId): RedirectResponse|JsonResponse
    {
        $serviceType = ServiceType::findOrFail($serviceTypeId);

        $validated = $request->validate([
            'label'            => 'required|string|max:150',
            'description'      => 'nullable|string|max:500',
            'accepted_formats' => 'required|string|max:100',
            'max_size_kb'      => 'required|integer|min:512|max:32768',
            'is_required'      => 'boolean',
            'capture_hint'     => 'nullable|string|max:300',
            'capture_mode'     => 'required|in:environment,user,any',
            'applicable_to'    => 'nullable|in:all,self_only,representative_only',
            'template_url'     => 'nullable|string|max:500',
            'sort_order'       => 'nullable|integer',
        ], [
            'label.required' => 'Nama / label dokumen persyaratan wajib diisi.',
            'accepted_formats.required' => 'Format file yang diterima wajib ditentukan (contoh: jpg,jpeg,png,pdf).',
            'max_size_kb.required' => 'Ukuran maksimal file wajib ditentukan.',
            'capture_mode.required' => 'Mode kamera wajib dipilih.',
        ]);

        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = $serviceType->documents()->count() + 1;
        }

        $document = $serviceType->documents()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Persyaratan '{$document->label}' berhasil ditambahkan.",
                'document' => $document,
            ]);
        }

        return back()->with('success', "Persyaratan '{$document->label}' berhasil ditambahkan.");
    }

    /**
     * Update an existing requirement document.
     */
    public function update(Request $request, int $serviceTypeId, int $documentId): RedirectResponse|JsonResponse
    {
        $document = ServiceDocument::where('id', $documentId)
            ->where('service_type_id', $serviceTypeId)
            ->firstOrFail();

        $validated = $request->validate([
            'label'            => 'required|string|max:150',
            'description'      => 'nullable|string|max:500',
            'accepted_formats' => 'required|string|max:100',
            'max_size_kb'      => 'required|integer|min:512|max:32768',
            'is_required'      => 'boolean',
            'capture_hint'     => 'nullable|string|max:300',
            'capture_mode'     => 'required|in:environment,user,any',
            'applicable_to'    => 'nullable|in:all,self_only,representative_only',
            'template_url'     => 'nullable|string|max:500',
            'sort_order'       => 'nullable|integer',
        ], [
            'label.required' => 'Nama / label dokumen persyaratan wajib diisi.',
            'accepted_formats.required' => 'Format file yang diterima wajib ditentukan (contoh: jpg,jpeg,png,pdf).',
            'max_size_kb.required' => 'Ukuran maksimal file wajib ditentukan.',
            'capture_mode.required' => 'Mode kamera wajib dipilih.',
        ]);

        $document->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Persyaratan '{$document->label}' berhasil diperbarui.",
                'document' => $document,
            ]);
        }

        return back()->with('success', "Persyaratan '{$document->label}' berhasil diperbarui.");
    }

    /**
     * Delete a requirement document.
     */
    public function destroy(Request $request, int $serviceTypeId, int $documentId): RedirectResponse|JsonResponse
    {
        $document = ServiceDocument::where('id', $documentId)
            ->where('service_type_id', $serviceTypeId)
            ->firstOrFail();

        $label = $document->label;
        $document->applicationDocuments()->delete();
        $document->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Persyaratan '{$label}' berhasil dihapus.",
            ]);
        }

        return back()->with('success', "Persyaratan '{$label}' berhasil dihapus.");
    }

    /**
     * Reorder documents order.
     */
    public function reorder(Request $request, int $serviceTypeId): JsonResponse|RedirectResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:queue_service_documents,id',
            'orders.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->input('orders') as $item) {
            ServiceDocument::where('id', $item['id'])
                ->where('service_type_id', $serviceTypeId)
                ->update(['sort_order' => $item['sort_order']]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan persyaratan berhasil disimpan.',
            ]);
        }

        return back()->with('success', 'Urutan persyaratan berhasil disimpan.');
    }
}
