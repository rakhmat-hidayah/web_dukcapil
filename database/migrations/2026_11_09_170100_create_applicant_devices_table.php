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
        Schema::create('applicant_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_account_id')
                ->constrained('applicant_accounts')
                ->cascadeOnDelete();
            $table->string('device_token_hash', 64)->index();
            $table->string('device_label', 150)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->string('revocation_reason', 255)->nullable();
            $table->string('revoked_by_type', 30)->nullable();
            $table->unsignedBigInteger('revoked_by_id')->nullable();
            $table->timestamps();

            $table->index(['applicant_account_id', 'revoked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_devices');
    }
};
