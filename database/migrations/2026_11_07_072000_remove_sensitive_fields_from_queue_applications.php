<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom data pribadi sensitif dari tabel queue_applications
     * untuk meningkatkan keamanan dan privasi data pemohon.
     */
    public function up(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            $columns = [
                'applicant_birth_place',
                'applicant_birth_date',
                'applicant_phone',
                'applicant_address',
                'applicant_village',
                'applicant_district',
            ];

            $existing = [];
            foreach ($columns as $col) {
                if (Schema::hasColumn('queue_applications', $col)) {
                    $existing[] = $col;
                }
            }

            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            $table->string('applicant_birth_place', 60)->nullable()->after('applicant_nik');
            $table->date('applicant_birth_date')->nullable()->after('applicant_birth_place');
            $table->string('applicant_phone', 20)->nullable()->after('applicant_birth_date');
            $table->string('applicant_address', 300)->nullable()->after('applicant_email');
            $table->string('applicant_village', 100)->nullable()->after('applicant_address');
            $table->string('applicant_district', 100)->nullable()->after('applicant_village');
        });
    }
};
