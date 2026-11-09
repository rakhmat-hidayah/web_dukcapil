<?php

namespace Tests\Feature;

use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantDevice;
use App\Models\Applicant\ApplicantEmailOtp;
use App\Models\User;
use App\Services\Applicant\ApplicantDeviceService;
use App\Services\Applicant\ApplicantOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicantDeviceBindingTest extends TestCase
{
    protected function createVerifiedAccount(string $nik = '5205011205950099', string $email = 'uji.device@gmail.com'): ApplicantAccount
    {
        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'PEMOHON DEVICE';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make('PasswordAman#123');
        $account->email_verified_at = now();
        $account->account_status = ApplicantAccount::STATUS_ACTIVE;
        $account->identity_status = ApplicantAccount::IDENTITY_VERIFIED;
        $account->save();

        return $account;
    }

    /**
     * Test 12: Login pertama mengikat browser.
     */
    public function test_first_login_binds_browser_with_persistent_cookie(): void
    {
        $account = $this->createVerifiedAccount('5205011205950012', 'device.test12@gmail.com');

        $response = $this->post(route('applicant.login.post'), [
            'nik'      => '5205011205950012',
            'password' => 'PasswordAman#123',
        ]);

        $response->assertRedirect(route('applicant.dashboard'));
        $response->assertCookie(ApplicantDeviceService::COOKIE_NAME);

        $activeDevice = $account->activeDevice;
        $this->assertNotNull($activeDevice);
        $this->assertTrue($activeDevice->isActive());

        // Ensure token in cookie matches hash in database
        $cookieToken = $response->getCookie(ApplicantDeviceService::COOKIE_NAME)->getValue();
        $this->assertEquals(hash('sha256', $cookieToken), $activeDevice->device_token_hash);
    }

    /**
     * Test 13: Browser yang sama dapat melanjutkan sesi setelah ditutup dan dibuka kembali.
     */
    public function test_same_browser_can_resume_session_with_persistent_cookie(): void
    {
        $account = $this->createVerifiedAccount('5205011205950013', 'device.test13@gmail.com');

        $rawToken = bin2hex(random_bytes(32));
        $account->devices()->create([
            'device_token_hash' => hash('sha256', $rawToken),
            'device_label'      => 'Test Browser',
            'registered_at'     => now(),
            'last_seen_at'      => now(),
        ]);

        // Access dashboard without pre-existing PHP session (simulating browser reopen)
        $response = $this->withCookie(ApplicantDeviceService::COOKIE_NAME, $rawToken)
            ->get(route('applicant.dashboard'));

        $response->assertOk();
        $this->assertAuthenticatedAs($account, 'applicant');
    }

    /**
     * Test 14: Perangkat kedua ditolak saat perangkat pertama masih aktif.
     */
    public function test_second_device_is_rejected_while_first_device_is_active(): void
    {
        $account = $this->createVerifiedAccount('5205011205950014', 'device.test14@gmail.com');

        // Browser 1 binds device
        $token1 = bin2hex(random_bytes(32));
        $account->devices()->create([
            'device_token_hash' => hash('sha256', $token1),
            'device_label'      => 'Browser 1 di Android',
            'registered_at'     => now(),
            'last_seen_at'      => now(),
        ]);

        // Browser 2 attempts to login without matching cookie
        $response = $this->post(route('applicant.login.post'), [
            'nik'      => '5205011205950014',
            'password' => 'PasswordAman#123',
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertGuest('applicant');

        // Still only 1 active device in database
        $this->assertEquals(1, $account->devices()->whereNull('revoked_at')->count());
    }

    /**
     * Test 15: Dua login bersamaan tidak menghasilkan dua perangkat aktif (Concurrency / Race Condition Test).
     */
    public function test_concurrent_logins_never_result_in_two_active_devices(): void
    {
        $account = $this->createVerifiedAccount('5205011205950015', 'device.test15@gmail.com');
        $service = app(ApplicantDeviceService::class);

        $request1 = Request::create(route('applicant.login.post'), 'POST', [
            'nik'      => '5205011205950015',
            'password' => 'PasswordAman#123',
        ]);
        $request1->headers->set('User-Agent', 'Browser A');

        $request2 = Request::create(route('applicant.login.post'), 'POST', [
            'nik'      => '5205011205950015',
            'password' => 'PasswordAman#123',
        ]);
        $request2->headers->set('User-Agent', 'Browser B');

        // Bind first device
        [$device1] = $service->bindOrValidateOnLogin($account, $request1);
        $this->assertNotNull($device1);

        // Immediate second attempt from another browser must throw ValidationException
        $exceptionThrown = false;
        try {
            $service->bindOrValidateOnLogin($account, $request2);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
        $this->assertEquals(1, ApplicantDevice::where('applicant_account_id', $account->id)->whereNull('revoked_at')->count());
    }

    /**
     * Test 16: Cookie perangkat yang tidak valid ditolak.
     */
    public function test_invalid_device_cookie_is_rejected(): void
    {
        $account = $this->createVerifiedAccount('5205011205950016', 'device.test16@gmail.com');

        $fakeToken = bin2hex(random_bytes(32));

        $response = $this->actingAs($account, 'applicant')
            ->withCookie(ApplicantDeviceService::COOKIE_NAME, $fakeToken)
            ->get(route('applicant.dashboard'));

        // Middleware logs out applicant and redirects to login
        $response->assertRedirect(route('applicant.login'));
        $this->assertGuest('applicant');
    }

    /**
     * Test 17: Cookie yang dihapus / hilang mengarahkan pengguna ke login.
     */
    public function test_missing_device_cookie_redirects_to_login(): void
    {
        $account = $this->createVerifiedAccount('5205011205950017', 'device.test17@gmail.com');

        $response = $this->actingAs($account, 'applicant')
            ->get(route('applicant.dashboard'));

        $response->assertRedirect(route('applicant.login'));
    }

    /**
     * Test 18: Pencabutan perangkat oleh admin langsung menghentikan akses pemohon.
     */
    public function test_device_revocation_by_admin_instantly_terminates_access(): void
    {
        $account = $this->createVerifiedAccount('5205011205950018', 'device.test18@gmail.com');
        $deviceService = app(ApplicantDeviceService::class);

        $token = bin2hex(random_bytes(32));
        $device = $account->devices()->create([
            'device_token_hash' => hash('sha256', $token),
            'device_label'      => 'HP Pemohon',
            'registered_at'     => now(),
            'last_seen_at'      => now(),
        ]);

        // Revoke device as admin
        $deviceService->revokeActiveDevice($account, 'Dugaan kebocoran akun', 'admin', 1);

        // Next request from applicant using revoked device must be rejected
        $response = $this->actingAs($account, 'applicant')
            ->withCookie(ApplicantDeviceService::COOKIE_NAME, $token)
            ->get(route('applicant.dashboard'));

        $response->assertRedirect(route('applicant.login'));
        $this->assertGuest('applicant');
    }

    /**
     * Test 19 & 20: Pemulihan akun menghasilkan perangkat baru dan mencabut perangkat lama.
     */
    public function test_account_recovery_revokes_old_device_and_binds_new_device(): void
    {
        $account = $this->createVerifiedAccount('5205011205950020', 'device.test20@gmail.com');

        // Initial device on old phone
        $oldToken = bin2hex(random_bytes(32));
        $oldDevice = $account->devices()->create([
            'device_token_hash' => hash('sha256', $oldToken),
            'device_label'      => 'HP Lama (Hilang)',
            'registered_at'     => now()->subDays(10),
            'last_seen_at'      => now()->subDays(2),
        ]);

        $otpService = app(ApplicantOtpService::class);
        $deviceService = app(ApplicantDeviceService::class);

        // Step 1: Request recovery OTP
        $this->post(route('applicant.recover.send-otp'), [
            'nik'      => '5205011205950020',
            'password' => 'PasswordAman#123',
        ])->assertSessionHas('otpSent');

        $otpRecord = ApplicantEmailOtp::where('email_normalized', 'device.test20@gmail.com')
            ->where('purpose', 'device_recovery')
            ->latest()
            ->first();

        // Simulate OTP verification and recovery
        $plainOtp = '123456';
        $otpRecord->update(['otp_hash' => Hash::make($plainOtp)]);

        $response = $this->withSession([
            'applicant_recovery_id'    => $account->id,
            'applicant_recovery_email' => $account->email_normalized,
        ])->post(route('applicant.recover.complete'), [
            'otp' => $plainOtp,
        ]);

        $response->assertRedirect(route('applicant.dashboard'));
        $response->assertCookie(ApplicantDeviceService::COOKIE_NAME);

        // Check database: old device revoked, new device active
        $oldDevice->refresh();
        $this->assertNotNull($oldDevice->revoked_at);

        $newDevice = $account->activeDevice;
        $this->assertNotNull($newDevice);
        $this->assertNotEquals($oldDevice->id, $newDevice->id);
        $this->assertTrue($newDevice->isActive());
    }
}
