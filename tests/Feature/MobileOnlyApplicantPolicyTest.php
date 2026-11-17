<?php

namespace Tests\Feature;

use App\Models\Applicant\ApplicantAccount;
use App\Models\User;
use App\Services\Applicant\ApplicantDeviceDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileOnlyApplicantPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected string $desktopUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    protected string $macDesktopUa = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';
    protected string $androidPhoneUa = 'Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.144 Mobile Safari/537.36';
    protected string $iphoneUa = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
    protected string $ipadTabletUa = 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
    protected string $curlBotUa = 'curl/7.88.1';

    /**
     * Skenario A1: Desktop PC/Laptop can browse homepage and public services info.
     */
    public function test_desktop_can_browse_homepage_and_services(): void
    {
        $responseHome = $this->withHeaders(['User-Agent' => $this->desktopUa])->get('/');
        $responseHome->assertSuccessful();

        $responseServices = $this->withHeaders(['User-Agent' => $this->desktopUa])->get('/layanan');
        $responseServices->assertSuccessful();
    }

    /**
     * Skenario A2: Desktop can open login page, but receives device_policy indicating desktop restriction.
     */
    public function test_desktop_can_view_applicant_login_page_with_desktop_restriction_prop(): void
    {
        $response = $this->withHeaders(['User-Agent' => $this->desktopUa])
            ->get(route('applicant.login'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Applicant/Login')
            ->where('device_policy.is_mobile', false)
            ->where('device_policy.category', ApplicantDeviceDetector::CATEGORY_DESKTOP)
        );
    }

    /**
     * Skenario A3: Desktop is strictly forbidden from POST login (403 Forbidden).
     */
    public function test_desktop_cannot_submit_login(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => $this->desktopUa,
            'Accept'     => 'application/json',
        ])->post(route('applicant.login.post'), [
            'nik'      => '5205010101900001',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status'          => 'error',
            'code'            => 403,
            'device_category' => ApplicantDeviceDetector::CATEGORY_DESKTOP,
        ]);
    }

    /**
     * Skenario A4: Desktop is strictly forbidden from POST registration (403 Forbidden).
     */
    public function test_desktop_cannot_submit_registration(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => $this->desktopUa,
            'Accept'     => 'application/json',
        ])->post(route('applicant.register.post'), [
            'nik'                   => '5205010101900002',
            'name'                  => 'PEMOHON DESKTOP',
            'phone'                 => '081234567890',
            'email'                 => 'desktop@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Skenario A5: Desktop is forbidden from requesting recovery OTP (403 Forbidden).
     */
    public function test_desktop_cannot_request_recovery_otp(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => $this->desktopUa,
            'Accept'     => 'application/json',
        ])->post(route('applicant.recover.send-otp'), [
            'nik'   => '5205010101900001',
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Skenario A6: Desktop is redirected from accessing applicant dashboard.
     */
    public function test_desktop_cannot_access_applicant_dashboard(): void
    {
        $response = $this->withHeaders(['User-Agent' => $this->desktopUa])
            ->get(route('applicant.dashboard'));

        $response->assertRedirect(route('applicant.login'));
    }

    /**
     * Skenario A7: Desktop clicking queue registration (via Inertia navigation) redirects gracefully to login with restriction notice.
     */
    public function test_desktop_clicking_queue_apply_via_inertia_redirects_to_login_page(): void
    {
        $version = (new \App\Http\Middleware\HandleInertiaRequests)->version(request());

        $response = $this->withHeaders([
            'User-Agent'        => $this->desktopUa,
            'X-Inertia'         => 'true',
            'X-Inertia-Version' => $version,
        ])->get(route('queue.apply.create'));

        $response->assertRedirect(route('applicant.login'));
    }

    /**
     * Skenario B1: Smartphone opens login page with device_policy.is_mobile = true.
     */
    public function test_smartphone_can_view_applicant_login_page_as_mobile(): void
    {
        $response = $this->withHeaders(['User-Agent' => $this->androidPhoneUa])
            ->get(route('applicant.login'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Applicant/Login')
            ->where('device_policy.is_mobile', true)
            ->where('device_policy.category', ApplicantDeviceDetector::CATEGORY_SMARTPHONE)
        );
    }

    /**
     * Skenario B2: Smartphone using iPhone is recognized as mobile.
     */
    public function test_iphone_can_view_applicant_login_page_as_mobile(): void
    {
        $response = $this->withHeaders(['User-Agent' => $this->iphoneUa])
            ->get(route('applicant.login'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Public/Applicant/Login')
            ->where('device_policy.is_mobile', true)
            ->where('device_policy.category', ApplicantDeviceDetector::CATEGORY_SMARTPHONE)
        );
    }

    /**
     * Skenario B3: Smartphone POST login passes mobile check (not blocked by 403).
     */
    public function test_smartphone_login_passes_mobile_middleware(): void
    {
        // When smartphone sends login request with invalid credentials, it passes mobile policy
        // and returns validation error (302/422), NEVER 403 Forbidden.
        $response = $this->withHeaders([
            'User-Agent' => $this->androidPhoneUa,
        ])->post(route('applicant.login.post'), [
            'nik'      => '5205010101900001',
            'password' => 'wrong_password',
        ]);

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /**
     * Skenario C1: Tablet (iPad) is rejected from submitting login by default policy.
     */
    public function test_tablet_is_rejected_from_submitting_login(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => $this->ipadTabletUa,
            'Accept'     => 'application/json',
        ])->post(route('applicant.login.post'), [
            'nik'      => '5205010101900001',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status'          => 'error',
            'code'            => 403,
            'device_category' => ApplicantDeviceDetector::CATEGORY_TABLET,
        ]);
    }

    /**
     * Skenario C2: Automated tool / cURL / Bot is strictly rejected.
     */
    public function test_bot_or_automated_tool_is_rejected(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => $this->curlBotUa,
            'Accept'     => 'application/json',
        ])->post(route('applicant.login.post'), [
            'nik'      => '5205010101900001',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status'          => 'error',
            'code'            => 403,
            'device_category' => ApplicantDeviceDetector::CATEGORY_BOT_UNKNOWN,
        ]);
    }

    /**
     * Skenario C3: Empty User-Agent fails closed and is rejected.
     */
    public function test_empty_user_agent_fails_closed(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => '',
            'Accept'     => 'application/json',
        ])->post(route('applicant.login.post'), [
            'nik'      => '5205010101900001',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Skenario D: Administrator and Officer authentication via desktop is NOT affected.
     */
    public function test_admin_and_officer_can_still_login_and_access_via_desktop(): void
    {
        // Admin login page remains fully accessible on desktop PC
        $response = $this->withHeaders(['User-Agent' => $this->desktopUa])
            ->get(route('admin.login'));

        $response->assertSuccessful();

        // Admin login POST does NOT get blocked by applicant mobile policy
        $responseLogin = $this->withHeaders(['User-Agent' => $this->desktopUa])
            ->post(route('admin.login.store'), [
                'email'    => 'nonexistent@admin.com',
                'password' => 'wrongpass',
            ]);

        // Status must be 302 (redirect with validation error), NEVER 403 Forbidden
        $this->assertEquals(302, $responseLogin->getStatusCode());
    }
}
