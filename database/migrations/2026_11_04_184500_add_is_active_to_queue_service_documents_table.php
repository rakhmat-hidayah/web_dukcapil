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
        Schema::table('queue_service_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('queue_service_documents', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_required');
                $table->index(['service_type_id', 'is_active', 'sort_order'], 'qsd_service_active_sort_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_service_documents', function (Blueprint $table) {
            if (Schema::hasColumn('queue_service_documents', 'is_active')) {
                $table->dropIndex('qsd_service_active_sort_idx');
                $table->dropColumn('is_active');
            }
        });
    }
};
