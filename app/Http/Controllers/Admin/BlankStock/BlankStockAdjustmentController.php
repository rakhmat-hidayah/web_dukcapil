<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankAdjustment;
use App\Models\BlankStock\KtpBlankOpname;
use App\Services\BlankStock\BlankStockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlankStockAdjustmentController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('blank_stocks.adjustment.create');

        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = KtpBlankAdjustment::with(['requester:id,name,username', 'approver:id,name', 'opname:id,opname_number'])
            ->latest('adjustment_date');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $adjustments = $query->paginate(15)->withQueryString()->through(function ($a) {
            return [
                'id'                => $a->id,
                'adjustment_number' => $a->adjustment_number,
                'adjustment_date'   => Carbon::parse($a->adjustment_date)->format('d M Y'),
                'type'              => $a->type,
                'quantity'          => $a->quantity,
                'reason'            => $a->reason,
                'opname_number'     => $a->opname ? $a->opname->opname_number : null,
                'requester_name'    => $a->requester ? $a->requester->name : 'Sistem',
                'approver_name'     => $a->approver ? $a->approver->name : null,
                'approved_at'       => $a->approved_at ? $a->approved_at->format('d M Y, H:i') : null,
                'status'            => $a->status,
                'status_label'      => $a->status_label,
                'status_color'      => $a->status_color,
                'attachment_url'    => $a->attachment_path ? asset('storage/' . $a->attachment_path) : null,
                'balance_before'    => $a->balance_before,
                'balance_after'     => $a->balance_after,
            ];
        });

        $stock = $this->stockService->getAuthoritativeStock();
        $approvedOpnames = KtpBlankOpname::where('status', KtpBlankOpname::STATUS_APPROVED)
            ->latest('id')
            ->limit(20)
            ->get(['id', 'opname_number', 'difference', 'opname_date']);

        return Inertia::render('Admin/BlankStock/Adjustments/Index', [
            'adjustments'     => $adjustments,
            'filters'         => [
                'search' => $search,
                'status' => $status,
            ],
            'currentStock'    => $stock->current_stock,
            'approvedOpnames' => $approvedOpnames,
            'canApprove'      => $request->user()->can('blank_stocks.adjustment.approve') || $request->user()->hasRole(['Super Administrator', 'Administrator Utama', 'Supervisor Layanan KTP-el']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('blank_stocks.adjustment.create');

        $data = $request->validate([
            'adjustment_date' => 'required|date',
            'type'            => 'required|string|in:ADDITION,DEDUCTION',
            'quantity'        => 'required|integer|min:1',
            'reason'          => 'required|string|min:5|max:1000',
            'opname_id'       => 'nullable|exists:ktp_blank_opnames,id',
            'attachment'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'reason.required' => 'Alasan penyesuaian stok wajib diisi secara rinci.',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('blank_adjustments', 'public');
        }

        $adj = $this->stockService->createAdjustment($data, $request->user());

        return redirect()->back()->with('success', "Usulan penyesuaian stok [{$adj->adjustment_number}] berhasil diajukan dan menunggu persetujuan Pimpinan.");
    }

    public function approve(Request $request, KtpBlankAdjustment $adjustment): RedirectResponse
    {
        $this->authorize('blank_stocks.adjustment.approve');

        $this->stockService->approveAdjustment($adjustment, $request->user());

        return redirect()->back()->with('success', "Penyesuaian stok [{$adjustment->adjustment_number}] disetujui. Saldo persediaan resmi telah dimutasi.");
    }
}
