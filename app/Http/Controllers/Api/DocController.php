<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Generator;

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
    <title>Dukcapil Dompu - API Dokumentasi</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css" />
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/favicon-32x32.png" sizes="32x32" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin:0; background: #fafafa; }
        .swagger-ui .topbar { display: none; } /* Hide default topbar */
        .header-brand { background: #035ca3; padding: 15px 30px; color: white; font-family: sans-serif; font-weight: bold; font-size: 16px; display: flex; align-items: center; justify-content: space-between; }
        .header-brand a { color: #facc15; font-size: 11px; text-decoration: none; border: 1px solid #facc15; padding: 4px 8px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header-brand">
        <span>Dukcapil Dompu REST API - OpenAPI 3.1</span>
        <a href="/api/docs/json" target="_blank">Unduh OpenAPI.json</a>
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
     */
    public function json(): HttpResponse
    {
        // Scan all API and CMS controllers to generate the documentation
        $openapi = Generator::scan([
            app_path('Http/Controllers/Api'),
            app_path('Http/Controllers/Admin'), // include CMS keys endpoint details
        ]);

        return response($openapi->toJson(), 200, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
