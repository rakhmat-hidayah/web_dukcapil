<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('Null = system note, non-null = admin reply');
            $table->text('message');
            $table->enum('type', ['admin_reply', 'status_change', 'note'])
                  ->default('admin_reply');
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->boolean('is_visible_to_submitter')->default(true)
                  ->comment('Internal notes are hidden from public tracker');
            $table->timestamps();

            $table->index(['complaint_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_replies');
    }
};
