<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('queue_applications')->cascadeOnDelete();
            $table->string('channel', 20)->default('email');
            $table->string('recipient', 150);
            $table->string('event_type', 50);
            $table->enum('status', ['sent', 'failed', 'skipped'])->default('skipped');
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['application_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_notification_logs');
    }
};
