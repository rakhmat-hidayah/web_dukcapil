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
        Schema::create('innovations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon', 50)->nullable()->comment('Emoji or icon name');
            $table->string('color', 20)->default('#4f46e5');
            $table->string('description', 500)->nullable();
            $table->text('content')->comment('Detailed rich text explaining the innovation');
            $table->string('youtube_url')->nullable()->comment('Explainer YouTube video embed link');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('innovations');
    }
};
