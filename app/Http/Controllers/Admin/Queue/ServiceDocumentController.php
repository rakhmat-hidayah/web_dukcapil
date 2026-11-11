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
            'is_required'      => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
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

        $validated['is_required'] = $request->boolean('is_required', true);
        $validated['is_active']   = $request->boolean('is_active', true);

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
            'is_required'      => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
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

        if ($request->has('is_required')) {
            $validated['is_required'] = $request->boolean('is_required');
        }
        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

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
     * Toggle active/inactive status of a requirement document.
     */
    public function toggleActive(Request $request, int $serviceTypeId, int $documentId): RedirectResponse|JsonResponse
    {
        $document = ServiceDocument::where('id', $documentId)
            ->where('service_type_id', $serviceTypeId)
            ->firstOrFail();

        $document->is_active = !$document->is_active;
        $document->save();

        $statusLabel = $document->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => "Persyaratan '{$document->label}' berhasil {$statusLabel}.",
                'is_active' => $document->is_active,
            ]);
        }

        return back()->with('success', "Persyaratan '{$document->label}' berhasil {$statusLabel}.");
    }

    /**
     * Toggle required/optional status of a requirement document.
     */
    public function toggleRequired(Request $request, int $serviceTypeId, int $documentId): RedirectResponse|JsonResponse
    {
        $document = ServiceDocument::where('id', $documentId)
            ->where('service_type_id', $serviceTypeId)
            ->firstOrFail();

        $document->is_required = !$document->is_required;
        $document->save();

        $statusLabel = $document->is_required ? 'Wajib' : 'Opsional';

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => "Ketentuan berkas '{$document->label}' diubah menjadi {$statusLabel}.",
                'is_required' => $document->is_required,
            ]);
        }

        return back()->with('success', "Ketentuan berkas '{$document->label}' diubah menjadi {$statusLabel}.");
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

        \Illuminate\Support\Facades\Cache::forget('public_queue_services');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan persyaratan berhasil disimpan.',
            ]);
        }

        return back()->with('success', 'Urutan persyaratan berhasil disimpan.');
    }
}
