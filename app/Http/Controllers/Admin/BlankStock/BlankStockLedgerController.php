<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankTransaction;
use App\Models\User;
use App\Services\BlankStock\BlankStockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlankStockLedgerController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('blank_stocks.ledger.view');

        $search = $request->query('search', '');
        $type = $request->query('type', '');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $operatorId = $request->query('operator_id');

        $query = KtpBlankTransaction::with(['user:id,name,username', 'operator:id,name,username'])->latest('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($type) {
            $query->where('transaction_type', $type);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        if ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        $transactions = $query->paginate(25)->withQueryString()->through(function ($t) {
            return [
                'id'                 => $t->id,
                'transaction_number' => $t->transaction_number,
                'transaction_date'   => Carbon::parse($t->transaction_date)->format('d M Y'),
                'transaction_type'   => $t->transaction_type,
                'type_label'         => $t->type_label,
                'type_color'         => $t->type_color,
                'qty_in'             => $t->qty_in,
                'qty_out_used'       => $t->qty_out_used,
                'qty_out_damaged'    => $t->qty_out_damaged,
                'total_out'          => $t->qty_out_used + $t->qty_out_damaged,
                'balance_before'     => $t->balance_before,
                'balance_after'      => $t->balance_after,
                'user_name'          => $t->user ? $t->user->name : 'Sistem',
                'operator_name'      => $t->operator ? $t->operator->name : null,
                'notes'              => $t->notes,
                'created_at'         => $t->created_at->format('d/m/Y H:i'),
            ];
        });

        $stock = $this->stockService->getAuthoritativeStock();
        $operators = User::role('Operator Cetak KTP-el')->select('id', 'name')->get();

        return Inertia::render('Admin/BlankStock/Ledger/Index', [
            'transactions' => $transactions,
            'filters'      => [
                'search'      => $search,
                'type'        => $type,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'operator_id' => $operatorId,
            ],
            'currentStock' => $stock->current_stock,
            'operators'    => $operators,
        ]);
    }
}
