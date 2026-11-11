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
            if (!Schema::hasColumn('queue_applications', 'family_shdk')) {
                $table->string('family_shdk', 50)->nullable()->after('applicant_email')
                    ->comment('Status Hubungan Dalam Keluarga (SHDK) jika pemohon mewakili anggota keluarga 1 KK');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            if (Schema::hasColumn('queue_applications', 'family_shdk')) {
                $table->dropColumn('family_shdk');
            }
        });
    }
};
