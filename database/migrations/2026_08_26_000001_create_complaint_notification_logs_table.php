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
        Schema::create('complaint_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('channel')->default('mail');
            $table->string('recipient');
            $table->string('notification_type')->default('new_complaint');
            $table->enum('status', ['PENDING', 'SENDING', 'SENT', 'FAILED'])->default('PENDING');
            $table->string('message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Database-level Unique Constraint for Race-Condition-Proof Idempotency
            $table->unique(['complaint_id', 'channel', 'notification_type'], 'uniq_complaint_notif_event');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_notification_logs');
    }
};
