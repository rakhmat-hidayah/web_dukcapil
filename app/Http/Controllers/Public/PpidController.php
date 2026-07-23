<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PpidDocument;
use App\Models\PpidPage;
use App\Models\PpidRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PpidController extends Controller
{
    /** Shared data for all PPID pages */
    private function sharedData(): array
    {
        return [
            'pages' => PpidPage::published()->ordered()->get(['id', 'slug', 'title', 'icon']),
        ];
    }

    /** Static content page (pengertian, profil, tugas-fungsi, kontak, sk-ppid) */
    public function page(string $slug): Response
    {
        $page = PpidPage::published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/Ppid/Page', [
            'page'  => $page,
            ...$this->sharedData(),
        ]);
    }

    /** Informasi Publik document list */
    public function informasiPublik(Request $request): Response
    {
        $docs = PpidDocument::published()
            ->category('informasi_publik')
            ->when($request->subcategory, fn($q) => $q->where('subcategory', $request->subcategory))
            ->when($request->year, fn($q) => $q->where('year', $request->year))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->orderBy('sort_order')
            ->orderByDesc('year')
            ->paginate(20)
            ->withQueryString();

        $subcategories = PpidDocument::published()
            ->category('informasi_publik')
            ->whereNotNull('subcategory')
            ->distinct()
            ->pluck('subcategory');

        $years = PpidDocument::published()
            ->category('informasi_publik')
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return Inertia::render('Public/Ppid/Documents', [
            'category'     => 'informasi_publik',
            'categoryLabel'=> 'Informasi Publik',
            'documents'    => $docs,
            'subcategories'=> $subcategories,
            'years'        => $years,
            'filters'      => $request->only(['subcategory', 'year', 'search']),
            ...$this->sharedData(),
        ]);
    }

    /** Prosedur document list */
    public function prosedur(Request $request): Response
    {
        $docs = PpidDocument::published()
            ->category('prosedur')
            ->when($request->subcategory, fn($q) => $q->where('subcategory', $request->subcategory))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        $subcategories = PpidDocument::published()
            ->category('prosedur')
            ->whereNotNull('subcategory')
            ->distinct()
            ->pluck('subcategory');

        return Inertia::render('Public/Ppid/Documents', [
            'category'     => 'prosedur',
            'categoryLabel'=> 'Prosedur',
            'documents'    => $docs,
            'subcategories'=> $subcategories,
            'years'        => [],
            'filters'      => $request->only(['subcategory', 'search']),
            ...$this->sharedData(),
        ]);
    }

    /** Layanan Informasi document list */
    public function layananInformasi(Request $request): Response
    {
        $docs = PpidDocument::published()
            ->category('layanan_informasi')
            ->when($request->subcategory, fn($q) => $q->where('subcategory', $request->subcategory))
            ->when($request->year, fn($q) => $q->where('year', $request->year))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->orderBy('sort_order')
            ->orderByDesc('year')
            ->paginate(20)
            ->withQueryString();

        $subcategories = PpidDocument::published()
            ->category('layanan_informasi')
            ->whereNotNull('subcategory')
            ->distinct()
            ->pluck('subcategory');

        $years = PpidDocument::published()
            ->category('layanan_informasi')
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return Inertia::render('Public/Ppid/Documents', [
            'category'     => 'layanan_informasi',
            'categoryLabel'=> 'Layanan Informasi',
            'documents'    => $docs,
            'subcategories'=> $subcategories,
            'years'        => $years,
            'filters'      => $request->only(['subcategory', 'year', 'search']),
            ...$this->sharedData(),
        ]);
    }

    /** Online PPID request form */
    public function requestForm(): Response
    {
        return Inertia::render('Public/Ppid/Request', $this->sharedData());
    }

    /** Submit PPID request */
    public function submitRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requester_name'       => 'required|string|max:255',
            'requester_email'      => 'required|email|max:255',
            'requester_phone'      => 'nullable|string|max:20',
            'requester_address'    => 'nullable|string|max:500',
            'requester_id_number'  => 'nullable|string|max:20',
            'purpose'              => 'required|string|max:500',
            'information_requested'=> 'required|string',
            'request_method'       => 'required|in:online,langsung,surat',
            'delivery_method'      => 'required|in:email,langsung,salinan',
        ]);

        $ppid = PpidRequest::create($validated);

        return redirect()->route('public.ppid.request.success', $ppid->ticket_number)
            ->with('success', 'Permohonan informasi berhasil dikirim.');
    }

    /** Request success page */
    public function requestSuccess(string $ticket): Response
    {
        $request = PpidRequest::where('ticket_number', $ticket)->firstOrFail();

        return Inertia::render('Public/Ppid/RequestSuccess', [
            'request' => $request,
            ...$this->sharedData(),
        ]);
    }

    /** Track PPID request */
    public function trackRequest(Request $request): Response
    {
        $ppid = null;
        if ($request->ticket) {
            $ppid = PpidRequest::where('ticket_number', $request->ticket)->first();
        }

        return Inertia::render('Public/Ppid/Track', [
            'ppidRequest' => $ppid,
            'ticket'      => $request->ticket,
            ...$this->sharedData(),
        ]);
    }

    /** Download a document and increment counter */
    public function downloadDocument(PpidDocument $document)
    {
        $document->increment('download_count');

        if ($document->file_url) {
            return redirect($document->file_url);
        }

        if ($document->file_path && file_exists(storage_path('app/public/' . $document->file_path))) {
            return response()->download(storage_path('app/public/' . $document->file_path));
        }

        abort(404, 'File tidak ditemukan.');
    }
}
