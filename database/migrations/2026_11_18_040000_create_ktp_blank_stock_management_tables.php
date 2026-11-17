<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Authoritative Singleton Stock Summary Table
        Schema::create('ktp_blank_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('current_stock')->default(0);
            $table->unsignedBigInteger('total_received')->default(0);
            $table->unsignedBigInteger('total_used')->default(0);
            $table->unsignedBigInteger('total_damaged')->default(0);
            $table->bigInteger('total_adjusted')->default(0);
            $table->unsignedInteger('min_stock_alert')->default(500);
            $table->unsignedInteger('critical_stock_alert')->default(100);
            $table->unsignedBigInteger('last_transaction_id')->nullable();
            $table->timestamp('last_transaction_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Penerimaan Blangko (Receipts from Ministry / Provincial Dukcapil)
        Schema::create('ktp_blank_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->date('received_date')->index();
            $table->unsignedInteger('quantity');
            $table->string('source', 200);
            $table->string('document_number', 100)->index();
            $table->date('document_date');
            $table->foreignId('received_by')->constrained('users')->cascadeOnUpdate();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 30)->default('VERIFIED')->index();
            $table->unsignedInteger('balance_before');
            $table->unsignedInteger('balance_after');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Laporan Penggunaan Harian Operator Cetak
        Schema::create('ktp_blank_usage_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number', 50)->unique();
            $table->date('usage_date')->index();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnUpdate();
            $table->string('session_name', 50)->nullable()->default('Harian Penuh');
            $table->unsignedInteger('quantity_used')->default(0);
            $table->unsignedInteger('quantity_damaged')->default(0);
            $table->unsignedInteger('total_quantity')->default(0);
            $table->text('damage_reason')->nullable();
            $table->string('damage_proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('SUBMITTED')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->unsignedInteger('balance_before')->nullable();
            $table->unsignedInteger('balance_after')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['usage_date', 'operator_id']);
        });

        // 4. Stok Opname Fisik & Rekonsiliasi
        Schema::create('ktp_blank_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number', 50)->unique();
            $table->date('opname_date')->index();
            $table->integer('system_stock');
            $table->integer('physical_stock');
            $table->integer('difference'); // physical - system
            $table->foreignId('checked_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('status', 30)->default('DRAFT')->index(); // DRAFT, PENDING_APPROVAL, APPROVED, REJECTED
            $table->text('notes')->nullable();
            $table->string('evidence_path')->nullable();
            $table->timestamps();
        });

        // 5. Penyesuaian & Koreksi Stok Sah
        Schema::create('ktp_blank_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique();
            $table->date('adjustment_date')->index();
            $table->string('type', 20)->index(); // ADDITION, DEDUCTION
            $table->unsignedInteger('quantity');
            $table->text('reason');
            $table->foreignId('opname_id')->nullable()->constrained('ktp_blank_opnames')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('status', 30)->default('PENDING_APPROVAL')->index(); // PENDING_APPROVAL, APPROVED, REJECTED
            $table->string('attachment_path')->nullable();
            $table->unsignedInteger('balance_before')->nullable();
            $table->unsignedInteger('balance_after')->nullable();
            $table->timestamps();
        });

        // 6. Kartu Persediaan / Buku Mutasi Induk (Immutable Ledger)
        Schema::create('ktp_blank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date')->index();
            $table->string('transaction_type', 30)->index(); // RECEIPT, USAGE, DAMAGE, ADJUSTMENT_ADD, ADJUSTMENT_SUB, REVERSAL
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedInteger('qty_in')->default(0);
            $table->unsignedInteger('qty_out_used')->default(0);
            $table->unsignedInteger('qty_out_damaged')->default(0);
            $table->unsignedInteger('balance_before');
            $table->unsignedInteger('balance_after');
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
            $table->index(['transaction_date', 'transaction_type']);
        });

        // 7. Pengaturan Modul Persediaan Blangko
        Schema::create('ktp_blank_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // Initialize single authoritative stock record (id = 1)
        DB::table('ktp_blank_stocks')->insert([
            'id'                   => 1,
            'current_stock'        => 0,
            'total_received'       => 0,
            'total_used'           => 0,
            'total_damaged'        => 0,
            'total_adjusted'       => 0,
            'min_stock_alert'      => 500,
            'critical_stock_alert' => 100,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Default Settings
        $defaultSettings = [
            ['key' => 'stock_threshold_limited', 'value' => '500', 'description' => 'Batas minimum persediaan untuk status Terbatas di halaman publik'],
            ['key' => 'stock_threshold_empty', 'value' => '0', 'description' => 'Batas stok untuk status Kosong di halaman publik'],
            ['key' => 'public_info_enabled', 'value' => '1', 'description' => 'Status aktif publikasi ketersediaan blangko untuk masyarakat'],
            ['key' => 'require_damage_proof', 'value' => '0', 'description' => 'Wajib mengunggah bukti foto dokumen blangko rusak'],
            ['key' => 'allow_operator_draft', 'value' => '1', 'description' => 'Izinkan operator menyimpan laporan penggunaan sebagai draf'],
            ['key' => 'notify_on_low_stock', 'value' => '1', 'description' => 'Tampilkan notifikasi jika stok di bawah batas aman'],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('ktp_blank_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ktp_blank_settings');
        Schema::dropIfExists('ktp_blank_transactions');
        Schema::dropIfExists('ktp_blank_adjustments');
        Schema::dropIfExists('ktp_blank_opnames');
        Schema::dropIfExists('ktp_blank_usage_reports');
        Schema::dropIfExists('ktp_blank_receipts');
        Schema::dropIfExists('ktp_blank_stocks');
    }
};
