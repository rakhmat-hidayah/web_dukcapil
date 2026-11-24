<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use App\Models\SurveyPeriod;
use App\Services\SurveyCalculationService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Temukan periode yang aktif atau berjudul Survey Layanan
        $period = SurveyPeriod::where('is_active', true)->first()
            ?: SurveyPeriod::where('title', 'like', '%Survey Layanan%')->first()
            ?: SurveyPeriod::latest('id')->first();

        if ($period) {
            $period->update([
                'survey_mechanism' => 'panrb',
                'external_survey_url' => 'https://skm.go.id/share/instansi/9bf2f5e6-f779-451e-b07e-a6fb6c955f85/1',
                'external_ikm_value' => 88.50,
                'external_service_quality' => 'A',
                'external_total_respondents' => 250,
                'external_notes' => 'Survei Kepuasan Masyarakat terintegrasi resmi dengan Kementerian PAN-RB (skm.go.id)',
            ]);

            try {
                $calcService = app(SurveyCalculationService::class);
                $calcService->calculatePeriodStats($period->id);
            } catch (\Throwable $e) {
                // Silently continue
            }
        }

        Cache::forget('home_ikm_widget');
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
