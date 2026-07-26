<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HttpResponse;
use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[OA\Info(
    version: '1.0.0',
    title: 'Dukcapil Dompu REST API',
    description: 'REST API untuk layanan Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Menyediakan endpoint untuk data profil, survei, dan layanan publik.',
    contact: new OA\Contact(
        name: 'Tim IT Dukcapil Dompu',
        email: 'admin@dukcapil.dompukab.go.id'
    ),
    license: new OA\License(
        name: 'Apache 2.0',
        url: 'https://www.apache.org/licenses/LICENSE-2.0.html'
    )
)]
#[OA\Server(
    url: '/api',
    description: 'API Server Utama'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Masukkan token API Anda di format: Bearer {token}'
)]
class DocController extends Controller
{
    /**
     * Render the Swagger UI page.
     */
    public function docs(): HttpResponse
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dukcapil Dompu - API Dokumentasi</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css" />
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/favicon-32x32.png" sizes="32x32" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; }
        .swagger-ui .topbar { display: none; }
        .header-brand {
            background: linear-gradient(135deg, #035ca3 0%, #0e91eb 100%);
            padding: 14px 30px;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(3,92,163,0.3);
        }
        .header-brand .badge {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .header-brand a {
            color: #facc15;
            font-size: 11px;
            text-decoration: none;
            border: 1px solid #facc15;
            padding: 5px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .header-brand a:hover { background: #facc15; color: #035ca3; }
    </style>
</head>
<body>
    <div class="header-brand">
        <div style="display:flex;align-items:center;gap:12px;">
            <span>Dukcapil Dompu REST API</span>
            <span class="badge">OpenAPI 3.1</span>
        </div>
        <a href="/api/docs/json" target="_blank">⬇ Unduh OpenAPI.json</a>
    </div>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/api/docs/json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                tryItOutEnabled: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Generate and output the OpenAPI 3.1 JSON specification.
     * Uses swagger-php v6 Generator with PHP 8 Attributes.
     */
    public function json(): HttpResponse
    {
        // swagger-php v6 natively reads PHP 8 #[OA\*] Attributes
        // All V1 controllers have been converted to Attributes format
        $openapi = (new Generator())->generate(
            sources: [app_path('Http/Controllers/Api')],
            validate: false
        );

        return response($openapi->toJson(), 200, [
            'Content-Type'                => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
