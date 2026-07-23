<?php

namespace Tests\Feature;

use App\Models\SurveyPeriod;
use App\Models\SurveyQuestion;
use App\Services\SurveyCalculationService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SurveyModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'dukcapil_dompukab']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
        \Illuminate\Support\Facades\DB::purge();
        \Illuminate\Support\Facades\DB::reconnect();
        Cache::forget('nav_menus');
    }

    public function test_public_survey_page_can_be_rendered()
    {
        $response = $this->get('/layanan/survei');
        $response->assertStatus(200);
    }

    public function test_survey_calculation_service_classifies_permenpanrb_categories_correctly()
    {
        $gradeA = SurveyCalculationService::classifyIkmCategory(92.5);
        $this->assertEquals('A', $gradeA['category']);
        $this->assertEquals('Sangat Baik', $gradeA['label']);

        $gradeB = SurveyCalculationService::classifyIkmCategory(82.0);
        $this->assertEquals('B', $gradeB['category']);
        $this->assertEquals('Baik', $gradeB['label']);

        $gradeC = SurveyCalculationService::classifyIkmCategory(70.0);
        $this->assertEquals('C', $gradeC['category']);

        $gradeD = SurveyCalculationService::classifyIkmCategory(55.0);
        $this->assertEquals('D', $gradeD['category']);
    }

    public function test_public_survey_api_endpoints_return_successful_json()
    {
        $responseCurrent = $this->getJson('/api/v1/survey/current');
        $responseCurrent->assertStatus(200)->assertJson(['status' => 'success']);

        $responseResults = $this->getJson('/api/v1/survey/results');
        $responseResults->assertStatus(200)->assertJson(['status' => 'success']);

        $responseArchive = $this->getJson('/api/v1/survey/archive');
        $responseArchive->assertStatus(200)->assertJson(['status' => 'success']);

        $responseStats = $this->getJson('/api/v1/survey/statistics');
        $responseStats->assertStatus(200)->assertJson(['status' => 'success']);
    }

    public function test_user_can_submit_public_survey_response()
    {
        $period = SurveyPeriod::where('is_active', true)->first();
        $this->assertNotNull($period);

        $question = SurveyQuestion::where('survey_period_id', $period->id)->first();
        $this->assertNotNull($question);

        $response = $this->withoutMiddleware()->post('/layanan/survei', [
            'survey_period_id' => $period->id,
            'respondent_name' => 'Budi Testing',
            'service_accessed' => 'Perekaman KTP-el',
            'suggestion' => 'Sangat bagus!',
            'answers' => [
                $question->id => 4,
            ],
        ]);

        $response->assertStatus(302);
    }
}
