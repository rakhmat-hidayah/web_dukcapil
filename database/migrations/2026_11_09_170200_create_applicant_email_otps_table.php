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
        Schema::create('applicant_email_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_account_id')
                ->nullable()
                ->constrained('applicant_accounts')
                ->cascadeOnDelete();
            $table->string('email_normalized', 100)->index();
            $table->string('otp_hash', 255);
            $table->enum('purpose', ['registration', 'device_recovery', 'password_reset'])->default('registration');
            $table->timestamp('expires_at')->index();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(3);
            $table->unsignedSmallInteger('resend_count')->default(0);
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['email_normalized', 'purpose', 'consumed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_email_otps');
    }
};
