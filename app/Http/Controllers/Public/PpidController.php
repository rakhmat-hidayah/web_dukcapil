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
        $settings = \App\Models\WebsiteSetting::allAsArray();
        return [
            'pages' => PpidPage::published()->ordered()->get(['id', 'slug', 'title', 'icon']),
            'officeContact' => [
                'address'       => $settings['office_address'] ?? 'Dinas Kependudukan dan Pencatatan Sipil Kab. Dompu, Jl. Soekarno-Hatta No. 09, Dompu, Nusa Tenggara Barat (84211)',
                'phone'         => $settings['office_phone'] ?? '',
                'email'         => $settings['office_email'] ?? 'dukcapilkabdompu@gmail.com',
                'hours_mon_thu' => $settings['office_hours_monday_thursday'] ?? 'Senin – Kamis: 08.00 – 16.00 WITA',
                'hours_fri'     => $settings['office_hours_friday'] ?? 'Jumat: 08.00 – 16.30 WITA',
                'hours_sat_sun' => $settings['office_hours_saturday_sunday'] ?? 'Sabtu – Minggu: Libur',
            ],
        ];
    }

    /** Static content page (pengertian, profil, tugas-fungsi, kontak, sk-ppid) */
    public function page(string $slug): Response
    {
        $page = PpidPage::published()->where('slug', $slug)->firstOrFail();

        $documents = null;
        if ($slug === 'sk-ppid') {
            $documents = PpidDocument::published()
                ->where('category', 'sk_ppid')
                ->orderBy('sort_order')
                ->orderByDesc('year')
                ->get();
        }

        if ($slug === 'kontak') {
            $settings = \App\Models\WebsiteSetting::allAsArray();
            $address  = $settings['office_address'] ?? 'Jl. Bhayangkara No. 01, Kel. Bada, Dompu, NTB 84211';
            $phone    = $settings['office_phone'] ?? '';
            $email    = $settings['office_email'] ?? 'dukcapilkabdompu@gmail.com';
            $hoursMonThu = $settings['office_hours_monday_thursday'] ?? 'Senin – Kamis: 08.00 – 16.00 WITA';
            $hoursFri    = $settings['office_hours_friday'] ?? 'Jumat: 08.00 – 16.30 WITA';

            $content = $page->content;
            $phoneDisplay = !empty($phone) ? e($phone) : '-';

            if (str_contains($content, '<!-- Alamat -->')) {
                $content = preg_replace('/(<!-- Alamat -->)(.*?)(?=<\/p>)/s', '$1' . e($address), $content);
            }
            if (str_contains($content, '<!-- Telepon -->')) {
                $content = preg_replace('/(<!-- Telepon -->)(.*?)(?=<\/p>)/s', '$1' . $phoneDisplay, $content);
            }
            if (str_contains($content, '<!-- Email -->')) {
                $content = preg_replace('/(<!-- Email -->)(.*?)(?=<\/p>)/s', '$1' . e($email), $content);
            }
            if (str_contains($content, '<!-- Jam Layanan -->')) {
                $content = preg_replace('/(<!-- Jam Layanan -->)(.*?)(?=<\/p>)/s', '$1' . e($hoursMonThu) . '<br>' . e($hoursFri), $content);
            }

            $page->content = $content;
        }

        return Inertia::render('Public/Ppid/Page', [
            'page'      => $page,
            'documents' => $documents,
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
            'requester_name'       => 'required|string|min:3|max:255',
            'requester_email'      => 'required|email|max:255',
            'requester_phone'      => 'nullable|numeric|digits_between:10,15',
            'requester_address'    => 'nullable|string|max:500',
            'requester_id_number'  => 'nullable|numeric|digits:16',
            'purpose'              => 'required|string|max:500',
            'information_requested'=> 'required|string',
            'request_method'       => 'required|in:online,langsung,surat',
            'delivery_method'      => 'required|in:email,langsung,salinan',
        ], [
            'requester_name.required'       => 'Nama lengkap pemohon wajib diisi.',
            'requester_name.min'            => 'Nama lengkap minimal 3 karakter.',
            'requester_email.required'      => 'Alamat email wajib diisi.',
            'requester_email.email'         => 'Format alamat email tidak valid (contoh: nama@email.com).',
            'requester_phone.numeric'       => 'Nomor telepon/WhatsApp harus berupa angka saja.',
            'requester_phone.digits_between'=> 'Nomor telepon/WhatsApp harus 10 hingga 15 digit angka.',
            'requester_id_number.numeric'   => 'NIK/Nomor Identitas KTP harus berupa angka saja.',
            'requester_id_number.digits'    => 'NIK/Nomor Identitas KTP harus tepat 16 digit angka.',
            'purpose.required'              => 'Tujuan penggunaan informasi wajib diisi.',
            'information_requested.required'=> 'Rincian informasi yang dibutuhkan wajib diisi.',
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

    /** Download PPID official response file attachment */
    public function downloadResponse(PpidRequest $request)
    {
        if ($request->response_file && file_exists(storage_path('app/public/' . $request->response_file))) {
            return response()->download(storage_path('app/public/' . $request->response_file));
        }

        abort(404, 'Berkas tanggapan tidak ditemukan.');
    }
}
