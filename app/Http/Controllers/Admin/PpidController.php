<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpidDocument;
use App\Models\PpidPage;
use App\Models\PpidRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PpidController extends Controller
{
    /* ─── PAGES ─────────────────────────────────── */

    public function pagesIndex(): Response
    {
        return Inertia::render('Admin/Ppid/Pages/Index', [
            'pages' => PpidPage::ordered()->get(),
        ]);
    }

    public function pagesEdit(PpidPage $page): Response
    {
        return Inertia::render('Admin/Ppid/Pages/Edit', ['page' => $page]);
    }

    public function pagesUpdate(Request $request, PpidPage $page): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'icon'             => 'nullable|string|max:50',
            'content'          => 'required|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'sort_order'       => 'integer',
            'is_published'     => 'boolean',
        ]);

        $page->update($validated);

        return back()->with('success', 'Halaman PPID berhasil diperbarui.');
    }

    /* ─── DOCUMENTS ──────────────────────────────── */

    public function documentsIndex(Request $request): Response
    {
        $docs = PpidDocument::query()
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderByDesc('year')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Ppid/Documents/Index', [
            'documents'       => $docs,
            'categoryLabels'  => PpidDocument::$categoryLabels,
            'filters'         => $request->only(['category', 'search']),
        ]);
    }

    public function documentsCreate(): Response
    {
        return Inertia::render('Admin/Ppid/Documents/Create', [
            'categoryLabels' => PpidDocument::$categoryLabels,
        ]);
    }

    public function documentsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category'    => 'required|in:informasi_publik,prosedur,layanan_informasi',
            'subcategory' => 'nullable|string|max:100',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file'        => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:20480',
            'file_url'    => 'nullable|url',
            'year'        => 'nullable|integer|min:2000|max:2099',
            'sort_order'  => 'integer',
            'is_published'=> 'boolean',
        ]);

        if ($request->hasFile('file')) {
            $file   = $request->file('file');
            $path   = $file->store('ppid-documents', 'public');
            $validated['file_path'] = $path;
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
        }
        unset($validated['file']);

        $validated['created_by'] = auth()->id();

        PpidDocument::create($validated);

        return redirect()->route('admin.ppid.documents.index')
            ->with('success', 'Dokumen PPID berhasil ditambahkan.');
    }

    public function documentsEdit(PpidDocument $document): Response
    {
        return Inertia::render('Admin/Ppid/Documents/Edit', [
            'document'       => $document,
            'categoryLabels' => PpidDocument::$categoryLabels,
        ]);
    }

    public function documentsUpdate(Request $request, PpidDocument $document): RedirectResponse
    {
        $validated = $request->validate([
            'category'    => 'required|in:informasi_publik,prosedur,layanan_informasi',
            'subcategory' => 'nullable|string|max:100',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file'        => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:20480',
            'file_url'    => 'nullable|url',
            'year'        => 'nullable|integer|min:2000|max:2099',
            'sort_order'  => 'integer',
            'is_published'=> 'boolean',
        ]);

        if ($request->hasFile('file')) {
            if ($document->file_path) Storage::disk('public')->delete($document->file_path);
            $file   = $request->file('file');
            $path   = $file->store('ppid-documents', 'public');
            $validated['file_path'] = $path;
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
        }
        unset($validated['file']);

        $document->update($validated);

        return redirect()->route('admin.ppid.documents.index')
            ->with('success', 'Dokumen PPID berhasil diperbarui.');
    }

    public function documentsDestroy(PpidDocument $document): RedirectResponse
    {
        if ($document->file_path) Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    /* ─── REQUESTS ───────────────────────────────── */

    public function requestsIndex(Request $request): Response
    {
        $items = PpidRequest::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('ticket_number', 'like', '%' . $request->search . '%')
                   ->orWhere('requester_name', 'like', '%' . $request->search . '%')
                   ->orWhere('requester_email', 'like', '%' . $request->search . '%');
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'diterima' => PpidRequest::where('status', 'diterima')->count(),
            'diproses' => PpidRequest::where('status', 'diproses')->count(),
            'selesai'  => PpidRequest::where('status', 'selesai')->count(),
            'ditolak'  => PpidRequest::where('status', 'ditolak')->count(),
        ];

        return Inertia::render('Admin/Ppid/Requests/Index', [
            'requests'     => $items,
            'summary'      => $summary,
            'statusLabels' => PpidRequest::$statusLabels,
            'statusColors' => PpidRequest::$statusColors,
            'filters'      => $request->only(['status', 'search']),
        ]);
    }

    public function requestsShow(PpidRequest $request): Response
    {
        return Inertia::render('Admin/Ppid/Requests/Show', [
            'ppidRequest'  => $request->load('respondedBy'),
            'statusLabels' => PpidRequest::$statusLabels,
            'statusColors' => PpidRequest::$statusColors,
        ]);
    }

    public function requestsRespond(Request $request, PpidRequest $ppidRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status'         => 'required|in:diterima,diproses,selesai,ditolak',
            'response_notes' => 'nullable|string',
            'response_file'  => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        if ($request->hasFile('response_file')) {
            $path = $request->file('response_file')->store('ppid-responses', 'public');
            $validated['response_file'] = $path;
        }

        $validated['responded_by'] = auth()->id();
        $validated['responded_at'] = now();

        $ppidRequest->update($validated);

        return back()->with('success', 'Permohonan PPID berhasil direspons.');
    }
}
