<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('queue_schedules')->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained('queue_service_types')->cascadeOnDelete();
            $table->integer('quota')->default(0);
            $table->integer('booked_count')->default(0);
            $table->timestamps();
            $table->unique(['schedule_id', 'service_type_id']);
            $table->index(['schedule_id', 'service_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_slots');
    }
};
