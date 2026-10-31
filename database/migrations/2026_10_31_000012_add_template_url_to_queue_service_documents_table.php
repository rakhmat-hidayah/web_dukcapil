<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('queue_service_documents') && !Schema::hasColumn('queue_service_documents', 'template_url')) {
            Schema::table('queue_service_documents', function (Blueprint $table) {
                $table->string('template_url', 500)->nullable()->after('applicable_to')
                    ->comment('Link download blangko / formulir resmi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('queue_service_documents') && Schema::hasColumn('queue_service_documents', 'template_url')) {
            Schema::table('queue_service_documents', function (Blueprint $table) {
                $table->dropColumn('template_url');
            });
        }
    }
};
