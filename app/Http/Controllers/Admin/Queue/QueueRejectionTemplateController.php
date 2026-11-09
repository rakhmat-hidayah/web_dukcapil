<?php

namespace App\Http\Controllers\Admin\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueRejectionTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueRejectionTemplateController extends Controller
{
    /**
     * Get list of templates as JSON.
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $templates = QueueRejectionTemplate::when($type, function ($q) use ($type) {
            $q->whereIn('type', [$type, 'all']);
        })->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:191',
            'content'    => 'required|string',
            'type'       => 'nullable|string|in:rejection,revision,all',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ], [
            'title.required'   => 'Judul template wajib diisi.',
            'content.required' => 'Isi teks template penolakan wajib diisi.',
        ]);

        $maxSort = QueueRejectionTemplate::max('sort_order') ?? 0;

        $template = QueueRejectionTemplate::create([
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'type'       => $validated['type'] ?? 'all',
            'sort_order' => $validated['sort_order'] ?? ($maxSort + 1),
            'is_active'  => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Template penolakan berhasil ditambahkan.',
                'template' => $template,
            ]);
        }

        return back()->with('success', 'Template penolakan berhasil ditambahkan.');
    }

    /**
     * Update an existing template.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $template = QueueRejectionTemplate::findOrFail($id);

        $validated = $request->validate([
            'title'      => 'required|string|max:191',
            'content'    => 'required|string',
            'type'       => 'nullable|string|in:rejection,revision,all',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ], [
            'title.required'   => 'Judul template wajib diisi.',
            'content.required' => 'Isi teks template penolakan wajib diisi.',
        ]);

        $template->update([
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'type'       => $validated['type'] ?? $template->type,
            'sort_order' => $validated['sort_order'] ?? $template->sort_order,
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : $template->is_active,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Template penolakan berhasil diperbarui.',
                'template' => $template,
            ]);
        }

        return back()->with('success', 'Template penolakan berhasil diperbarui.');
    }

    /**
     * Remove a template.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $template = QueueRejectionTemplate::findOrFail($id);
        $title = $template->title;
        $template->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Template \"{$title}\" berhasil dihapus.",
            ]);
        }

        return back()->with('success', "Template \"{$title}\" berhasil dihapus.");
    }
}
