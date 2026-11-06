<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom nomor telepon/WhatsApp perwakilan (representative_phone)
     * dari tabel queue_applications untuk menjaga kerahasiaan dan keamanan data.
     */
    public function up(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            if (Schema::hasColumn('queue_applications', 'representative_phone')) {
                $table->dropColumn('representative_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_applications', function (Blueprint $table) {
            $table->string('representative_phone', 20)->nullable()->after('representative_name');
        });
    }
};
