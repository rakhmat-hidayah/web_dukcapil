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
        Schema::table('demographic_datasets', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester')->default(1)->after('year')->comment('1: Semester 1 (Juni), 2: Semester 2 (Desember)');
            $table->index(['year', 'semester', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('demographic_datasets', function (Blueprint $table) {
            $table->dropIndex(['year', 'semester', 'type', 'status']);
            $table->dropColumn('semester');
        });
    }
};
