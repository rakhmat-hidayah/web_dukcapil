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
        Schema::table('survey_periods', function (Blueprint $table) {
            $table->string('survey_mechanism', 30)->default('internal')->after('target_respondents'); // 'internal' or 'panrb'
            $table->string('external_survey_url', 500)->nullable()->after('survey_mechanism');
            $table->decimal('external_ikm_value', 5, 2)->nullable()->after('external_survey_url');
            $table->string('external_service_quality', 2)->nullable()->after('external_ikm_value');
            $table->integer('external_total_respondents')->nullable()->after('external_service_quality');
            $table->text('external_notes')->nullable()->after('external_total_respondents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_periods', function (Blueprint $table) {
            $table->dropColumn([
                'survey_mechanism',
                'external_survey_url',
                'external_ikm_value',
                'external_service_quality',
                'external_total_respondents',
                'external_notes',
            ]);
        });
    }
};
