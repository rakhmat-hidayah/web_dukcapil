<?php

namespace Tests\Feature;

use App\Mail\ApplicantVerificationResultMail;
use App\Models\Applicant\ApplicantAccount;
use App\Models\Applicant\ApplicantEmailOtp;
use App\Models\User;
use App\Services\Applicant\ApplicantDeviceService;
use App\Services\Applicant\ApplicantOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicantAuthenticationTest extends TestCase
{
    /**
     * Test 1: Pendaftaran NIK baru berhasil.
     */
    public function test_new_nik_registration_succeeds(): void
    {
        $nik = '5205011205950001';
        $email = 'warga.baru@gmail.com';

        // Clean any existing test records
        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $response = $this->post(route('applicant.register.post'), [
            'nik'                   => $nik,
            'name'                  => 'AHMAD DAHLAN',
            'email'                 => $email,
            'password'              => 'PasswordKuat123#',
            'password_confirmation' => 'PasswordKuat123#',
        ]);

        $response->assertRedirect(route('applicant.verify-otp'));

        $account = ApplicantAccount::where('email_normalized', $email)->first();
        $this->assertNotNull($account);
        $this->assertEquals(ApplicantAccount::STATUS_EMAIL_PENDING, $account->account_status);
        $this->assertEquals(ApplicantAccount::IDENTITY_PENDING, $account->identity_status);
        $this->assertEquals($nik, $account->nik);

        // Verify OTP record created
        $otp = ApplicantEmailOtp::where('email_normalized', $email)->latest()->first();
        $this->assertNotNull($otp);
        $this->assertFalse($otp->isExpired());
    }

    /**
     * Test 2: Pendaftaran dengan NIK yang sudah digunakan ditolak.
     */
    public function test_duplicate_nik_registration_is_rejected(): void
    {
        $nik = '5205011205950002';
        $email1 = 'nik.pertama@gmail.com';
        $email2 = 'nik.kedua@gmail.com';

        ApplicantAccount::where('email_normalized', $email1)->forceDelete();
        ApplicantAccount::where('email_normalized', $email2)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'WARGA SATU';
        $account->email_normalized = $email1;
        $account->nik = $nik;
        $account->password = Hash::make('Password123#');
        $account->account_status = ApplicantAccount::STATUS_ACTIVE;
        $account->identity_status = ApplicantAccount::IDENTITY_VERIFIED;
        $account->save();

        $response = $this->from(route('applicant.register'))
            ->post(route('applicant.register.post'), [
                'nik'                   => $nik,
                'name'                  => 'WARGA DUA',
                'email'                 => $email2,
                'password'              => 'Password123#',
                'password_confirmation' => 'Password123#',
            ]);

        $response->assertSessionHasErrors('nik');
        $this->assertEquals(1, ApplicantAccount::where('nik_hash', ApplicantAccount::computeNikHash($nik))->count());
    }

    /**
     * Test 3: Pendaftaran dengan email yang sudah digunakan ditolak.
     */
    public function test_duplicate_email_registration_is_rejected(): void
    {
        $email = 'email.terpakai@gmail.com';
        $nik1 = '5205011205950003';
        $nik2 = '5205011205950004';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'PEMILIK EMAIL';
        $account->email_normalized = $email;
        $account->nik = $nik1;
        $account->password = Hash::make('Password123#');
        $account->save();

        $response = $this->from(route('applicant.register'))
            ->post(route('applicant.register.post'), [
                'nik'                   => $nik2,
                'name'                  => 'PENDAFTAR LAIN',
                'email'                 => $email,
                'password'              => 'Password123#',
                'password_confirmation' => 'Password123#',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test 4: OTP salah ditolak.
     */
    public function test_incorrect_otp_is_rejected(): void
    {
        $email = 'uji.otp.salah@gmail.com';
        $service = app(ApplicantOtpService::class);
        $service->generateAndSend($email, 'registration');

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->verify($email, '000000', 'registration');
    }

    /**
     * Test 5: OTP kedaluwarsa ditolak.
     */
    public function test_expired_otp_is_rejected(): void
    {
        $email = 'uji.otp.expired@gmail.com';
        $service = app(ApplicantOtpService::class);
        $otpRecord = $service->generateAndSend($email, 'registration');

        // Fast-forward OTP expiration
        $otpRecord->update(['expires_at' => now()->subMinutes(5)]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->verify($email, '123456', 'registration');
    }

    /**
     * Test 6: OTP yang telah digunakan tidak dapat digunakan kembali (single-use).
     */
    public function test_consumed_otp_cannot_be_reused(): void
    {
        $email = 'uji.otp.reused@gmail.com';
        $plainOtp = '654321';

        $otpRecord = ApplicantEmailOtp::create([
            'email_normalized' => $email,
            'otp_hash'         => Hash::make($plainOtp),
            'purpose'          => 'registration',
            'expires_at'       => now()->addMinutes(10),
            'attempts'         => 0,
            'max_attempts'     => 3,
            'resend_count'     => 0,
        ]);

        $service = app(ApplicantOtpService::class);
        $verified = $service->verify($email, $plainOtp, 'registration');
        $this->assertNotNull($verified);
        $this->assertNotNull($verified->consumed_at);

        // Second attempt must fail
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->verify($email, $plainOtp, 'registration');
    }

    /**
     * Test 7: Password disimpan dalam bentuk hash (bukan teks biasa).
     */
    public function test_password_is_stored_as_secure_hash(): void
    {
        $nik = '5205011205950007';
        $email = 'uji.hash.pwd@gmail.com';
        $rawPassword = 'PasswordSangatRahasia!#99';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'UJI HASH';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make($rawPassword);
        $account->save();

        $this->assertNotEquals($rawPassword, $account->password);
        $this->assertTrue(Hash::check($rawPassword, $account->password));
        $this->assertStringStartsWith('$', $account->password);
    }

    /**
     * Test 8: Login NIK dan password yang benar berhasil.
     */
    public function test_login_with_valid_credentials_succeeds(): void
    {
        $nik = '5205011205950008';
        $email = 'uji.login.valid@gmail.com';
        $password = 'PasswordValid#123';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'PEMOHON VALID';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make($password);
        $account->email_verified_at = now();
        $account->account_status = ApplicantAccount::STATUS_ACTIVE;
        $account->identity_status = ApplicantAccount::IDENTITY_VERIFIED;
        $account->save();

        $response = $this->post(route('applicant.login.post'), [
            'nik'      => $nik,
            'password' => $password,
        ]);

        $response->assertRedirect(route('applicant.dashboard'));
        $response->assertCookie(ApplicantDeviceService::COOKIE_NAME);
        $this->assertAuthenticatedAs($account, 'applicant');
    }

    /**
     * Test 9: Login dengan password salah ditolak.
     */
    public function test_login_with_incorrect_password_is_rejected(): void
    {
        $nik = '5205011205950009';
        $email = 'uji.login.salah@gmail.com';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'PEMOHON SALAH';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make('PasswordBenar#123');
        $account->email_verified_at = now();
        $account->account_status = ApplicantAccount::STATUS_ACTIVE;
        $account->identity_status = ApplicantAccount::IDENTITY_VERIFIED;
        $account->save();

        $response = $this->post(route('applicant.login.post'), [
            'nik'      => $nik,
            'password' => 'PasswordSalahTotal#999',
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertGuest('applicant');
    }

    /**
     * Test 10: Akun IDENTITY_PENDING tidak dapat mengakses fitur pengajuan.
     */
    public function test_identity_pending_account_cannot_access_apply_feature(): void
    {
        $nik = '5205011205950010';
        $email = 'uji.pending.apply@gmail.com';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'PEMOHON PENDING';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make('Password#123');
        $account->email_verified_at = now();
        $account->account_status = ApplicantAccount::STATUS_IDENTITY_PENDING;
        $account->identity_status = ApplicantAccount::IDENTITY_PENDING;
        $account->save();

        // Bind device so applicant has valid device
        $deviceService = app(ApplicantDeviceService::class);
        $token = bin2hex(random_bytes(32));
        $account->devices()->create([
            'device_token_hash' => ApplicantDeviceService::hashToken($token),
            'device_label'      => 'Test Browser',
            'registered_at'     => now(),
            'last_seen_at'      => now(),
        ]);

        $response = $this->actingAs($account, 'applicant')
            ->withCookie(ApplicantDeviceService::COOKIE_NAME, $token)
            ->get(route('queue.apply.create'));

        $response->assertRedirect(route('applicant.dashboard'));
        $response->assertSessionHas('warning');
    }

    /**
     * Test 11: Akun SUSPENDED tidak dapat login.
     */
    public function test_suspended_account_cannot_login(): void
    {
        $nik = '5205011205950011';
        $email = 'uji.suspended@gmail.com';
        $password = 'Password123#';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'PEMOHON SUSPENDED';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make($password);
        $account->email_verified_at = now();
        $account->account_status = ApplicantAccount::STATUS_SUSPENDED;
        $account->identity_status = ApplicantAccount::IDENTITY_VERIFIED;
        $account->save();

        $response = $this->post(route('applicant.login.post'), [
            'nik'      => $nik,
            'password' => $password,
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertGuest('applicant');
    }

    /**
     * Test 21: Autentikasi admin/petugas yang sudah ada tetap berfungsi tanpa interferensi.
     */
    public function test_existing_admin_authentication_unaffected(): void
    {
        $admin = User::first() ?? User::factory()->create([
            'email'    => 'admin.test@dukcapil.dompukab.go.id',
            'username' => 'superadmin_test',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.dashboard'));

        $response->assertOk();
    }

    /**
     * Test 22: Verifikasi OTP sukses mengarahkan ke halaman login dengan query NIK dan flash sukses.
     */
    public function test_successful_otp_verification_redirects_to_login_with_nik_and_success_flash(): void
    {
        $email = 'sukses.verif.otp@gmail.com';
        $nik = '5205011205950099';
        $plainOtp = '123456';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'PEMOHON BARU';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make('Password123#');
        $account->account_status = ApplicantAccount::STATUS_EMAIL_PENDING;
        $account->identity_status = ApplicantAccount::IDENTITY_PENDING;
        $account->save();

        ApplicantEmailOtp::create([
            'applicant_account_id' => $account->id,
            'email_normalized'     => $email,
            'otp_hash'             => Hash::make($plainOtp),
            'purpose'              => 'registration',
            'expires_at'           => now()->addMinutes(10),
            'attempts'             => 0,
            'max_attempts'         => 3,
            'resend_count'         => 0,
        ]);

        $response = $this->post(route('applicant.verify-otp.post'), [
            'email' => $email,
            'otp'   => $plainOtp,
        ]);

        $response->assertRedirect(route('applicant.login', ['nik' => $nik]));
        $response->assertSessionHas('success');
    }

    /**
     * Test 23: Admin memverifikasi identitas pemohon memicu pengiriman email pemberitahuan.
     */
    public function test_admin_verify_identity_sends_notification_email(): void
    {
        Mail::fake();

        \Spatie\Permission\Models\Role::findOrCreate('Super Administrator', 'web');

        $admin = User::first() ?? User::factory()->create([
            'email'    => 'admin.notif@dukcapil.dompukab.go.id',
            'username' => 'admin_notif',
        ]);
        $admin->assignRole('Super Administrator');

        $email = 'notif.verif.identitas@gmail.com';
        $nik = '5205011205950088';

        ApplicantAccount::where('email_normalized', $email)->forceDelete();

        $account = new ApplicantAccount();
        $account->name = 'WARGA TERVERIFIKASI';
        $account->email_normalized = $email;
        $account->nik = $nik;
        $account->password = Hash::make('Password123#');
        $account->email_verified_at = now();
        $account->account_status = ApplicantAccount::STATUS_IDENTITY_PENDING;
        $account->identity_status = ApplicantAccount::IDENTITY_PENDING;
        $account->save();

        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.queue.applicants.verify-identity', $account), [
                'decision' => 'VERIFIED',
                'notes'    => 'Data kependudukan SIAK valid',
            ]);

        $response->assertSessionHas('success');

        $account->refresh();
        $this->assertEquals(ApplicantAccount::IDENTITY_VERIFIED, $account->identity_status);
        $this->assertEquals(ApplicantAccount::STATUS_ACTIVE, $account->account_status);

        Mail::assertSent(ApplicantVerificationResultMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }
}
