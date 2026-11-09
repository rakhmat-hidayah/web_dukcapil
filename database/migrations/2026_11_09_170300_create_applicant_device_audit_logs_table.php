<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applicant_device_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_account_id')
                ->constrained('applicant_accounts')
                ->cascadeOnDelete();
            $table->foreignId('device_id')
                ->nullable()
                ->constrained('applicant_devices')
                ->nullOnDelete();
            $table->string('event_type', 60);
            $table->enum('actor_type', ['applicant', 'admin', 'system'])->default('applicant');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('reason', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['applicant_account_id', 'created_at'], 'ad_logs_acc_created_idx');
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_device_audit_logs');
    }
};
