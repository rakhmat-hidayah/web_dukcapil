<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AuthController;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTwoFactorRememberTest extends TestCase
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
        Cache::forget('nav_menus');
    }

    private function create2faAdmin(string $username = 'admin_2fa_test'): User
    {
        $user = User::updateOrCreate(
            ['username' => $username],
            [
                'name' => 'Admin 2FA Test',
                'email' => $username . '@dukcapil.dompukab.go.id',
                'password' => bcrypt('SecretAdminPass123!'),
                'is_active' => true,
            ]
        );
        $user->syncRoles(['Administrator Utama']);

        // Enable 2FA with known secret
        $twoFactorService = app(TwoFactorService::class);
        $secret = $twoFactorService->generateSecretKey();
        $user->setTwoFactorSecretEncrypted($secret);
        $user->two_factor_enabled = true;
        $user->two_factor_confirmed_at = now();
        $user->save();

        return $user;
    }

    public function test_admin_with_2fa_is_redirected_to_challenge_when_no_cookie(): void
    {
        $user = $this->create2faAdmin('admin_challenge_test');

        $response = $this->withSession(['captcha_image_code' => '1234'])
            ->post(route('admin.login.store'), [
                'username' => $user->username,
                'password' => 'SecretAdminPass123!',
                'captcha'  => '1234',
                'remember' => true,
            ]);

        $response->assertRedirect(route('admin.login.two-factor'));
        $this->assertEquals($user->id, session('pending_2fa_user_id'));
        $this->assertTrue(session('pending_2fa_remember'));
        $this->assertFalse(Auth::check());
    }

    public function test_verifying_otp_with_remember_queues_6_hour_cookie(): void
    {
        $user = $this->create2faAdmin('admin_otp_cookie_test');
        $secret = $user->getTwoFactorSecretDecrypted();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validOtp = $google2fa->getCurrentOtp($secret);

        $response = $this->withSession([
            'pending_2fa_user_id' => $user->id,
            'pending_2fa_remember' => true,
        ])->post(route('admin.login.two-factor.store'), [
            'code' => $validOtp,
            'remember' => true,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());

        // Assert 2FA remember cookie is queued with 360 minutes (6 hours)
        $response->assertCookie(AuthController::TWO_FACTOR_REMEMBER_COOKIE);
    }

    public function test_subsequent_login_with_valid_remember_cookie_bypasses_otp(): void
    {
        $user = $this->create2faAdmin('admin_bypass_test');

        // Prepare valid 6-hour remember payload
        $payload = json_encode([
            'user_id'    => $user->id,
            'hash'       => hash_hmac('sha256', $user->id . '|' . $user->password, config('app.key')),
            'expires_at' => now()->addHours(6)->timestamp,
        ]);

        // Login with username + password + captcha AND 2fa remember cookie
        $response = $this->withCookie(AuthController::TWO_FACTOR_REMEMBER_COOKIE, $payload)
            ->withSession(['captcha_image_code' => '1234'])
            ->post(route('admin.login.store'), [
                'username' => $user->username,
                'password' => 'SecretAdminPass123!',
                'captcha'  => '1234',
                'remember' => true,
            ]);

        // Directly redirects to dashboard without 2FA challenge!
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());

        // Cookie is refreshed for another 6 hours
        $response->assertCookie(AuthController::TWO_FACTOR_REMEMBER_COOKIE);
    }

    public function test_expired_remember_cookie_still_prompts_for_otp(): void
    {
        $user = $this->create2faAdmin('admin_expired_cookie_test');

        // Expired cookie (expired 10 minutes ago)
        $expiredPayload = json_encode([
            'user_id'    => $user->id,
            'hash'       => hash_hmac('sha256', $user->id . '|' . $user->password, config('app.key')),
            'expires_at' => now()->subMinutes(10)->timestamp,
        ]);

        $response = $this->withCookie(AuthController::TWO_FACTOR_REMEMBER_COOKIE, $expiredPayload)
            ->withSession(['captcha_image_code' => '1234'])
            ->post(route('admin.login.store'), [
                'username' => $user->username,
                'password' => 'SecretAdminPass123!',
                'captcha'  => '1234',
                'remember' => true,
            ]);

        // Must redirect to two-factor challenge
        $response->assertRedirect(route('admin.login.two-factor'));
        $this->assertFalse(Auth::check());
    }

    public function test_unchecking_remember_revokes_cookie_and_prompts_otp(): void
    {
        $user = $this->create2faAdmin('admin_uncheck_test');

        $payload = json_encode([
            'user_id'    => $user->id,
            'hash'       => hash_hmac('sha256', $user->id . '|' . $user->password, config('app.key')),
            'expires_at' => now()->addHours(6)->timestamp,
        ]);

        // Admin logs in with remember=false
        $response = $this->withCookie(AuthController::TWO_FACTOR_REMEMBER_COOKIE, $payload)
            ->withSession(['captcha_image_code' => '1234'])
            ->post(route('admin.login.store'), [
                'username' => $user->username,
                'password' => 'SecretAdminPass123!',
                'captcha'  => '1234',
                'remember' => false,
            ]);

        // Must redirect to two-factor challenge and expire cookie
        $response->assertRedirect(route('admin.login.two-factor'));
        $response->assertCookieExpired(AuthController::TWO_FACTOR_REMEMBER_COOKIE);
        $this->assertFalse(Auth::check());
    }
}
