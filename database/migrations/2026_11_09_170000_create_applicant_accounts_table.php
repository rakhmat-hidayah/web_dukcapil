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
        Schema::create('applicant_accounts', function (Blueprint $table) {
            $table->id();
            $table->text('nik_encrypted');
            $table->string('nik_hash', 64)->unique()->comment('HMAC-SHA256 blind index of NIK');
            $table->string('name', 60);
            $table->string('email_normalized', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('identity_status', ['PENDING', 'VERIFIED', 'REJECTED'])->default('PENDING');
            $table->enum('account_status', ['EMAIL_PENDING', 'IDENTITY_PENDING', 'ACTIVE', 'SUSPENDED', 'LOCKED'])->default('EMAIL_PENDING');
            $table->string('status_reason', 255)->nullable();
            $table->timestamp('identity_verified_at')->nullable();
            $table->unsignedBigInteger('identity_verified_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_status');
            $table->index('identity_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_accounts');
    }
};
