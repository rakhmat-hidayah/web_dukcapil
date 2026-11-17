<?php

namespace Tests\Feature;

use App\Models\BlankStock\KtpBlankAdjustment;
use App\Models\BlankStock\KtpBlankOpname;
use App\Models\BlankStock\KtpBlankReceipt;
use App\Models\BlankStock\KtpBlankStock;
use App\Models\BlankStock\KtpBlankTransaction;
use App\Models\BlankStock\KtpBlankUsageReport;
use App\Models\User;
use App\Services\BlankStock\BlankStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KtpBlankStockManagementTest extends TestCase
{
    protected BlankStockService $stockService;
    protected User $bendahara;
    protected User $supervisor;
    protected User $operatorA;
    protected User $operatorB;
    protected User $operatorC;
    protected User $operatorD;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(BlankStockService::class);

        // Run permission seeder
        $this->artisan('db:seed', ['--class' => 'KtpBlankStockPermissionSeeder']);

        // Set up test users
        $this->bendahara = User::where('username', 'bendahara_barang')->first() ?? User::factory()->create(['name' => 'Bendahara Barang', 'username' => 'bendahara_barang']);
        if (!$this->bendahara->hasRole('Bendahara Barang')) {
            $this->bendahara->assignRole('Bendahara Barang');
        }

        $this->supervisor = User::where('username', 'supervisor_ktpel')->first() ?? User::factory()->create(['name' => 'Supervisor Layanan', 'username' => 'supervisor_ktpel']);
        if (!$this->supervisor->hasRole('Supervisor Layanan KTP-el')) {
            $this->supervisor->assignRole('Supervisor Layanan KTP-el');
        }

        $this->operatorA = User::where('username', 'operator_cetak_a')->first() ?? User::factory()->create(['name' => 'Operator A', 'username' => 'operator_cetak_a']);
        if (!$this->operatorA->hasRole('Operator Cetak KTP-el')) {
            $this->operatorA->assignRole('Operator Cetak KTP-el');
        }

        $this->operatorB = User::where('username', 'operator_cetak_b')->first() ?? User::factory()->create(['name' => 'Operator B', 'username' => 'operator_cetak_b']);
        if (!$this->operatorB->hasRole('Operator Cetak KTP-el')) {
            $this->operatorB->assignRole('Operator Cetak KTP-el');
        }

        $this->operatorC = User::factory()->create(['name' => 'Operator C', 'username' => 'operator_cetak_c']);
        $this->operatorC->assignRole('Operator Cetak KTP-el');

        $this->operatorD = User::factory()->create(['name' => 'Operator D', 'username' => 'operator_cetak_d']);
        $this->operatorD->assignRole('Operator Cetak KTP-el');

        // Reset singleton stock table to 0 before tests
        $stock = KtpBlankStock::firstOrCreate(['id' => 1], [
            'current_stock'        => 0,
            'total_received'       => 0,
            'total_used'           => 0,
            'total_damaged'        => 0,
            'total_adjusted'       => 0,
            'min_stock_alert'      => 100,
            'critical_stock_alert' => 20,
        ]);
        $stock->update([
            'current_stock'  => 0,
            'total_received' => 0,
            'total_used'     => 0,
            'total_damaged'  => 0,
            'total_adjusted' => 0,
        ]);
    }

    /**
     * Skenario 1: Penerimaan 500 keping oleh Bendahara Barang menambah saldo persediaan.
     */
    public function test_bendahara_records_receipt_and_stock_increases(): void
    {
        $response = $this->actingAs($this->bendahara)->post(route('admin.blank-stocks.receipts.store'), [
            'received_date'   => now()->toDateString(),
            'quantity'        => 500,
            'source'          => 'Ditjen Dukcapil Kemendagri',
            'document_number' => 'BAST-001/DUKCAPIL/2026',
            'document_date'   => now()->toDateString(),
            'notes'           => 'Penerimaan alokasi blangko tahap I',
        ]);

        $response->assertRedirect();

        $stock = $this->stockService->getAuthoritativeStock();
        $this->assertEquals(500, $stock->current_stock);
        $this->assertEquals(500, $stock->total_received);

        // Assert immutable transaction ledger was written
        $this->assertDatabaseHas('ktp_blank_transactions', [
            'transaction_type' => KtpBlankTransaction::TYPE_RECEIPT,
            'qty_in'           => 500,
            'balance_after'    => 500,
            'user_id'          => $this->bendahara->id,
        ]);
    }

    /**
     * Skenario 2: Multi-Operator mencatat penggunaan serentak dan saldo persediaan terpotong atomic tepat.
     */
    public function test_multi_operator_concurrency_and_balance_deduction(): void
    {
        // Initial receipt 500
        $this->stockService->recordReceipt([
            'received_date'   => now()->toDateString(),
            'quantity'        => 500,
            'source'          => 'Ditjen Dukcapil Kemendagri',
            'document_number' => 'BAST-TEST/2026',
            'document_date'   => now()->toDateString(),
        ], $this->bendahara);

        // Op A: 20 used, 1 damaged = 21
        $this->stockService->recordUsageReport([
            'usage_date'       => now()->toDateString(),
            'session_name'     => 'Pelayanan Pagi',
            'quantity_used'    => 20,
            'quantity_damaged' => 1,
            'damage_reason'    => 'Chip error',
            'is_draft'         => false,
        ], $this->operatorA);

        // Op B: 15 used, 0 damaged = 15
        $this->stockService->recordUsageReport([
            'usage_date'       => now()->toDateString(),
            'session_name'     => 'Pelayanan Pagi',
            'quantity_used'    => 15,
            'quantity_damaged' => 0,
            'is_draft'         => false,
        ], $this->operatorB);

        // Op C: 18 used, 2 damaged = 20
        $this->stockService->recordUsageReport([
            'usage_date'       => now()->toDateString(),
            'session_name'     => 'Pelayanan Siang',
            'quantity_used'    => 18,
            'quantity_damaged' => 2,
            'damage_reason'    => 'Ribbon macet',
            'is_draft'         => false,
        ], $this->operatorC);

        // Op D: 12 used, 1 damaged = 13
        $this->stockService->recordUsageReport([
            'usage_date'       => now()->toDateString(),
            'session_name'     => 'Pelayanan Sore',
            'quantity_used'    => 12,
            'quantity_damaged' => 1,
            'damage_reason'    => 'Kartu tergores',
            'is_draft'         => false,
        ], $this->operatorD);

        // Total out: 21 + 15 + 20 + 13 = 69
        // Expected current stock: 500 - 69 = 431
        $stock = $this->stockService->getAuthoritativeStock();
        $this->assertEquals(431, $stock->current_stock);
        $this->assertEquals(65, $stock->total_used);
        $this->assertEquals(4, $stock->total_damaged);
    }

    /**
     * Skenario 3: Penolakan transaksi jika persediaan tidak mencukupi (Pencegahan Stok Negatif).
     */
    public function test_cannot_deduct_more_than_available_stock(): void
    {
        // Initial receipt 50
        $this->stockService->recordReceipt([
            'received_date'   => now()->toDateString(),
            'quantity'        => 50,
            'source'          => 'Provinsi NTB',
            'document_number' => 'BAST-002',
            'document_date'   => now()->toDateString(),
        ], $this->bendahara);

        $this->expectException(ValidationException::class);

        // Attempt to deduct 51 (exceeds 50)
        $this->stockService->recordUsageReport([
            'usage_date'       => now()->toDateString(),
            'session_name'     => 'Cetak Luar Batas',
            'quantity_used'    => 50,
            'quantity_damaged' => 1,
            'damage_reason'    => 'Kartu rusak',
            'is_draft'         => false,
        ], $this->operatorA);
    }

    /**
     * Skenario 4: Operator tidak dapat melihat atau mengedit laporan milik operator lain (IDOR Protection).
     */
    public function test_operator_cannot_access_other_operator_reports(): void
    {
        // Initial receipt 50 keping
        $this->stockService->recordReceipt([
            'received_date'   => now()->toDateString(),
            'quantity'        => 50,
            'source'          => 'Ditjen Dukcapil',
            'document_number' => 'BAST-TEST-OP',
            'document_date'   => now()->toDateString(),
        ], $this->bendahara);

        // Op A creates a report
        $reportA = $this->stockService->recordUsageReport([
            'usage_date'       => now()->toDateString(),
            'session_name'     => 'Sesi Rahasia A',
            'quantity_used'    => 5,
            'quantity_damaged' => 0,
            'is_draft'         => true,
        ], $this->operatorA);

        // Op B visits usage index -> reportA should not be visible in op B's query
        $response = $this->actingAs($this->operatorB)->get(route('admin.blank-stocks.usage.index'));
        $response->assertOk();

        // Operator B attempts to submit draft owned by Operator A -> 403 Forbidden
        $submitResponse = $this->actingAs($this->operatorB)->post(route('admin.blank-stocks.usage.submit-draft', $reportA->id));
        $submitResponse->assertForbidden();
    }

    /**
     * Skenario 5: Operator tidak diizinkan mencatat penerimaan atau mengubah pengaturan.
     */
    public function test_operator_cannot_record_receipt_or_manage_settings(): void
    {
        // Attempt receipt
        $receiptResponse = $this->actingAs($this->operatorA)->post(route('admin.blank-stocks.receipts.store'), [
            'quantity' => 100,
        ]);
        $receiptResponse->assertForbidden();

        // Attempt settings update
        $settingsResponse = $this->actingAs($this->operatorA)->post(route('admin.blank-stocks.settings.update'), [
            'min_stock_alert' => 50,
        ]);
        $settingsResponse->assertForbidden();
    }

    /**
     * Skenario 6: Reversal transaksi mengembalikan stok persediaan secara sah dengan audit log.
     */
    public function test_reversal_transaction_restores_stock_accurately(): void
    {
        // Receipt 100
        $this->stockService->recordReceipt([
            'received_date'   => now()->toDateString(),
            'quantity'        => 100,
            'source'          => 'Ditjen Dukcapil',
            'document_number' => 'BAST-REV-1',
            'document_date'   => now()->toDateString(),
        ], $this->bendahara);

        // Usage 20 -> balance becomes 80
        $report = $this->stockService->recordUsageReport([
            'usage_date'       => now()->toDateString(),
            'session_name'     => 'Pelayanan Pagi',
            'quantity_used'    => 20,
            'quantity_damaged' => 0,
            'is_draft'         => false,
        ], $this->operatorA);

        $this->assertEquals(80, $this->stockService->getAuthoritativeStock()->current_stock);

        // Supervisor reverses report
        $reversalResponse = $this->actingAs($this->supervisor)->post(route('admin.blank-stocks.usage.reverse', $report->id), [
            'reversal_reason' => 'Salah input tanggal dan kuantiti sesi cetak',
        ]);
        $reversalResponse->assertRedirect();

        // Stock restored back to 100
        $stock = $this->stockService->getAuthoritativeStock();
        $this->assertEquals(100, $stock->current_stock);
        $this->assertEquals(0, $stock->total_used);

        // Assert reversal transaction was created
        $this->assertDatabaseHas('ktp_blank_transactions', [
            'transaction_type' => KtpBlankTransaction::TYPE_REVERSAL,
            'qty_in'           => 20,
            'balance_after'    => 100,
            'user_id'          => $this->supervisor->id,
        ]);
    }

    /**
     * Skenario 7: Stok Opname Fisik mencatat selisih tanpa mutasi otomatis sampai disetujui penyesuaian.
     */
    public function test_opname_does_not_mutate_stock_until_adjustment_approved(): void
    {
        // Receipt 100
        $this->stockService->recordReceipt([
            'received_date'   => now()->toDateString(),
            'quantity'        => 100,
            'source'          => 'Ditjen Dukcapil',
            'document_number' => 'BAST-OP-1',
            'document_date'   => now()->toDateString(),
        ], $this->bendahara);

        // Bendahara records Opname with physical 98 (difference -2)
        $opname = $this->stockService->recordOpname([
            'opname_date'    => now()->toDateString(),
            'physical_stock' => 98,
            'notes'          => 'Ditemukan selisih 2 keping rusak saat penghitungan brankas',
        ], $this->bendahara);

        $this->assertEquals(-2, $opname->difference);
        // Stock MUST REMAIN 100 before adjustment approval
        $this->assertEquals(100, $this->stockService->getAuthoritativeStock()->current_stock);

        // Supervisor approves opname BA
        $this->stockService->approveOpname($opname, $this->supervisor);
        $this->assertEquals(100, $this->stockService->getAuthoritativeStock()->current_stock);

        // Bendahara creates adjustment deduction of 2
        $adj = $this->stockService->createAdjustment([
            'adjustment_date' => now()->toDateString(),
            'type'            => KtpBlankAdjustment::TYPE_DEDUCTION,
            'quantity'        => 2,
            'reason'          => 'Koreksi penyesuaian fisik berdasarkan BA Opname ' . $opname->opname_number,
            'opname_id'       => $opname->id,
        ], $this->bendahara);

        // Still 100 pending supervisor approval
        $this->assertEquals(100, $this->stockService->getAuthoritativeStock()->current_stock);

        // Supervisor approves adjustment
        $this->stockService->approveAdjustment($adj, $this->supervisor);

        // Now stock is officially adjusted to 98!
        $this->assertEquals(98, $this->stockService->getAuthoritativeStock()->current_stock);
        $this->assertEquals(-2, $this->stockService->getAuthoritativeStock()->total_adjusted);
    }

    /**
     * Skenario 8: Halaman publik menampilkan data agregat aman tanpa membocorkan data pribadi atau nama operator.
     */
    public function test_public_stock_page_is_accessible_and_privacy_compliant(): void
    {
        $this->stockService->recordReceipt([
            'received_date'   => now()->toDateString(),
            'quantity'        => 600,
            'source'          => 'Ditjen Dukcapil',
            'document_number' => 'BAST-PUB-1',
            'document_date'   => now()->toDateString(),
        ], $this->bendahara);

        $response = $this->get(route('public.blangko.index'));
        $response->assertOk();

        $summary = $this->stockService->getPublicStockSummary();
        $this->assertEquals('TERSEDIA', $summary['status']);
        $this->assertEquals(600, $summary['current_stock']);

        // Assert strictly privacy: No internal operator names or user IDs in public payload
        $this->assertArrayNotHasKey('operator_id', $summary);
        $this->assertArrayNotHasKey('operator_name', $summary);
        $this->assertArrayNotHasKey('nik', $summary);
    }
}
