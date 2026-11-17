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
                fputcsv($handle, ['No', 'Tanggal', 'Jumlah Digunakan', 'Jumlah Rusak', 'Total Pengurangan', 'Jumlah Operator']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [$no++, $r['date'], $r['used'], $r['damaged'], $r['total'], $r['operators']]);
                }
                fputcsv($handle, ['TOTAL', '', $data['total_used'], $data['total_damaged'], $data['grand_total'], '']);
            } elseif ($reportType === 'receipts') {
                fputcsv($handle, ['No', 'No. Tanda Terima', 'Tanggal Terima', 'Jumlah (Keping)', 'Sumber', 'No. Dokumen/BAST', 'Penerima', 'Saldo Akhir']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [$no++, $r['receipt_number'], $r['received_date'], $r['quantity'], $r['source'], $r['document_number'], $r['receiver_name'], $r['balance_after']]);
                }
                fputcsv($handle, ['TOTAL', '', '', $data['total_quantity'], '', '', '', '']);
            } elseif ($reportType === 'operator_usage') {
                fputcsv($handle, ['No', 'Nama Operator', 'Jumlah Laporan', 'Blangko Digunakan', 'Blangko Rusak', 'Total Blangko']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [$no++, $r['operator_name'], $r['report_count'], $r['used'], $r['damaged'], $r['total']]);
                }
                fputcsv($handle, ['TOTAL', '', $data['total_reports'], $data['total_used'], $data['total_damaged'], $data['grand_total']]);
            } elseif ($reportType === 'damage') {
                fputcsv($handle, ['No', 'No. Laporan', 'Tanggal', 'Operator', 'Jumlah Rusak', 'Alasan Kerusakan', 'Status']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [$no++, $r['report_number'], $r['date'], $r['operator_name'], $r['damaged'], $r['reason'], $r['status']]);
                }
                fputcsv($handle, ['TOTAL', '', '', '', $data['total_damaged'], '', '']);
            } elseif ($reportType === 'ledger') {
                fputcsv($handle, ['No', 'No. Transaksi', 'Tanggal', 'Jenis Mutasi', 'Masuk (+)', 'Keluar Digunakan (-)', 'Keluar Rusak (-)', 'Saldo Akhir', 'Petugas', 'Keterangan']);
                $no = 1;
                foreach ($data['rows'] as $r) {
                    fputcsv($handle, [$no++, $r['transaction_number'], $r['date'], $r['type_label'], $r['qty_in'], $r['qty_out_used'], $r['qty_out_damaged'], $r['balance_after'], $r['actor_name'], $r['notes']]);
                }
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
                $query = KtpBlankUsageReport::whereBetween('usage_date', [$startDate, $endDate])
                    ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED]);

                if ($operatorId) {
                    $query->where('operator_id', $operatorId);
                }

                $records = $query->selectRaw('usage_date, SUM(quantity_used) as used, SUM(quantity_damaged) as damaged, COUNT(DISTINCT operator_id) as operators')
                    ->groupBy('usage_date')
                    ->orderBy('usage_date')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'date'      => Carbon::parse($r->usage_date)->format('d/m/Y'),
                        'used'      => (int) $r->used,
                        'damaged'   => (int) $r->damaged,
                        'total'     => (int) ($r->used + $r->damaged),
                        'operators' => (int) $r->operators,
                    ];
                });

                return [
                    'rows'          => $rows,
                    'total_used'    => $rows->sum('used'),
                    'total_damaged' => $rows->sum('damaged'),
                    'grand_total'   => $rows->sum('total'),
                ];

            case 'receipts':
                $records = KtpBlankReceipt::with('receiver:id,name')
                    ->whereBetween('received_date', [$startDate, $endDate])
                    ->where('status', KtpBlankReceipt::STATUS_VERIFIED)
                    ->orderBy('received_date')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'receipt_number'  => $r->receipt_number,
                        'received_date'   => Carbon::parse($r->received_date)->format('d/m/Y'),
                        'quantity'        => $r->quantity,
                        'source'          => $r->source,
                        'document_number' => $r->document_number,
                        'receiver_name'   => $r->receiver ? $r->receiver->name : 'Sistem',
                        'balance_after'   => $r->balance_after,
                    ];
                });

                return [
                    'rows'           => $rows,
                    'total_quantity' => $rows->sum('quantity'),
                ];

            case 'operator_usage':
                $records = KtpBlankUsageReport::with('operator:id,name')
                    ->whereBetween('usage_date', [$startDate, $endDate])
                    ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED])
                    ->selectRaw('operator_id, COUNT(id) as report_count, SUM(quantity_used) as used, SUM(quantity_damaged) as damaged')
                    ->groupBy('operator_id')
                    ->orderByDesc('used')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'operator_name' => $r->operator ? $r->operator->name : 'Operator #' . $r->operator_id,
                        'report_count'  => (int) $r->report_count,
                        'used'          => (int) $r->used,
                        'damaged'       => (int) $r->damaged,
                        'total'         => (int) ($r->used + $r->damaged),
                    ];
                });

                return [
                    'rows'          => $rows,
                    'total_reports' => $rows->sum('report_count'),
                    'total_used'    => $rows->sum('used'),
                    'total_damaged' => $rows->sum('damaged'),
                    'grand_total'   => $rows->sum('total'),
                ];

            case 'damage':
                $records = KtpBlankUsageReport::with('operator:id,name')
                    ->whereBetween('usage_date', [$startDate, $endDate])
                    ->where('quantity_damaged', '>', 0)
                    ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED])
                    ->orderByDesc('usage_date')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'report_number' => $r->report_number,
                        'date'          => Carbon::parse($r->usage_date)->format('d/m/Y'),
                        'operator_name' => $r->operator ? $r->operator->name : 'Operator #' . $r->operator_id,
                        'damaged'       => $r->quantity_damaged,
                        'reason'        => $r->damage_reason,
                        'status'        => $r->status_label,
                    ];
                });

                return [
                    'rows'          => $rows,
                    'total_damaged' => $rows->sum('damaged'),
                ];

            case 'ledger':
            default:
                $records = KtpBlankTransaction::with(['user:id,name', 'operator:id,name'])
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->orderBy('id')
                    ->get();

                $rows = $records->map(function ($r) {
                    return [
                        'transaction_number' => $r->transaction_number,
                        'date'               => Carbon::parse($r->transaction_date)->format('d/m/Y'),
                        'type_label'         => $r->type_label,
                        'qty_in'             => $r->qty_in,
                        'qty_out_used'       => $r->qty_out_used,
                        'qty_out_damaged'    => $r->qty_out_damaged,
                        'balance_after'      => $r->balance_after,
                        'actor_name'         => $r->user ? $r->user->name : 'Sistem',
                        'notes'              => $r->notes,
                    ];
                });

                return [
                    'rows' => $rows,
                ];
        }
    }
}
