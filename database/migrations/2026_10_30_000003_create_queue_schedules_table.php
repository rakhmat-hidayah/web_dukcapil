<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('schedule_date')->unique();
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('15:30:00');
            $table->integer('total_quota')->default(0)->comment('Sum of all slots');
            $table->boolean('is_holiday')->default(false);
            $table->string('holiday_note', 200)->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
            $table->index('schedule_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_schedules');
    }
};
