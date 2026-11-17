<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankUsageReport;
use App\Models\User;
use App\Services\BlankStock\BlankStockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlankStockUsageController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $canViewAll = $user->can('blank_stocks.usage.view_all') || $user->hasRole(['Super Administrator', 'Administrator Utama', 'Supervisor Layanan KTP-el', 'Bendahara Barang']);

        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $operatorId = $request->query('operator_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = KtpBlankUsageReport::with(['operator:id,name,username', 'approver:id,name'])->latest('usage_date');

        // RBAC: Operators can strictly only see their own reports
        if (!$canViewAll) {
            $query->where('operator_id', $user->id);
        } elseif ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                  ->orWhere('damage_reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('operator', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('usage_date', [$startDate, $endDate]);
        }

        $reports = $query->paginate(15)->withQueryString()->through(function ($r) {
            return [
                'id'                 => $r->id,
                'report_number'      => $r->report_number,
                'usage_date'         => Carbon::parse($r->usage_date)->format('d M Y'),
                'raw_date'           => $r->usage_date,
                'operator_id'        => $r->operator_id,
                'operator_name'      => $r->operator ? $r->operator->name : 'Operator #' . $r->operator_id,
                'session_name'       => $r->session_name,
                'quantity_used'      => $r->quantity_used,
                'quantity_damaged'   => $r->quantity_damaged,
                'total_quantity'     => $r->total_quantity,
                'damage_reason'      => $r->damage_reason,
                'damage_proof_url'   => $r->damage_proof_path ? asset('storage/' . $r->damage_proof_path) : null,
                'notes'              => $r->notes,
                'status'             => $r->status,
                'status_label'       => $r->status_label,
                'status_color'       => $r->status_color,
                'approver_name'      => $r->approver ? $r->approver->name : null,
                'approved_at'        => $r->approved_at ? $r->approved_at->format('d M Y, H:i') : null,
                'reversal_reason'    => $r->reversal_reason,
                'balance_before'     => $r->balance_before,
                'balance_after'      => $r->balance_after,
                'created_at'         => $r->created_at->format('d M Y, H:i'),
            ];
        });

        $stock = $this->stockService->getAuthoritativeStock();
        $operators = $canViewAll ? User::role('Operator Cetak KTP-el')->select('id', 'name')->get() : [];

        return Inertia::render('Admin/BlankStock/Usage/Index', [
            'reports'      => $reports,
            'filters'      => [
                'search'      => $search,
                'status'      => $status,
                'operator_id' => $operatorId,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
            ],
            'canViewAll'   => $canViewAll,
            'canApprove'   => $user->can('blank_stocks.usage.approve') || $user->hasRole(['Super Administrator', 'Administrator Utama', 'Supervisor Layanan KTP-el']),
            'currentStock' => $stock->current_stock,
            'operators'    => $operators,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('blank_stocks.usage.create');

        $data = $request->validate([
            'usage_date'         => 'required|date',
            'session_name'       => 'nullable|string|max:50',
            'quantity_used'      => 'required|integer|min:0',
            'quantity_damaged'   => 'required|integer|min:0',
            'damage_reason'      => 'required_if:quantity_damaged,1,2,3,4,5,6,7,8,9|nullable|string|max:500',
            'damage_proof'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes'              => 'nullable|string|max:1000',
            'is_draft'           => 'nullable|boolean',
        ], [
            'damage_reason.required_if' => 'Keterangan alasan kerusakan wajib diisi jika terdapat blangko rusak.',
        ]);

        if ($request->hasFile('damage_proof')) {
            $path = $request->file('damage_proof')->store('blank_damage_proofs', 'public');
            $data['damage_proof_path'] = $path;
        }

        $data['status'] = (!empty($data['is_draft']))
            ? KtpBlankUsageReport::STATUS_DRAFT
            : KtpBlankUsageReport::STATUS_SUBMITTED;

        // Operator identity is strictly taken from authenticated user
        $report = $this->stockService->recordUsageReport($data, $request->user());

        $msg = $report->status === KtpBlankUsageReport::STATUS_DRAFT
            ? "Draf laporan penggunaan berhasil disimpan (stok persediaan belum dikurangi)."
            : "Laporan penggunaan {$report->quantity_used} keping dan kerusakan {$report->quantity_damaged} keping berhasil dicatat dan memotong persediaan resmi.";

        return redirect()->back()->with('success', $msg);
    }

    public function submitDraft(Request $request, KtpBlankUsageReport $report): RedirectResponse
    {
        $this->authorize('blank_stocks.usage.create');

        if ($report->operator_id !== $request->user()->id && !$request->user()->hasRole(['Super Administrator', 'Administrator Utama'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak otorisasi untuk mengajukan draf laporan milik operator lain.');
        }

        $this->stockService->submitDraftReport($report, $request->user());

        return redirect()->back()->with('success', "Draf laporan [{$report->report_number}] berhasil diajukan dan persediaan blangko telah diperbarui.");
    }

    public function approve(Request $request, KtpBlankUsageReport $report): RedirectResponse
    {
        $this->authorize('blank_stocks.usage.approve');

        $this->stockService->approveUsageReport($report, $request->user());

        return redirect()->back()->with('success', "Laporan penggunaan [{$report->report_number}] berhasil disahkan.");
    }

    public function reverse(Request $request, KtpBlankUsageReport $report): RedirectResponse
    {
        $this->authorize('blank_stocks.usage.approve');

        $reason = $request->input('reason') ?? $request->input('reversal_reason');
        if (empty($reason) || strlen($reason) < 5) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'reason' => 'Alasan pembatalan/koreksi wajib diisi minimal 5 karakter.',
            ]);
        }

        $this->stockService->reverseUsageReport($report, $request->user(), $reason);

        return redirect()->back()->with('success', "Laporan [{$report->report_number}] berhasil dibatalkan dan saldo blangko telah dikembalikan.");
    }
}
