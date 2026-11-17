<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankOpname;
use App\Services\BlankStock\BlankStockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlankStockOpnameController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('blank_stocks.opname.view');

        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = KtpBlankOpname::with(['checker:id,name,username', 'approver:id,name'])->latest('opname_date');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('opname_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $opnames = $query->paginate(15)->withQueryString()->through(function ($o) {
            return [
                'id'             => $o->id,
                'opname_number'  => $o->opname_number,
                'opname_date'    => Carbon::parse($o->opname_date)->format('d M Y'),
                'raw_date'       => $o->opname_date,
                'system_stock'   => $o->system_stock,
                'physical_stock' => $o->physical_stock,
                'difference'     => $o->difference,
                'checker_name'   => $o->checker ? $o->checker->name : 'Sistem',
                'approver_name'  => $o->approver ? $o->approver->name : null,
                'approved_at'    => $o->approved_at ? $o->approved_at->format('d M Y, H:i') : null,
                'status'         => $o->status,
                'status_label'   => $o->status_label,
                'status_color'   => $o->status_color,
                'notes'          => $o->notes,
                'evidence_url'   => $o->evidence_path ? asset('storage/' . $o->evidence_path) : null,
            ];
        });

        $stock = $this->stockService->getAuthoritativeStock();

        return Inertia::render('Admin/BlankStock/Opname/Index', [
            'opnames'      => $opnames,
            'filters'      => [
                'search' => $search,
                'status' => $status,
            ],
            'currentStock' => $stock->current_stock,
            'canApprove'   => $request->user()->can('blank_stocks.opname.approve') || $request->user()->hasRole(['Super Administrator', 'Administrator Utama', 'Supervisor Layanan KTP-el']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('blank_stocks.opname.create');

        $data = $request->validate([
            'opname_date'    => 'required|date',
            'physical_stock' => 'required|integer|min:0',
            'notes'          => 'nullable|string|max:1000',
            'evidence'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('blank_opnames', 'public');
        }

        $opname = $this->stockService->recordOpname($data, $request->user());

        return redirect()->back()->with('success', "Pemeriksaan fisik stok opname [{$opname->opname_number}] berhasil dicatat. Selisih fisik: {$opname->difference} keping.");
    }

    public function approve(Request $request, KtpBlankOpname $opname): RedirectResponse
    {
        $this->authorize('blank_stocks.opname.approve');

        $this->stockService->approveOpname($opname, $request->user());

        return redirect()->back()->with('success', "Berita Acara Stok Opname [{$opname->opname_number}] berhasil disahkan oleh Pimpinan.");
    }
}
