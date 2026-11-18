<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankOpname;
use App\Models\BlankStock\KtpBlankReceipt;
use App\Models\BlankStock\KtpBlankTransaction;
use App\Models\BlankStock\KtpBlankUsageReport;
use App\Models\User;
use App\Services\BlankStock\BlankStockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BlankStockReportController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('blank_stocks.reports.export');

        $reportType = $request->query('type', 'daily_usage'); // daily_usage, monthly_usage, receipts, operator_usage, damage, ledger, opname
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $operatorId = $request->query('operator_id');

        $data = $this->buildReportData($reportType, $startDate, $endDate, $operatorId);
        $operators = User::role('Operator Cetak KTP-el')->select('id', 'name')->get();
        $stock = $this->stockService->getAuthoritativeStock();

        return Inertia::render('Admin/BlankStock/Reports/Index', [
            'reportType'   => $reportType,
            'filters'      => [
                'type'        => $reportType,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'operator_id' => $operatorId,
            ],
            'data'         => $data,
            'operators'    => $operators,
            'currentStock' => $stock->current_stock,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('blank_stocks.reports.export');

        $reportType = $request->query('type', 'daily_usage');
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $operatorId = $request->query('operator_id');

        $data = $this->buildReportData($reportType, $startDate, $endDate, $operatorId);

        $filename = "Laporan_{$reportType}_{$startDate}_sd_{$endDate}.csv";

        return response()->stream(function () use ($reportType, $data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Title headers
            fputcsv($handle, ['DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL KABUPATEN DOMPU']);
            fputcsv($handle, ['MODUL MANAJEMEN PERSEDIAAN BLANGKO KTP-EL']);
            fputcsv($handle, ['Tipe Laporan: ' . strtoupper(str_replace('_', ' ', $reportType))]);
            fputcsv($handle, ['Tanggal Cetak: ' . now()->format('d/m/Y H:i') . ' WITA']);
            fputcsv($handle, []);

            if ($reportType === 'daily_usage') {
                fputcsv($handle, ['No', 'No. Laporan', 'Tanggal', 'Operator', 'Sesi', 'Digunakan (Keping)', 'Rusak (Keping)', 'Total Keping', 'Status']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [
                        $no++,
                        $r['report_number'],
                        $r['usage_date'],
                        $r['operator_name'],
                        $r['session_name'],
                        $r['quantity_used'],
                        $r['quantity_damaged'],
                        $r['total_quantity'],
                        $r['status_label'],
                    ]);
                }
                fputcsv($handle, [
                    'TOTAL',
                    '',
                    '',
                    '',
                    '',
                    $data['summary']['total_used'] ?? 0,
                    $data['summary']['total_damaged'] ?? 0,
                    $data['summary']['grand_total'] ?? 0,
                    '',
                ]);
            } elseif ($reportType === 'receipts') {
                fputcsv($handle, ['No', 'No. Penerimaan', 'Tanggal Terima', 'Jumlah (Keping)', 'Sumber', 'No. Dokumen/BAST', 'Penerima', 'Saldo Akhir']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [
                        $no++,
                        $r['receipt_number'],
                        $r['received_date'],
                        $r['quantity'],
                        $r['source'],
                        $r['document_number'],
                        $r['receiver_name'],
                        $r['balance_after'],
                    ]);
                }
                fputcsv($handle, ['TOTAL', '', '', $data['summary']['total_quantity'] ?? 0, '', '', '', '']);
            } elseif ($reportType === 'operator_usage') {
                fputcsv($handle, ['No', 'Nama Operator', 'Jumlah Laporan', 'Blangko Digunakan', 'Blangko Rusak', 'Total Blangko', 'Rasio Kerusakan']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [
                        $no++,
                        $r['operator_name'],
                        $r['report_count'],
                        $r['total_used'],
                        $r['total_damaged'],
                        $r['total_out'],
                        $r['damage_ratio'] . '%',
                    ]);
                }
                fputcsv($handle, [
                    'TOTAL',
                    '',
                    $data['summary']['total_reports'] ?? 0,
                    $data['summary']['total_used'] ?? 0,
                    $data['summary']['total_damaged'] ?? 0,
                    $data['summary']['grand_total'] ?? 0,
                    ($data['summary']['overall_ratio'] ?? 0) . '%',
                ]);
            } elseif ($reportType === 'damage') {
                fputcsv($handle, ['No', 'No. Laporan', 'Tanggal', 'Operator', 'Jumlah Rusak', 'Alasan Kerusakan', 'Status']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [
                        $no++,
                        $r['report_number'],
                        $r['usage_date'],
                        $r['operator_name'],
                        $r['quantity_damaged'],
                        $r['damage_reason'],
                        $r['status_label'],
                    ]);
                }
                fputcsv($handle, ['TOTAL', '', '', '', $data['summary']['total_damaged'] ?? 0, '', '']);
            } elseif ($reportType === 'opname') {
                fputcsv($handle, ['No', 'No. Berita Acara', 'Tanggal', 'Stok Sistem', 'Fisik Terhitung', 'Selisih', 'Pemeriksa', 'Status']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [
                        $no++,
                        $r['opname_number'],
                        $r['opname_date'],
                        $r['system_stock'],
                        $r['physical_stock'],
                        $r['difference'],
                        $r['checker_name'],
                        $r['status_label'],
                    ]);
                }
            } elseif ($reportType === 'ledger') {
                fputcsv($handle, ['No', 'No. Transaksi', 'Tanggal', 'Jenis Mutasi', 'Masuk (+)', 'Keluar Digunakan (-)', 'Keluar Rusak (-)', 'Total Keluar (-)', 'Saldo Akhir', 'Petugas', 'Keterangan']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [
                        $no++,
                        $r['transaction_number'],
                        $r['transaction_date'],
                        $r['type_label'],
                        $r['qty_in'],
                        $r['qty_out_used'],
                        $r['qty_out_damaged'],
                        $r['total_out'],
                        $r['balance_after'],
                        $r['actor_name'],
                        $r['notes'],
                    ]);
                }
                fputcsv($handle, [
                    'TOTAL',
                    '',
                    '',
                    '',
                    $data['summary']['total_in'] ?? 0,
                    $data['summary']['total_used'] ?? 0,
                    $data['summary']['total_damaged'] ?? 0,
                    $data['summary']['total_out'] ?? 0,
                    '',
                    '',
                    '',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function buildReportData(string $type, string $startDate, string $endDate, ?int $operatorId = null): array
    {
        switch ($type) {
            case 'daily_usage':
                $query = KtpBlankUsageReport::with('operator:id,name')
                    ->whereBetween('usage_date', [$startDate, $endDate])
                    ->whereIn('status', [
                        KtpBlankUsageReport::STATUS_SUBMITTED,
                        KtpBlankUsageReport::STATUS_APPROVED,
                    ]);

                if ($operatorId) {
                    $query->where('operator_id', $operatorId);
                }

                $records = $query->orderBy('usage_date')
                    ->orderBy('id')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'id'               => $r->id,
                        'report_number'    => $r->report_number,
                        'usage_date'       => Carbon::parse($r->usage_date)->format('d/m/Y'),
                        'raw_date'         => $r->usage_date,
                        'operator_id'      => $r->operator_id,
                        'operator_name'    => $r->operator ? $r->operator->name : ('Operator #' . $r->operator_id),
                        'session_name'     => $r->session_name ?: 'Pelayanan Harian',
                        'quantity_used'    => (int) $r->quantity_used,
                        'quantity_damaged' => (int) $r->quantity_damaged,
                        'total_quantity'   => (int) $r->total_quantity,
                        'damage_reason'    => $r->damage_reason,
                        'damage_proof_url' => $r->damage_proof_path ? asset('storage/' . $r->damage_proof_path) : null,
                        'status'           => $r->status,
                        'status_label'     => $r->status_label,
                        'balance_before'   => $r->balance_before,
                        'balance_after'    => $r->balance_after,
                    ];
                });

                return [
                    'rows'    => $rows,
                    'summary' => [
                        'total_reports' => $rows->count(),
                        'total_used'    => $rows->sum('quantity_used'),
                        'total_damaged' => $rows->sum('quantity_damaged'),
                        'grand_total'   => $rows->sum('total_quantity'),
                    ],
                ];

            case 'receipts':
                $records = KtpBlankReceipt::with('receiver:id,name')
                    ->whereBetween('received_date', [$startDate, $endDate])
                    ->where('status', KtpBlankReceipt::STATUS_VERIFIED)
                    ->orderBy('received_date')
                    ->orderBy('id')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'id'              => $r->id,
                        'receipt_number'  => $r->receipt_number,
                        'received_date'   => Carbon::parse($r->received_date)->format('d/m/Y'),
                        'raw_date'        => $r->received_date,
                        'quantity'        => (int) $r->quantity,
                        'source'          => $r->source,
                        'document_number' => $r->document_number,
                        'receiver_name'   => $r->receiver ? $r->receiver->name : 'Sistem',
                        'balance_before'  => (int) $r->balance_before,
                        'balance_after'   => (int) $r->balance_after,
                        'notes'           => $r->notes,
                    ];
                });

                return [
                    'rows'    => $rows,
                    'summary' => [
                        'total_receipts' => $rows->count(),
                        'total_quantity' => $rows->sum('quantity'),
                    ],
                ];

            case 'operator_usage':
                $records = KtpBlankUsageReport::with('operator:id,name')
                    ->whereBetween('usage_date', [$startDate, $endDate])
                    ->whereIn('status', [
                        KtpBlankUsageReport::STATUS_SUBMITTED,
                        KtpBlankUsageReport::STATUS_APPROVED,
                    ])
                    ->selectRaw('operator_id, COUNT(id) as report_count, SUM(quantity_used) as used, SUM(quantity_damaged) as damaged')
                    ->groupBy('operator_id')
                    ->orderByDesc('used')
                    ->get();

                $rows = $records->map(function ($r) {
                    $used = (int) $r->used;
                    $damaged = (int) $r->damaged;
                    $total = $used + $damaged;
                    $damageRatio = $total > 0 ? round(($damaged / $total) * 100, 1) : 0;
                    return [
                        'operator_id'   => $r->operator_id,
                        'operator_name' => $r->operator ? $r->operator->name : ('Operator #' . $r->operator_id),
                        'report_count'  => (int) $r->report_count,
                        'total_used'    => $used,
                        'total_damaged' => $damaged,
                        'total_out'     => $total,
                        'damage_ratio'  => $damageRatio,
                    ];
                });

                $grandUsed = $rows->sum('total_used');
                $grandDamaged = $rows->sum('total_damaged');
                $grandTotal = $rows->sum('total_out');
                $overallRatio = $grandTotal > 0 ? round(($grandDamaged / $grandTotal) * 100, 1) : 0;

                return [
                    'rows'    => $rows,
                    'summary' => [
                        'total_reports' => $rows->sum('report_count'),
                        'total_used'    => $grandUsed,
                        'total_damaged' => $grandDamaged,
                        'grand_total'   => $grandTotal,
                        'overall_ratio' => $overallRatio,
                    ],
                ];

            case 'damage':
                $records = KtpBlankUsageReport::with('operator:id,name')
                    ->whereBetween('usage_date', [$startDate, $endDate])
                    ->where('quantity_damaged', '>', 0)
                    ->whereIn('status', [
                        KtpBlankUsageReport::STATUS_SUBMITTED,
                        KtpBlankUsageReport::STATUS_APPROVED,
                    ])
                    ->orderByDesc('usage_date')
                    ->orderByDesc('id')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'id'               => $r->id,
                        'report_number'    => $r->report_number,
                        'usage_date'       => Carbon::parse($r->usage_date)->format('d/m/Y'),
                        'operator_name'    => $r->operator ? $r->operator->name : ('Operator #' . $r->operator_id),
                        'quantity_damaged' => (int) $r->quantity_damaged,
                        'damage_reason'    => $r->damage_reason ?: 'Tanpa keterangan',
                        'damage_proof_url' => $r->damage_proof_path ? asset('storage/' . $r->damage_proof_path) : null,
                        'status'           => $r->status,
                        'status_label'     => $r->status_label,
                    ];
                });

                return [
                    'rows'    => $rows,
                    'summary' => [
                        'total_reports' => $rows->count(),
                        'total_damaged' => $rows->sum('quantity_damaged'),
                    ],
                ];

            case 'opname':
                $records = KtpBlankOpname::with(['checker:id,name', 'approver:id,name'])
                    ->whereBetween('opname_date', [$startDate, $endDate])
                    ->orderByDesc('opname_date')
                    ->orderByDesc('id')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'id'             => $r->id,
                        'opname_number'  => $r->opname_number,
                        'opname_date'    => Carbon::parse($r->opname_date)->format('d/m/Y'),
                        'system_stock'   => (int) $r->system_stock,
                        'physical_stock' => (int) $r->physical_stock,
                        'difference'     => (int) $r->difference,
                        'checker_name'   => $r->checker ? $r->checker->name : 'Sistem',
                        'approver_name'  => $r->approver ? $r->approver->name : null,
                        'status'         => $r->status,
                        'status_label'   => $r->status_label,
                        'notes'          => $r->notes,
                    ];
                });

                return [
                    'rows'    => $rows,
                    'summary' => [
                        'total_opnames'    => $rows->count(),
                        'total_difference' => $rows->sum('difference'),
                    ],
                ];

            case 'ledger':
            default:
                $records = KtpBlankTransaction::with(['user:id,name', 'operator:id,name'])
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->orderBy('id')
                    ->get();

                $rows = $records->map(function ($r) {
                    $actor = $r->operator ? $r->operator->name : ($r->user ? $r->user->name : 'Sistem');
                    return [
                        'id'                 => $r->id,
                        'transaction_number' => $r->transaction_number,
                        'transaction_date'   => Carbon::parse($r->transaction_date)->format('d/m/Y'),
                        'transaction_type'   => $r->transaction_type,
                        'type_label'         => $r->type_label,
                        'qty_in'             => (int) $r->qty_in,
                        'qty_out_used'       => (int) $r->qty_out_used,
                        'qty_out_damaged'    => (int) $r->qty_out_damaged,
                        'total_out'          => (int) ($r->qty_out_used + $r->qty_out_damaged),
                        'balance_after'      => (int) $r->balance_after,
                        'actor_name'         => $actor,
                        'notes'              => $r->notes,
                    ];
                });

                return [
                    'rows'    => $rows,
                    'summary' => [
                        'total_in'      => $rows->sum('qty_in'),
                        'total_used'    => $rows->sum('qty_out_used'),
                        'total_damaged' => $rows->sum('qty_out_damaged'),
                        'total_out'     => $rows->sum('total_out'),
                    ],
                ];
        }
    }
}
