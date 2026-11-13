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
        if (Schema::hasTable('applicant_accounts')) {
            Schema::table('applicant_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('applicant_accounts', 'selfie_path')) {
                    $table->string('selfie_path', 255)->nullable()->after('phone');
                }
                if (!Schema::hasColumn('applicant_accounts', 'selfie_deleted_at')) {
                    $table->timestamp('selfie_deleted_at')->nullable()->after('selfie_path');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('applicant_accounts')) {
            Schema::table('applicant_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('applicant_accounts', 'selfie_deleted_at')) {
                    $table->dropColumn('selfie_deleted_at');
                }
                if (Schema::hasColumn('applicant_accounts', 'selfie_path')) {
                    $table->dropColumn('selfie_path');
                }
            });
        }
    }
};
