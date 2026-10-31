<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('description', 300)->nullable();
            $table->timestamps();
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_settings');
    }
};
