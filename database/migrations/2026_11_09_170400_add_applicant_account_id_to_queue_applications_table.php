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
        Schema::table('queue_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('queue_applications', 'applicant_account_id')) {
                $table->foreignId('applicant_account_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('applicant_accounts')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            if (Schema::hasColumn('queue_applications', 'applicant_account_id')) {
                $table->dropForeign(['applicant_account_id']);
                $table->dropColumn('applicant_account_id');
            }
        });
    }
};
