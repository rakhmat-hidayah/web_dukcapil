<?php

namespace Tests\Feature;

use App\Models\Applicant\ApplicantAccount;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\ServiceType;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicantTrackingAuthTest extends TestCase
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
        DB::purge();
        DB::reconnect();
    }

    public function test_unauthenticated_user_cannot_track_status_via_check_route(): void
    {
        $response = $this->post(route('queue.check'), [
            'query' => 'REG-2611-TEST12',
        ]);

        $response->assertRedirect(route('applicant.login'));
        $response->assertSessionHas('warning');
    }

    public function test_authenticated_applicant_can_track_their_application(): void
    {
        ApplicantAccount::where('email_normalized', 'tracker_test@gmail.com')->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'Pemohon Tracker Test';
        $account->email_normalized = 'tracker_test@gmail.com';
        $account->nik = '5205011111110001';
        $account->password = bcrypt('Password123!');
        $account->email_verified_at = now();
        $account->account_status = ApplicantAccount::STATUS_ACTIVE;
        $account->identity_status = ApplicantAccount::IDENTITY_VERIFIED;
        $account->save();

        $service = ServiceType::first();

        $app = QueueApplication::firstOrCreate(
            ['reg_number' => 'REG-TEST-TRACK-01'],
            [
                'applicant_account_id' => $account->id,
                'applicant_nik' => $account->nik,
                'applicant_name' => $account->name,
                'service_type_id' => $service ? $service->id : 1,
                'status' => QueueApplication::STATUS_SUBMITTED,
                'access_token' => 'token_test_track_01',
            ]
        );

        $response = $this->actingAs($account, 'applicant')->post(route('queue.check'), [
            'query' => 'REG-TEST-TRACK-01',
        ]);

        $response->assertRedirect(route('queue.status', ['token' => $app->access_token]));
    }
}
