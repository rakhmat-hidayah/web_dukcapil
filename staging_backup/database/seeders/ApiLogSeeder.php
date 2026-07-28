<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiLogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure API Keys exist
        $this->call(ApiSettingSeeder::class);

        // Additional API Keys for realistic client distribution
        $additionalKeys = [
            [
                'client_name' => 'Aplikasi SANAI Mobile',
                'api_key' => 'dkp_sanai_mobile_app_token_2026',
                'rate_limit_per_hour' => 5000,
                'is_active' => true,
                'permissions' => json_encode(['demographics', 'survey', 'downloads']),
            ],
            [
                'client_name' => 'Portal Satu Data Dompu',
                'api_key' => 'dkp_satudata_dompukab_token_2026',
                'rate_limit_per_hour' => 3000,
                'is_active' => true,
                'permissions' => json_encode(['demographics']),
            ]
        ];

        foreach ($additionalKeys as $keyData) {
            DB::table('api_keys')->updateOrInsert(
                ['client_name' => $keyData['client_name']],
                array_merge($keyData, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. Fetch created API keys
        $apiKeys = ApiKey::all();
        $apiKeyIds = $apiKeys->pluck('id')->toArray();

        // 3. Clear old api_logs
        DB::table('api_logs')->truncate();

        // 4. Sample endpoints & HTTP methods
        $endpoints = [
            ['path' => 'api/v1/demographics/summary', 'method' => 'GET', 'weight' => 35],
            ['path' => 'api/v1/demographics/kecamatans', 'method' => 'GET', 'weight' => 20],
            ['path' => 'api/v1/news', 'method' => 'GET', 'weight' => 15],
            ['path' => 'api/v1/downloads', 'method' => 'GET', 'weight' => 10],
            ['path' => 'api/v1/survey/current', 'method' => 'GET', 'weight' => 10],
            ['path' => 'api/v1/test-auth', 'method' => 'POST', 'weight' => 5],
            ['path' => 'api/v1/pages/visi-misi', 'method' => 'GET', 'weight' => 5],
        ];

        $ips = ['180.252.12.44', '114.122.34.19', '36.88.201.5', '103.247.218.12', '127.0.0.1'];

        // 5. Generate ~350 realistic logs spread over the last 24 hours
        $logs = [];
        $now = time();

        for ($i = 0; $i < 350; $i++) {
            // Random timestamp within the last 24 hours
            $minutesAgo = rand(0, 1439);
            $createdAt = date('Y-m-d H:i:s', $now - ($minutesAgo * 60));

            // Select random endpoint based on weights
            $ep = $endpoints[array_rand($endpoints)];

            // Select random client key (or null for public)
            $keyId = (rand(1, 10) > 2 && !empty($apiKeyIds)) ? $apiKeyIds[array_rand($apiKeyIds)] : null;

            // Random status code (mostly 200, occasional 400, 404, 429)
            $randCode = rand(1, 100);
            if ($randCode <= 88) {
                $statusCode = 200;
            } elseif ($randCode <= 94) {
                $statusCode = 404;
            } elseif ($randCode <= 97) {
                $statusCode = 429; // rate limit violation
            } else {
                $statusCode = 500; // server error
            }

            // Duration ms
            $duration = rand(15, 145);

            $logs[] = [
                'api_key_id' => $keyId,
                'endpoint' => $ep['path'],
                'method' => $ep['method'],
                'ip_address' => $ips[array_rand($ips)],
                'response_code' => $statusCode,
                'duration_ms' => $duration,
                'created_at' => $createdAt,
            ];
        }

        // Chunk insert
        foreach (array_chunk($logs, 100) as $chunk) {
            DB::table('api_logs')->insert($chunk);
        }
    }
}
