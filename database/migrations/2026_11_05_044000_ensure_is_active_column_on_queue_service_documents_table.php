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
        if (Schema::hasTable('queue_service_documents')) {
            Schema::table('queue_service_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('queue_service_documents', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_required');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe: Retain column
    }
};
