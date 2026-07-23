<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\ApiSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the API settings and a test key
        ApiSetting::insert([
            ['key' => 'api_rate_limit_public', 'value' => '100', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'api_rate_limit_partner', 'value' => '1000', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'api_terms_of_service', 'value' => '# Ketentuan Layanan\nTest terms.', 'created_at' => now(), 'updated_at' => now()],
        ]);

        ApiKey::create([
            'client_name' => 'Test Client',
            'api_key' => 'dkp_test_key_123456789',
            'rate_limit_per_hour' => 1000,
            'is_active' => true,
            'permissions' => ['*'],
        ]);
    }

    /** @test */
    public function test_public_terms_endpoint_returns_200(): void
    {
        $response = $this->getJson('/api/v1/terms');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ketentuan layanan berhasil diambil',
            ])
            ->assertJsonPath('data.terms', fn ($terms) => str_contains($terms, 'Ketentuan Layanan'));
    }

    /** @test */
    public function test_protected_endpoint_returns_401_without_key(): void
    {
        $response = $this->getJson('/api/v1/test-auth');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_protected_endpoint_returns_401_with_invalid_key(): void
    {
        $response = $this->getJson('/api/v1/test-auth', [
            'X-API-KEY' => 'dkp_invalid_key_that_does_not_exist',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_protected_endpoint_returns_200_with_valid_key(): void
    {
        $response = $this->getJson('/api/v1/test-auth', [
            'X-API-KEY' => 'dkp_test_key_123456789',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Otentikasi API Key Berhasil',
            ])
            ->assertJsonPath('data.client', 'Test Client');
    }

    /** @test */
    public function test_protected_endpoint_returns_401_with_deactivated_key(): void
    {
        ApiKey::where('api_key', 'dkp_test_key_123456789')->update(['is_active' => false]);

        $response = $this->getJson('/api/v1/test-auth', [
            'X-API-KEY' => 'dkp_test_key_123456789',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function test_standard_response_structure_is_correct(): void
    {
        $response = $this->getJson('/api/v1/terms');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta',
                'links',
            ]);
    }
}
