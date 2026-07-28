<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dusun_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->unsignedSmallInteger('number');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rws');
    }
};
