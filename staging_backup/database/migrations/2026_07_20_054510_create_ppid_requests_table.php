<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppid_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // PPID-YYYY-XXXXXX
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('requester_phone')->nullable();
            $table->string('requester_address')->nullable();
            $table->string('requester_id_number')->nullable(); // KTP number
            $table->string('purpose');                // tujuan penggunaan informasi
            $table->text('information_requested');    // deskripsi informasi yang diminta
            $table->enum('request_method', ['online', 'langsung', 'surat'])->default('online');
            $table->enum('delivery_method', ['email', 'langsung', 'salinan'])->default('email');
            $table->enum('status', ['diterima', 'diproses', 'selesai', 'ditolak'])->default('diterima');
            $table->text('response_notes')->nullable();  // catatan admin
            $table->string('response_file')->nullable(); // file balasan
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_requests');
    }
};
