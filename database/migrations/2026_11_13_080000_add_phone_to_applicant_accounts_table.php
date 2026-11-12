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
        if (Schema::hasTable('applicant_accounts') && !Schema::hasColumn('applicant_accounts', 'phone')) {
            Schema::table('applicant_accounts', function (Blueprint $table) {
                $table->string('phone', 20)->nullable()->after('email_normalized');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('applicant_accounts') && Schema::hasColumn('applicant_accounts', 'phone')) {
            Schema::table('applicant_accounts', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
    }
};
