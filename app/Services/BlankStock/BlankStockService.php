<?php

namespace App\Services\BlankStock;

use App\Models\BlankStock\KtpBlankAdjustment;
use App\Models\BlankStock\KtpBlankOpname;
use App\Models\BlankStock\KtpBlankReceipt;
use App\Models\BlankStock\KtpBlankSetting;
use App\Models\BlankStock\KtpBlankStock;
use App\Models\BlankStock\KtpBlankTransaction;
use App\Models\BlankStock\KtpBlankUsageReport;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BlankStockService
{
    public function __construct()
    {
        self::ensureDatabaseSchema();
    }

    /**
     * Ensure database migrations and permission tables exist.
     */
    public static function ensureDatabaseSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('ktp_blank_stocks')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }

            if (!\Spatie\Permission\Models\Permission::where('name', 'blank_stocks.dashboard')->exists()) {
                (new \Database\Seeders\KtpBlankStockPermissionSeeder())->run();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('BlankStock auto-migrate/schema sync warning: ' . $e->getMessage());
        }
    }

    /**
     * Get the authoritative singleton stock record.
     */
    public function getAuthoritativeStock(): KtpBlankStock
    {
        self::ensureDatabaseSchema();
        return KtpBlankStock::getSingleton();
    }

    /**
     * Record receipt of KTP-el blanks by Bendahara Barang.
     * Thread-safe using pessimistic locking.
     *
     * @throws ValidationException
     */
    public function recordReceipt(array $data, User $actor): KtpBlankReceipt
    {
        $quantity = (int) ($data['quantity'] ?? 0);
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah penerimaan blangko harus lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use ($data, $quantity, $actor) {
            /** @var KtpBlankStock $stock */
            $stock = KtpBlankStock::where('id', 1)->lockForUpdate()->firstOrFail();

            $balanceBefore = $stock->current_stock;
            $balanceAfter  = $balanceBefore + $quantity;

            // Generate unique receipt code: RCV-YYYYMM-XXXX
            $period = Carbon::parse($data['received_date'] ?? now())->format('Ym');
            $countThisMonth = KtpBlankReceipt::where('receipt_number', 'like', "RCV-{$period}-%")->count();
            $seq = str_pad($countThisMonth + 1, 4, '0', STR_PAD_LEFT);
            $receiptNumber = "RCV-{$period}-{$seq}";
            while (KtpBlankReceipt::where('receipt_number', $receiptNumber)->exists()) {
                $seq = str_pad(++$countThisMonth + 1, 4, '0', STR_PAD_LEFT);
                $receiptNumber = "RCV-{$period}-{$seq}";
            }

            // Create Receipt Record
            $receipt = KtpBlankReceipt::create([
                'receipt_number'  => $receiptNumber,
                'received_date'   => $data['received_date'] ?? now()->toDateString(),
                'quantity'        => $quantity,
                'source'          => $data['source'],
                'document_number' => $data['document_number'],
                'document_date'   => $data['document_date'] ?? now()->toDateString(),
                'received_by'     => $actor->id,
                'notes'           => $data['notes'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
                'status'          => KtpBlankReceipt::STATUS_VERIFIED,
                'balance_before'  => $balanceBefore,
                'balance_after'   => $balanceAfter,
            ]);

            // Generate Transaction Number: TRX-YYYYMMDD-XXXX
            $dayCode = Carbon::parse($receipt->received_date)->format('Ymd');
            $trxCount = KtpBlankTransaction::where('transaction_number', 'like', "TRX-{$dayCode}-%")->count();
            $trxSeq = str_pad($trxCount + 1, 4, '0', STR_PAD_LEFT);
            $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
            while (KtpBlankTransaction::where('transaction_number', $trxNumber)->exists()) {
                $trxSeq = str_pad(++$trxCount + 1, 4, '0', STR_PAD_LEFT);
                $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
            }

            // Record into Immutable Inventory Ledger
            $trx = KtpBlankTransaction::create([
                'transaction_number' => $trxNumber,
                'transaction_date'   => $receipt->received_date,
                'transaction_type'   => KtpBlankTransaction::TYPE_RECEIPT,
                'reference_type'     => KtpBlankReceipt::class,
                'reference_id'       => $receipt->id,
                'qty_in'             => $quantity,
                'qty_out_used'       => 0,
                'qty_out_damaged'    => 0,
                'balance_before'     => $balanceBefore,
                'balance_after'      => $balanceAfter,
                'user_id'            => $actor->id,
                'operator_id'        => null,
                'notes'              => "Penerimaan dari {$receipt->source} (No. Dokumen: {$receipt->document_number})",
            ]);

            // Update Authoritative Stock
            $stock->update([
                'current_stock'       => $balanceAfter,
                'total_received'      => $stock->total_received + $quantity,
                'last_transaction_id' => $trx->id,
                'last_transaction_at' => now(),
                'updated_by'          => $actor->id,
            ]);

            // Invalidate Public Cache
            Cache::forget('public_blank_stock_summary');

            // Audit Trail Logging
            try {
                ActivityLogger::log(
                    "Mencatat penerimaan {$quantity} blangko KTP-el dari {$receipt->source} [{$receipt->receipt_number}]",
                    $receipt,
                    'blank_stock_receipt',
                    [
                        'receipt_number'  => $receipt->receipt_number,
                        'quantity'        => $quantity,
                        'source'          => $receipt->source,
                        'document_number' => $receipt->document_number,
                        'balance_before'  => $balanceBefore,
                        'balance_after'   => $balanceAfter,
                    ]
                );
            } catch (\Throwable $e) {}

            return $receipt;
        });
    }

    /**
     * Record daily usage & damage report by an Operator Cetak.
     * Strictly supports concurrent submissions by multiple operators with row-level locking.
     *
     * @throws ValidationException
     */
    public function recordUsageReport(array $data, User $operator): KtpBlankUsageReport
    {
        $used = (int) ($data['quantity_used'] ?? 0);
        $damaged = (int) ($data['quantity_damaged'] ?? 0);
        $total = $used + $damaged;

        if ($used < 0 || $damaged < 0) {
            throw ValidationException::withMessages([
                'quantity_used' => 'Jumlah penggunaan tidak boleh bernilai negatif.',
            ]);
        }

        if ($total <= 0) {
            throw ValidationException::withMessages([
                'quantity_used' => 'Total blangko yang dilaporkan (digunakan + rusak) harus lebih dari 0.',
            ]);
        }

        if ($damaged > 0 && empty(trim($data['damage_reason'] ?? ''))) {
            throw ValidationException::withMessages([
                'damage_reason' => 'Keterangan alasan kerusakan wajib diisi jika terdapat blangko rusak.',
            ]);
        }

        $isDraft = ($data['status'] ?? KtpBlankUsageReport::STATUS_SUBMITTED) === KtpBlankUsageReport::STATUS_DRAFT;

        return DB::transaction(function () use ($data, $used, $damaged, $total, $operator, $isDraft) {
            /** @var KtpBlankStock $stock */
            $stock = KtpBlankStock::where('id', 1)->lockForUpdate()->firstOrFail();

            // Validate that stock does not become negative upon submission
            if (!$isDraft && $stock->current_stock < $total) {
                throw ValidationException::withMessages([
                    'quantity_used' => "Saldo persediaan blangko tidak mencukupi. Stok tersedia saat ini: {$stock->current_stock} keping, total penggunaan diajukan: {$total} keping.",
                ]);
            }

            $usageDate = $data['usage_date'] ?? now()->toDateString();
            $dayCode = Carbon::parse($usageDate)->format('Ymd');

            // Unique Report Number: USE-YYYYMMDD-OP{user_id}-XXXX
            $countToday = KtpBlankUsageReport::where('usage_date', $usageDate)
                ->where('operator_id', $operator->id)
                ->count();
            $seq = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);
            $reportNumber = "USE-{$dayCode}-OP{$operator->id}-{$seq}";
            while (KtpBlankUsageReport::where('report_number', $reportNumber)->exists()) {
                $seq = str_pad(++$countToday + 1, 3, '0', STR_PAD_LEFT);
                $reportNumber = "USE-{$dayCode}-OP{$operator->id}-{$seq}";
            }

            $balanceBefore = $stock->current_stock;
            $balanceAfter  = $isDraft ? null : ($balanceBefore - $total);

            // Create Usage Report
            $report = KtpBlankUsageReport::create([
                'report_number'      => $reportNumber,
                'usage_date'         => $usageDate,
                'operator_id'        => $operator->id,
                'session_name'       => $data['session_name'] ?? 'Harian Penuh',
                'quantity_used'      => $used,
                'quantity_damaged'   => $damaged,
                'total_quantity'     => $total,
                'damage_reason'      => $damaged > 0 ? ($data['damage_reason'] ?? null) : null,
                'damage_proof_path'  => $data['damage_proof_path'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'status'             => $isDraft ? KtpBlankUsageReport::STATUS_DRAFT : KtpBlankUsageReport::STATUS_SUBMITTED,
                'balance_before'     => $balanceBefore,
                'balance_after'      => $balanceAfter,
            ]);

            // If not a draft, mutate authoritative stock & insert ledger transaction immediately
            if (!$isDraft) {
                // Generate Transaction Number: TRX-YYYYMMDD-XXXX
                $trxCount = KtpBlankTransaction::where('transaction_number', 'like', "TRX-{$dayCode}-%")->count();
                $trxSeq = str_pad($trxCount + 1, 4, '0', STR_PAD_LEFT);
                $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
                while (KtpBlankTransaction::where('transaction_number', $trxNumber)->exists()) {
                    $trxSeq = str_pad(++$trxCount + 1, 4, '0', STR_PAD_LEFT);
                    $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
                }

                $trx = KtpBlankTransaction::create([
                    'transaction_number' => $trxNumber,
                    'transaction_date'   => $usageDate,
                    'transaction_type'   => KtpBlankTransaction::TYPE_USAGE,
                    'reference_type'     => KtpBlankUsageReport::class,
                    'reference_id'       => $report->id,
                    'qty_in'             => 0,
                    'qty_out_used'       => $used,
                    'qty_out_damaged'    => $damaged,
                    'balance_before'     => $balanceBefore,
                    'balance_after'      => $balanceAfter,
                    'user_id'            => $operator->id,
                    'operator_id'        => $operator->id,
                    'notes'              => "Laporan penggunaan Operator {$operator->name}: {$used} digunakan, {$damaged} rusak [{$report->report_number}]",
                ]);

                // Update Authoritative Stock
                $stock->update([
                    'current_stock'       => $balanceAfter,
                    'total_used'          => $stock->total_used + $used,
                    'total_damaged'       => $stock->total_damaged + $damaged,
                    'last_transaction_id' => $trx->id,
                    'last_transaction_at' => now(),
                    'updated_by'          => $operator->id,
                ]);

                Cache::forget('public_blank_stock_summary');
                Cache::forget('public_blank_usage_reports');
            }

            // Audit Trail Logging
            try {
                ActivityLogger::log(
                    "Operator {$operator->name} melaporkan " . ($isDraft ? "draf " : "") . "penggunaan {$used} keping dan kerusakan {$damaged} keping blangko KTP-el [{$report->report_number}]",
                    $report,
                    'blank_stock_usage',
                    [
                        'report_number'    => $report->report_number,
                        'quantity_used'    => $used,
                        'quantity_damaged' => $damaged,
                        'total_quantity'   => $total,
                        'balance_before'   => $balanceBefore,
                        'balance_after'    => $balanceAfter,
                        'is_draft'         => $isDraft,
                    ]
                );
            } catch (\Throwable $e) {}

            return $report;
        });
    }

    /**
     * Submit a previously saved draft usage report.
     *
     * @throws ValidationException
     */
    public function submitDraftReport(KtpBlankUsageReport $report, User $operator): KtpBlankUsageReport
    {
        if ($report->operator_id !== $operator->id && !$operator->hasRole(['Super Administrator', 'Administrator Utama'])) {
            throw ValidationException::withMessages([
                'report' => 'Anda tidak memiliki hak akses untuk mengajukan draf laporan milik operator lain.',
            ]);
        }

        if ($report->status !== KtpBlankUsageReport::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'report' => 'Hanya laporan berstatus Draf yang dapat diajukan.',
            ]);
        }

        return DB::transaction(function () use ($report, $operator) {
            /** @var KtpBlankStock $stock */
            $stock = KtpBlankStock::where('id', 1)->lockForUpdate()->firstOrFail();

            if ($stock->current_stock < $report->total_quantity) {
                throw ValidationException::withMessages([
                    'report' => "Saldo persediaan blangko tidak mencukupi saat ini. Stok tersedia: {$stock->current_stock} keping, kebutuhan draf: {$report->total_quantity} keping.",
                ]);
            }

            $balanceBefore = $stock->current_stock;
            $balanceAfter  = $balanceBefore - $report->total_quantity;

            $dayCode = Carbon::parse($report->usage_date)->format('Ymd');
            $trxCount = KtpBlankTransaction::where('transaction_number', 'like', "TRX-{$dayCode}-%")->count();
            $trxSeq = str_pad($trxCount + 1, 4, '0', STR_PAD_LEFT);
            $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
            while (KtpBlankTransaction::where('transaction_number', $trxNumber)->exists()) {
                $trxSeq = str_pad(++$trxCount + 1, 4, '0', STR_PAD_LEFT);
                $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
            }

            $trx = KtpBlankTransaction::create([
                'transaction_number' => $trxNumber,
                'transaction_date'   => $report->usage_date,
                'transaction_type'   => KtpBlankTransaction::TYPE_USAGE,
                'reference_type'     => KtpBlankUsageReport::class,
                'reference_id'       => $report->id,
                'qty_in'             => 0,
                'qty_out_used'       => $report->quantity_used,
                'qty_out_damaged'    => $report->quantity_damaged,
                'balance_before'     => $balanceBefore,
                'balance_after'      => $balanceAfter,
                'user_id'            => $operator->id,
                'operator_id'        => $report->operator_id,
                'notes'              => "Pengajuan draf laporan penggunaan [{$report->report_number}]",
            ]);

            $report->update([
                'status'         => KtpBlankUsageReport::STATUS_SUBMITTED,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
            ]);

            $stock->update([
                'current_stock'       => $balanceAfter,
                'total_used'          => $stock->total_used + $report->quantity_used,
                'total_damaged'       => $stock->total_damaged + $report->quantity_damaged,
                'last_transaction_id' => $trx->id,
                'last_transaction_at' => now(),
                'updated_by'          => $operator->id,
            ]);

            Cache::forget('public_blank_stock_summary');
            Cache::forget('public_blank_usage_reports');

            try {
                ActivityLogger::log(
                    "Operator {$operator->name} mengajukan draf laporan penggunaan blangko [{$report->report_number}]",
                    $report,
                    'blank_stock_usage_submit',
                    [
                        'report_number' => $report->report_number,
                        'total'         => $report->total_quantity,
                        'balance_after' => $balanceAfter,
                    ]
                );
            } catch (\Throwable $e) {}

            return $report;
        });
    }

    /**
     * Update an existing usage report (Draft or Submitted).
     *
     * @throws ValidationException
     */
    public function updateUsageReport(KtpBlankUsageReport $report, array $data, User $actor): KtpBlankUsageReport
    {
        $isOwner = ($report->operator_id === $actor->id);
        $isAdmin = ($actor->isSuperAdmin() || $actor->hasRole(['Super Administrator', 'Administrator Utama', 'Supervisor Layanan KTP-el', 'Supervisor Persediaan Blangko']));

        if (!$isOwner && !$isAdmin) {
            throw ValidationException::withMessages([
                'general' => 'Anda tidak memiliki hak akses untuk mengubah laporan penggunaan operator lain.',
            ]);
        }

        if ($report->status === KtpBlankUsageReport::STATUS_REVERSED) {
            throw ValidationException::withMessages([
                'general' => 'Laporan yang telah dibatalkan (reversal) tidak dapat diubah kembali.',
            ]);
        }

        $used = isset($data['quantity_used']) ? (int) $data['quantity_used'] : $report->quantity_used;
        $damaged = isset($data['quantity_damaged']) ? (int) $data['quantity_damaged'] : $report->quantity_damaged;
        $total = $used + $damaged;

        if ($used < 0 || $damaged < 0) {
            throw ValidationException::withMessages([
                'quantity_used' => 'Jumlah penggunaan tidak boleh bernilai negatif.',
            ]);
        }

        if ($total <= 0) {
            throw ValidationException::withMessages([
                'quantity_used' => 'Total blangko yang dilaporkan (digunakan + rusak) harus lebih dari 0.',
            ]);
        }

        if ($damaged > 0 && empty(trim($data['damage_reason'] ?? $report->damage_reason ?? ''))) {
            throw ValidationException::withMessages([
                'damage_reason' => 'Keterangan alasan kerusakan wajib diisi jika terdapat blangko rusak.',
            ]);
        }

        return DB::transaction(function () use ($report, $data, $used, $damaged, $total, $actor) {
            /** @var KtpBlankStock $stock */
            $stock = KtpBlankStock::where('id', 1)->lockForUpdate()->firstOrFail();

            if ($report->status === KtpBlankUsageReport::STATUS_DRAFT) {
                $report->update([
                    'usage_date'        => $data['usage_date'] ?? $report->usage_date,
                    'session_name'      => $data['session_name'] ?? $report->session_name,
                    'quantity_used'     => $used,
                    'quantity_damaged'  => $damaged,
                    'total_quantity'    => $total,
                    'damage_reason'     => $damaged > 0 ? ($data['damage_reason'] ?? $report->damage_reason) : null,
                    'damage_proof_path' => $data['damage_proof_path'] ?? $report->damage_proof_path,
                    'notes'             => $data['notes'] ?? $report->notes,
                ]);

                if (!empty($data['submit_immediately'])) {
                    return $this->submitDraftReport($report, $actor);
                }
            } elseif ($report->status === KtpBlankUsageReport::STATUS_SUBMITTED) {
                $oldTotal = $report->total_quantity;
                $diff = $total - $oldTotal;

                if ($diff > 0 && $stock->current_stock < $diff) {
                    throw ValidationException::withMessages([
                        'quantity_used' => "Saldo persediaan blangko tidak mencukupi untuk penambahan mutasi. Tersedia saat ini: {$stock->current_stock} keping, dibutuhkan penambahan: {$diff} keping.",
                    ]);
                }

                if ($diff > 0) {
                    $stock->current_stock -= $diff;
                } elseif ($diff < 0) {
                    $stock->current_stock += abs($diff);
                }

                $usedDiff = $used - $report->quantity_used;
                $damagedDiff = $damaged - $report->quantity_damaged;
                $stock->total_used += $usedDiff;
                $stock->total_damaged += $damagedDiff;
                $stock->save();

                // Update ledger transaction
                $trx = KtpBlankTransaction::where('reference_type', KtpBlankUsageReport::class)
                    ->where('reference_id', $report->id)
                    ->first();
                if ($trx) {
                    $trx->qty_out_used = $used;
                    $trx->qty_out_damaged = $damaged;
                    $trx->balance_after = $stock->current_stock;
                    $trx->notes = "Koreksi Laporan Penggunaan: {$report->report_number}" . (!empty($data['notes']) ? " - {$data['notes']}" : "");
                    $trx->save();
                }

                $report->update([
                    'usage_date'        => $data['usage_date'] ?? $report->usage_date,
                    'session_name'      => $data['session_name'] ?? $report->session_name,
                    'quantity_used'     => $used,
                    'quantity_damaged'  => $damaged,
                    'total_quantity'    => $total,
                    'damage_reason'     => $damaged > 0 ? ($data['damage_reason'] ?? $report->damage_reason) : null,
                    'damage_proof_path' => $data['damage_proof_path'] ?? $report->damage_proof_path,
                    'notes'             => $data['notes'] ?? $report->notes,
                    'balance_after'     => $stock->current_stock,
                ]);

                Cache::forget('public_blank_stock_summary');
                Cache::forget('public_blank_usage_reports');
            } elseif ($report->status === KtpBlankUsageReport::STATUS_APPROVED) {
                // If already approved, update metadata only
                $report->update([
                    'session_name'      => $data['session_name'] ?? $report->session_name,
                    'damage_reason'     => $data['damage_reason'] ?? $report->damage_reason,
                    'damage_proof_path' => $data['damage_proof_path'] ?? $report->damage_proof_path,
                    'notes'             => $data['notes'] ?? $report->notes,
                ]);
            }

            try {
                ActivityLogger::log(
                    "User {$actor->name} memperbarui laporan penggunaan blangko [{$report->report_number}]",
                    $report,
                    'blank_stock_usage_update',
                    [
                        'report_number' => $report->report_number,
                        'total'         => $report->total_quantity,
                        'status'        => $report->status,
                    ]
                );
            } catch (\Throwable $e) {}

            return $report->fresh();
        });
    }

    /**
     * Approve a submitted usage report by Supervisor / Pimpinan.
     */
    public function approveUsageReport(KtpBlankUsageReport $report, User $supervisor): KtpBlankUsageReport
    {
        $report->update([
            'status'      => KtpBlankUsageReport::STATUS_APPROVED,
            'approved_by' => $supervisor->id,
            'approved_at' => now(),
        ]);

        try {
            ActivityLogger::log(
                "Supervisor {$supervisor->name} mengesahkan laporan penggunaan blangko [{$report->report_number}]",
                $report,
                'blank_stock_usage_approve'
            );
        } catch (\Throwable $e) {}

        return $report;
    }

    /**
     * Reverse an erroneously submitted usage report through official reversal transaction.
     * Restores stock safely without destroying audit history.
     *
     * @throws ValidationException
     */
    public function reverseUsageReport(KtpBlankUsageReport $report, User $actor, string $reason): KtpBlankUsageReport
    {
        if ($report->status === KtpBlankUsageReport::STATUS_REVERSED) {
            throw ValidationException::withMessages([
                'report' => 'Laporan ini sudah dibatalkan/direverse sebelumnya.',
            ]);
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan pembatalan/koreksi wajib diisi secara rinci.',
            ]);
        }

        return DB::transaction(function () use ($report, $actor, $reason) {
            /** @var KtpBlankStock $stock */
            $stock = KtpBlankStock::where('id', 1)->lockForUpdate()->firstOrFail();

            $balanceBefore = $stock->current_stock;
            // Only add back stock if report was actually submitted/approved (not draft)
            $quantityToRestore = in_array($report->status, [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED])
                ? $report->total_quantity
                : 0;

            $balanceAfter = $balanceBefore + $quantityToRestore;

            if ($quantityToRestore > 0) {
                $dayCode = now()->format('Ymd');
                $trxCount = KtpBlankTransaction::where('transaction_number', 'like', "TRX-{$dayCode}-%")->count();
                $trxSeq = str_pad($trxCount + 1, 4, '0', STR_PAD_LEFT);
                $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
                while (KtpBlankTransaction::where('transaction_number', $trxNumber)->exists()) {
                    $trxSeq = str_pad(++$trxCount + 1, 4, '0', STR_PAD_LEFT);
                    $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
                }

                $trx = KtpBlankTransaction::create([
                    'transaction_number' => $trxNumber,
                    'transaction_date'   => now()->toDateString(),
                    'transaction_type'   => KtpBlankTransaction::TYPE_REVERSAL,
                    'reference_type'     => KtpBlankUsageReport::class,
                    'reference_id'       => $report->id,
                    'qty_in'             => $quantityToRestore,
                    'qty_out_used'       => 0,
                    'qty_out_damaged'    => 0,
                    'balance_before'     => $balanceBefore,
                    'balance_after'      => $balanceAfter,
                    'user_id'            => $actor->id,
                    'operator_id'        => $report->operator_id,
                    'notes'              => "Pembatalan laporan [{$report->report_number}]. Alasan: {$reason}",
                ]);

                $stock->update([
                    'current_stock'       => $balanceAfter,
                    'total_used'          => max(0, $stock->total_used - $report->quantity_used),
                    'total_damaged'       => max(0, $stock->total_damaged - $report->quantity_damaged),
                    'last_transaction_id' => $trx->id,
                    'last_transaction_at' => now(),
                    'updated_by'          => $actor->id,
                ]);

                Cache::forget('public_blank_stock_summary');
                Cache::forget('public_blank_usage_reports');
            }

            $report->update([
                'status'          => KtpBlankUsageReport::STATUS_REVERSED,
                'reversal_reason' => $reason,
            ]);

            try {
                ActivityLogger::log(
                    "Membatalkan laporan penggunaan blangko [{$report->report_number}]. Alasan: {$reason}",
                    $report,
                    'blank_stock_usage_reverse',
                    [
                        'report_number'       => $report->report_number,
                        'quantity_restored'   => $quantityToRestore,
                        'reason'              => $reason,
                        'balance_after'       => $balanceAfter,
                    ]
                );
            } catch (\Throwable $e) {}

            return $report;
        });
    }

    /**
     * Record physical stock opname examination.
     * Does NOT mutate stock balance automatically.
     */
    public function recordOpname(array $data, User $actor): KtpBlankOpname
    {
        $stock = $this->getAuthoritativeStock();
        $physicalStock = (int) ($data['physical_stock'] ?? 0);
        $systemStock = $stock->current_stock;
        $difference = $physicalStock - $systemStock;

        $opnameDate = $data['opname_date'] ?? now()->toDateString();
        $period = Carbon::parse($opnameDate)->format('Ym');
        $count = KtpBlankOpname::where('opname_number', 'like', "SOP-{$period}-%")->count();
        $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        $opnameNumber = "SOP-{$period}-{$seq}";
        while (KtpBlankOpname::where('opname_number', $opnameNumber)->exists()) {
            $seq = str_pad(++$count + 1, 3, '0', STR_PAD_LEFT);
            $opnameNumber = "SOP-{$period}-{$seq}";
        }

        $opname = KtpBlankOpname::create([
            'opname_number'  => $opnameNumber,
            'opname_date'    => $opnameDate,
            'system_stock'   => $systemStock,
            'physical_stock' => $physicalStock,
            'difference'     => $difference,
            'checked_by'     => $actor->id,
            'status'         => KtpBlankOpname::STATUS_PENDING_APPROVAL,
            'notes'          => $data['notes'] ?? null,
            'evidence_path'  => $data['evidence_path'] ?? null,
        ]);

        try {
            ActivityLogger::log(
                "Mencatat pemeriksaan fisik stok opname blangko KTP-el [{$opname->opname_number}]: Fisik {$physicalStock}, Sistem {$systemStock}, Selisih {$difference}",
                $opname,
                'blank_stock_opname',
                [
                    'opname_number'  => $opname->opname_number,
                    'system_stock'   => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference'     => $difference,
                ]
            );
        } catch (\Throwable $e) {}

        return $opname;
    }

    /**
     * Approve stock opname report by Supervisor/Pimpinan.
     */
    public function approveOpname(KtpBlankOpname $opname, User $supervisor): KtpBlankOpname
    {
        $opname->update([
            'status'      => KtpBlankOpname::STATUS_APPROVED,
            'approved_by' => $supervisor->id,
            'approved_at' => now(),
        ]);

        try {
            ActivityLogger::log(
                "Pimpinan {$supervisor->name} mengesahkan Berita Acara Stok Opname [{$opname->opname_number}]",
                $opname,
                'blank_stock_opname_approve'
            );
        } catch (\Throwable $e) {}

        return $opname;
    }

    /**
     * Create official stock adjustment.
     *
     * @throws ValidationException
     */
    public function createAdjustment(array $data, User $actor): KtpBlankAdjustment
    {
        $quantity = (int) ($data['quantity'] ?? 0);
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah penyesuaian harus lebih dari 0.',
            ]);
        }

        $type = strtoupper($data['type'] ?? '');
        if (!in_array($type, [KtpBlankAdjustment::TYPE_ADDITION, KtpBlankAdjustment::TYPE_DEDUCTION])) {
            throw ValidationException::withMessages([
                'type' => 'Jenis penyesuaian tidak valid (Pilih Penambahan atau Pengurangan).',
            ]);
        }

        $adjustmentDate = $data['adjustment_date'] ?? now()->toDateString();
        $period = Carbon::parse($adjustmentDate)->format('Ym');
        $count = KtpBlankAdjustment::where('adjustment_number', 'like', "ADJ-{$period}-%")->count();
        $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        $adjNumber = "ADJ-{$period}-{$seq}";
        while (KtpBlankAdjustment::where('adjustment_number', $adjNumber)->exists()) {
            $seq = str_pad(++$count + 1, 3, '0', STR_PAD_LEFT);
            $adjNumber = "ADJ-{$period}-{$seq}";
        }

        $adj = KtpBlankAdjustment::create([
            'adjustment_number' => $adjNumber,
            'adjustment_date'   => $adjustmentDate,
            'type'              => $type,
            'quantity'          => $quantity,
            'reason'            => $data['reason'],
            'opname_id'         => $data['opname_id'] ?? null,
            'requested_by'      => $actor->id,
            'status'            => KtpBlankAdjustment::STATUS_PENDING_APPROVAL,
            'attachment_path'   => $data['attachment_path'] ?? null,
        ]);

        try {
            ActivityLogger::log(
                "Mengajukan penyesuaian stok blangko KTP-el [{$adj->adjustment_number}]: {$type} {$quantity} keping",
                $adj,
                'blank_stock_adjustment_request'
            );
        } catch (\Throwable $e) {}

        return $adj;
    }

    /**
     * Approve and execute stock adjustment.
     *
     * @throws ValidationException
     */
    public function approveAdjustment(KtpBlankAdjustment $adj, User $supervisor): KtpBlankAdjustment
    {
        if ($adj->status === KtpBlankAdjustment::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'adjustment' => 'Penyesuaian stok ini telah disetujui sebelumnya.',
            ]);
        }

        return DB::transaction(function () use ($adj, $supervisor) {
            /** @var KtpBlankStock $stock */
            $stock = KtpBlankStock::where('id', 1)->lockForUpdate()->firstOrFail();

            $balanceBefore = $stock->current_stock;
            $quantity = $adj->quantity;

            if ($adj->type === KtpBlankAdjustment::TYPE_DEDUCTION) {
                if ($balanceBefore < $quantity) {
                    throw ValidationException::withMessages([
                        'adjustment' => "Stok tidak mencukupi untuk melakukan pengurangan penyesuaian. Stok saat ini: {$balanceBefore}, pengurangan: {$quantity}.",
                    ]);
                }
                $balanceAfter = $balanceBefore - $quantity;
                $trxType = KtpBlankTransaction::TYPE_ADJUSTMENT_SUB;
                $qtyIn = 0;
                $qtyOut = $quantity;
                $adjDelta = -$quantity;
            } else {
                $balanceAfter = $balanceBefore + $quantity;
                $trxType = KtpBlankTransaction::TYPE_ADJUSTMENT_ADD;
                $qtyIn = $quantity;
                $qtyOut = 0;
                $adjDelta = $quantity;
            }

            $dayCode = now()->format('Ymd');
            $trxCount = KtpBlankTransaction::where('transaction_number', 'like', "TRX-{$dayCode}-%")->count();
            $trxSeq = str_pad($trxCount + 1, 4, '0', STR_PAD_LEFT);
            $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
            while (KtpBlankTransaction::where('transaction_number', $trxNumber)->exists()) {
                $trxSeq = str_pad(++$trxCount + 1, 4, '0', STR_PAD_LEFT);
                $trxNumber = "TRX-{$dayCode}-{$trxSeq}";
            }

            $trx = KtpBlankTransaction::create([
                'transaction_number' => $trxNumber,
                'transaction_date'   => now()->toDateString(),
                'transaction_type'   => $trxType,
                'reference_type'     => KtpBlankAdjustment::class,
                'reference_id'       => $adj->id,
                'qty_in'             => $qtyIn,
                'qty_out_used'       => 0,
                'qty_out_damaged'    => $qtyOut,
                'balance_before'     => $balanceBefore,
                'balance_after'      => $balanceAfter,
                'user_id'            => $supervisor->id,
                'notes'              => "Eksekusi Penyesuaian Stok [{$adj->adjustment_number}]: {$adj->reason}",
            ]);

            $adj->update([
                'status'         => KtpBlankAdjustment::STATUS_APPROVED,
                'approved_by'    => $supervisor->id,
                'approved_at'    => now(),
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
            ]);

            $stock->update([
                'current_stock'       => $balanceAfter,
                'total_adjusted'      => $stock->total_adjusted + $adjDelta,
                'last_transaction_id' => $trx->id,
                'last_transaction_at' => now(),
                'updated_by'          => $supervisor->id,
            ]);

            Cache::forget('public_blank_stock_summary');
            Cache::forget('public_blank_usage_reports');

            try {
                ActivityLogger::log(
                    "Pimpinan {$supervisor->name} menyetujui penyesuaian stok [{$adj->adjustment_number}]: {$adj->type} {$quantity} keping. Saldo baru: {$balanceAfter}",
                    $adj,
                    'blank_stock_adjustment_approve',
                    [
                        'adjustment_number' => $adj->adjustment_number,
                        'type'              => $adj->type,
                        'quantity'          => $quantity,
                        'balance_before'    => $balanceBefore,
                        'balance_after'     => $balanceAfter,
                    ]
                );
            } catch (\Throwable $e) {}

            return $adj;
        });
    }

    /**
     * Get internal dashboard summary & metrics.
     */
    public function getDashboardSummary(?string $startDate = null, ?string $endDate = null, ?int $operatorId = null): array
    {
        $stock = $this->getAuthoritativeStock();
        $today = now()->toDateString();
        $thisMonthStart = now()->startOfMonth()->toDateString();
        $thisMonthEnd = now()->endOfMonth()->toDateString();

        // 1. Today's metrics (all valid submitted/approved reports)
        $todayUsageQuery = KtpBlankUsageReport::where('usage_date', $today)
            ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED]);
        $todayUsed = (int) (clone $todayUsageQuery)->sum('quantity_used');
        $todayDamaged = (int) (clone $todayUsageQuery)->sum('quantity_damaged');
        $todayReceived = (int) KtpBlankReceipt::where('received_date', $today)->where('status', KtpBlankReceipt::STATUS_VERIFIED)->sum('quantity');

        // 2. This Month's metrics
        $monthUsageQuery = KtpBlankUsageReport::whereBetween('usage_date', [$thisMonthStart, $thisMonthEnd])
            ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED]);
        $monthUsed = (int) (clone $monthUsageQuery)->sum('quantity_used');
        $monthDamaged = (int) (clone $monthUsageQuery)->sum('quantity_damaged');
        $monthReceived = (int) KtpBlankReceipt::whereBetween('received_date', [$thisMonthStart, $thisMonthEnd])
            ->where('status', KtpBlankReceipt::STATUS_VERIFIED)
            ->sum('quantity');

        // 3. Operator reporting status today
        $allOperatorUsers = User::role('Operator Cetak KTP-el')->where('is_active', true)->get();
        $reportedOperatorIds = (clone $todayUsageQuery)->pluck('operator_id')->unique()->toArray();

        $operatorsReportedToday = count($reportedOperatorIds);
        $operatorsPendingToday = max(0, $allOperatorUsers->count() - $operatorsReportedToday);

        // 4. Daily usage chart data (Last 14 days)
        $chartStart = now()->subDays(13)->toDateString();
        $dailyRecords = KtpBlankUsageReport::whereBetween('usage_date', [$chartStart, $today])
            ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED])
            ->selectRaw('usage_date, SUM(quantity_used) as used, SUM(quantity_damaged) as damaged')
            ->groupBy('usage_date')
            ->orderBy('usage_date')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->usage_date)->format('Y-m-d'));

        $dailyChart = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $label = Carbon::parse($d)->translatedFormat('d M');
            $row = $dailyRecords->get($d);
            $dailyChart[] = [
                'date'    => $d,
                'label'   => $label,
                'used'    => $row ? (int) $row->used : 0,
                'damaged' => $row ? (int) $row->damaged : 0,
            ];
        }

        // 5. Operator breakdown for current month
        $operatorBreakdown = KtpBlankUsageReport::whereBetween('usage_date', [$thisMonthStart, $thisMonthEnd])
            ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED])
            ->with('operator:id,name,username')
            ->selectRaw('operator_id, SUM(quantity_used) as total_used, SUM(quantity_damaged) as total_damaged, COUNT(id) as total_reports')
            ->groupBy('operator_id')
            ->orderByDesc('total_used')
            ->get()
            ->map(function ($item) {
                return [
                    'operator_id'   => $item->operator_id,
                    'operator_name' => $item->operator ? $item->operator->name : 'Operator #' . $item->operator_id,
                    'total_used'    => (int) $item->total_used,
                    'total_damaged' => (int) $item->total_damaged,
                    'total'         => (int) ($item->total_used + $item->total_damaged),
                    'total_reports' => (int) $item->total_reports,
                ];
            });

        // 6. Recent Transactions (Last 10)
        $recentTransactions = KtpBlankTransaction::with(['user:id,name', 'operator:id,name'])
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function ($trx) {
                return [
                    'id'                 => $trx->id,
                    'transaction_number' => $trx->transaction_number,
                    'transaction_date'   => Carbon::parse($trx->transaction_date)->format('d M Y'),
                    'transaction_type'   => $trx->transaction_type,
                    'type_label'         => $trx->type_label,
                    'type_color'         => $trx->type_color,
                    'qty_in'             => $trx->qty_in,
                    'qty_out_used'       => $trx->qty_out_used,
                    'qty_out_damaged'    => $trx->qty_out_damaged,
                    'balance_after'      => $trx->balance_after,
                    'actor_name'         => $trx->user ? $trx->user->name : 'Sistem',
                    'operator_name'      => $trx->operator ? $trx->operator->name : null,
                    'notes'              => $trx->notes,
                ];
            });

        // 7. Active Alerts
        $alerts = [];
        if ($stock->current_stock <= 0) {
            $alerts[] = [
                'type'    => 'danger',
                'title'   => 'Stok Blangko Habis!',
                'message' => 'Persediaan blangko KTP-el saat ini kosong (0 keping). Segera koordinasikan penerimaan blangko baru dari Ditjen Dukcapil / Provinsi.',
            ];
        } elseif ($stock->current_stock <= $stock->min_stock_alert) {
            $alerts[] = [
                'type'    => 'warning',
                'title'   => 'Persediaan Menipis!',
                'message' => "Stok blangko saat ini tersisa {$stock->current_stock} keping (di bawah batas minimum {$stock->min_stock_alert} keping).",
            ];
        }

        if ($operatorsPendingToday > 0 && now()->hour >= 14) {
            $alerts[] = [
                'type'    => 'info',
                'title'   => 'Laporan Operator Belum Lengkap',
                'message' => "Terdapat {$operatorsPendingToday} Operator Cetak yang belum mengirimkan laporan penggunaan harian hari ini.",
            ];
        }

        $pendingAdjustmentsCount = KtpBlankAdjustment::where('status', KtpBlankAdjustment::STATUS_PENDING_APPROVAL)->count();
        if ($pendingAdjustmentsCount > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'title'   => 'Penyesuaian Stok Menunggu Persetujuan',
                'message' => "Ada {$pendingAdjustmentsCount} usulan penyesuaian stok yang menunggu verifikasi dan persetujuan Pimpinan.",
            ];
        }

        $lastOpname = KtpBlankOpname::where('status', KtpBlankOpname::STATUS_APPROVED)->latest('opname_date')->first() 
            ?? KtpBlankOpname::latest('opname_date')->first();

        $formattedLastUpdated = $stock->last_transaction_at ? $stock->last_transaction_at->format('d M Y, H:i') : '-';

        return [
            'stock' => [
                'current_stock'        => $stock->current_stock,
                'total_received'       => $stock->total_received,
                'total_used'           => $stock->total_used,
                'total_damaged'        => $stock->total_damaged,
                'total_adjusted'       => $stock->total_adjusted,
                'status'               => $stock->availability_status,
                'availability_status'  => $stock->availability_status,
                'status_color'         => $stock->status_color,
                'min_stock_alert'      => $stock->min_stock_alert,
                'critical_stock_alert' => $stock->critical_stock_alert,
                'last_transaction_at'  => $formattedLastUpdated,
                'last_updated'         => $formattedLastUpdated,
            ],
            'metrics' => [
                'today_used'           => $todayUsed,
                'today_damaged'        => $todayDamaged,
                'today_received'       => $todayReceived,
                'month_used'           => $monthUsed,
                'month_damaged'        => $monthDamaged,
                'month_received'       => $monthReceived,
            ],
            'operators_status' => [
                'reported_today'       => $operatorsReportedToday,
                'pending_today'        => $operatorsPendingToday,
            ],
            'pending_adjustments_count' => $pendingAdjustmentsCount,
            'last_opname' => $lastOpname ? [
                'date'       => Carbon::parse($lastOpname->opname_date)->format('d M Y'),
                'difference' => (int) $lastOpname->difference,
            ] : null,
            'today' => [
                'received'                  => $todayReceived,
                'used'                      => $todayUsed,
                'damaged'                   => $todayDamaged,
                'operators_reported'        => $operatorsReportedToday,
                'operators_pending'         => $operatorsPendingToday,
            ],
            'this_month' => [
                'received' => $monthReceived,
                'used'     => $monthUsed,
                'damaged'  => $monthDamaged,
            ],
            'daily_chart'         => $dailyChart,
            'operator_breakdown'  => $operatorBreakdown,
            'recent_transactions' => $recentTransactions,
            'alerts'              => $alerts,
        ];
    }

    /**
     * Get safe public stock availability summary.
     * Cached with invalidation upon any stock mutation.
     */
    public function getPublicStockSummary(): array
    {
        return Cache::remember('public_blank_stock_summary', 300, function () {
            $stock = $this->getAuthoritativeStock();
            $thisMonthStart = now()->startOfMonth()->toDateString();
            $thisMonthEnd = now()->endOfMonth()->toDateString();

            $monthReceived = (int) KtpBlankReceipt::whereBetween('received_date', [$thisMonthStart, $thisMonthEnd])
                ->where('status', KtpBlankReceipt::STATUS_VERIFIED)
                ->sum('quantity');

            $monthUsed = (int) KtpBlankUsageReport::whereBetween('usage_date', [$thisMonthStart, $thisMonthEnd])
                ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED])
                ->sum('quantity_used');

            $monthDamaged = (int) KtpBlankUsageReport::whereBetween('usage_date', [$thisMonthStart, $thisMonthEnd])
                ->whereIn('status', [KtpBlankUsageReport::STATUS_SUBMITTED, KtpBlankUsageReport::STATUS_APPROVED])
                ->sum('quantity_damaged');

            $lastUpdated = $stock->last_transaction_at ?? $stock->updated_at ?? now();

            return [
                'is_active'              => (bool) KtpBlankSetting::get('public_info_enabled', 1),
                'available_stock'        => $stock->current_stock,
                'current_stock'          => $stock->current_stock,
                'status'                 => $stock->availability_status,
                'status_color'           => $stock->status_color,
                'month_received'         => $monthReceived,
                'received_this_month'    => $monthReceived,
                'month_used'             => $monthUsed,
                'used_this_month'        => $monthUsed,
                'month_damaged'          => $monthDamaged,
                'damaged_this_month'     => $monthDamaged,
                'total_out_this_month'   => $monthUsed + $monthDamaged,
                'month_name'             => now()->translatedFormat('F Y'),
                'last_updated_at'        => Carbon::parse($lastUpdated)->translatedFormat('d F Y, H:i') . ' WITA',
                'last_updated_formatted' => Carbon::parse($lastUpdated)->translatedFormat('d F Y, H:i') . ' WITA',
                'office_name'            => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu',
                'office_address'         => 'Jl. Soekarno Hatta No. 09, Kabupaten Dompu, NTB',
            ];
        });
    }

    /**
     * Get safe public usage report details for transparency page.
     * Excludes internal operator PII and proof images.
     */
    public function getPublicUsageReports(int $limit = 15): array
    {
        return Cache::remember('public_blank_usage_reports', 300, function () use ($limit) {
            return KtpBlankUsageReport::whereIn('status', [
                    KtpBlankUsageReport::STATUS_SUBMITTED,
                    KtpBlankUsageReport::STATUS_APPROVED,
                ])
                ->orderByDesc('usage_date')
                ->orderByDesc('id')
                ->limit($limit)
                ->get([
                    'id',
                    'report_number',
                    'usage_date',
                    'session_name',
                    'quantity_used',
                    'quantity_damaged',
                    'total_quantity',
                    'damage_reason',
                    'status',
                ])
                ->map(function (KtpBlankUsageReport $report) {
                    return [
                        'id'                   => $report->id,
                        'report_number'        => $report->report_number,
                        'usage_date'           => $report->usage_date?->format('Y-m-d'),
                        'usage_date_formatted' => $report->usage_date
                            ? Carbon::parse($report->usage_date)->translatedFormat('d M Y')
                            : '-',
                        'session_name'         => $report->session_name ?: 'Sesi Harian',
                        'quantity_used'        => (int) $report->quantity_used,
                        'quantity_damaged'     => (int) $report->quantity_damaged,
                        'total_quantity'       => (int) $report->total_quantity,
                        'damage_reason'        => $report->damage_reason,
                        'status'               => $report->status,
                        'status_label'         => $report->status_label,
                    ];
                })
                ->values()
                ->all();
        });
    }
}
