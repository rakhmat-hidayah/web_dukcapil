<?php

namespace Tests\Feature;

use App\Models\Applicant\ApplicantAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicantUnregisteredNoticeTest extends TestCase
{
    use RefreshDatabase;

    protected array $mobileHeaders = [
        'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
    ];

    public function test_recovery_with_unregistered_nik_shows_not_registered_message(): void
    {
        $unregisteredNik = '5205022804040001';
        $email = 'rangenaresti68@gmail.com';

        $response = $this->withHeaders($this->mobileHeaders)->post(route('applicant.recover.send-otp'), [
            'nik'   => $unregisteredNik,
            'email' => $email,
        ]);

        $response->assertSessionHasErrors(['nik']);
        $errors = session('errors')->get('nik');
        $this->assertStringContainsString('belum terdaftar', $errors[0]);
    }

    public function test_recovery_with_registered_nik_but_mismatched_email_shows_email_mismatch_message(): void
    {
        $registeredNik = '5205022804040002';
        $correctEmail = 'benar@gmail.com';
        $wrongEmail = 'salah@gmail.com';

        $account = new ApplicantAccount();
        $account->name = 'WARGA TEST';
        $account->email_normalized = $correctEmail;
        $account->phone = '081234567890';
        $account->nik = $registeredNik;
        $account->password = Hash::make('Password#123');
        $account->account_status = ApplicantAccount::STATUS_ACTIVE;
        $account->identity_status = ApplicantAccount::IDENTITY_VERIFIED;
        $account->save();

        $response = $this->withHeaders($this->mobileHeaders)->post(route('applicant.recover.send-otp'), [
            'nik'   => $registeredNik,
            'email' => $wrongEmail,
        ]);

        $response->assertSessionHasErrors(['email']);
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('tidak sesuai', $errors[0]);
    }

    public function test_login_with_unregistered_nik_shows_not_registered_message(): void
    {
        $unregisteredNik = '5205022804040001';

        $response = $this->withHeaders($this->mobileHeaders)->post(route('applicant.login.post'), [
            'nik'      => $unregisteredNik,
            'password' => 'Password#123',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $errors = session('errors')->get('nik');
        $this->assertStringContainsString('belum terdaftar', $errors[0]);
    }
}
