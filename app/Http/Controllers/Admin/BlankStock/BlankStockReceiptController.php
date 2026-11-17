<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankReceipt;
use App\Services\BlankStock\BlankStockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlankStockReceiptController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('blank_stocks.receipts.view');

        $search = $request->query('search', '');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = KtpBlankReceipt::with('receiver:id,name,username')->latest('received_date');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('received_date', [$startDate, $endDate]);
        }

        $receipts = $query->paginate(15)->withQueryString()->through(function ($r) {
            return [
                'id'              => $r->id,
                'receipt_number'  => $r->receipt_number,
                'received_date'   => Carbon::parse($r->received_date)->format('d M Y'),
                'raw_date'        => $r->received_date,
                'quantity'        => $r->quantity,
                'source'          => $r->source,
                'document_number' => $r->document_number,
                'document_date'   => Carbon::parse($r->document_date)->format('d M Y'),
                'receiver_name'   => $r->receiver ? $r->receiver->name : 'Sistem',
                'notes'           => $r->notes,
                'attachment_url'  => $r->attachment_path ? asset('storage/' . $r->attachment_path) : null,
                'status'          => $r->status,
                'balance_before'  => $r->balance_before,
                'balance_after'   => $r->balance_after,
            ];
        });

        $stock = $this->stockService->getAuthoritativeStock();

        return Inertia::render('Admin/BlankStock/Receipts/Index', [
            'receipts'     => $receipts,
            'filters'      => [
                'search'     => $search,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
            'currentStock' => $stock->current_stock,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('blank_stocks.receipts.create');

        $data = $request->validate([
            'quantity'        => 'required|integer|min:1',
            'source'          => 'required|string|max:200',
            'document_number' => 'required|string|max:100',
            'document_date'   => 'required|date',
            'received_date'   => 'required|date',
            'notes'           => 'nullable|string|max:1000',
            'attachment'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('blank_receipts', 'public');
            $data['attachment_path'] = $path;
        }

        $this->stockService->recordReceipt($data, $request->user());

        return redirect()->back()->with('success', "Penerimaan sejumlah {$data['quantity']} blangko KTP-el berhasil disimpan dan ditambahkan ke saldo resmi.");
    }
}
