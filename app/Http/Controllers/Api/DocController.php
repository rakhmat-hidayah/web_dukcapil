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
     * Render the Swagger UI page with custom brand theme.
     */
    public function docs(): HttpResponse
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dukcapil Dompu — REST API Dokumentasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css">
    <style>
        :root {
            --brand-900: #082a4a;
            --brand-700: #035ca3;
            --brand-500: #0e91eb;
            --brand-300: #7dd3fc;
            --brand-50:  #f0f9ff;
            --accent:    #facc15;
            --surface:   #f8fafc;
            --card:      #ffffff;
            --border:    #e2e8f0;
            --text:      #0f172a;
            --muted:     #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
        }

        /* ─── TOP NAVBAR ─── */
        .api-navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: linear-gradient(135deg, var(--brand-900) 0%, var(--brand-700) 60%, var(--brand-500) 100%);
            box-shadow: 0 4px 20px rgba(3,92,163,0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            height: 62px;
            gap: 16px;
        }

        .api-navbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .api-logo-icon {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            backdrop-filter: blur(8px);
        }

        .api-title {
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }
        .api-title small {
            display: block;
            font-size: 10px;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .api-navbar-badges {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .api-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .api-badge-version {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff;
        }
        .api-badge-oas {
            background: var(--accent);
            color: var(--brand-900);
        }

        .api-navbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .api-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .api-nav-link-outline {
            color: rgba(255,255,255,0.9);
            border: 1.5px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.07);
        }
        .api-nav-link-outline:hover {
            background: rgba(255,255,255,0.18);
            border-color: rgba(255,255,255,0.6);
            color: #fff;
        }
        .api-nav-link-accent {
            color: var(--brand-900);
            background: var(--accent);
            border: 1.5px solid var(--accent);
        }
        .api-nav-link-accent:hover {
            background: #fde68a;
            transform: translateY(-1px);
        }

        /* ─── INFO BANNER ─── */
        .api-info-bar {
            background: linear-gradient(135deg, var(--brand-50) 0%, #e0f2fe 100%);
            border-bottom: 1px solid #bae6fd;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .api-info-desc {
            font-size: 13px;
            color: var(--brand-700);
            font-weight: 500;
            max-width: 700px;
            line-height: 1.5;
        }

        .api-info-stats {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }

        .api-stat {
            text-align: center;
        }
        .api-stat-num {
            font-size: 20px;
            font-weight: 800;
            color: var(--brand-700);
            line-height: 1;
        }
        .api-stat-label {
            font-size: 10px;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-top: 2px;
        }

        .api-stat-divider {
            width: 1px;
            height: 30px;
            background: #93c5fd;
        }

        /* ─── MAIN CONTENT CONTAINER ─── */
        .api-content {
            max-width: 1140px;
            margin: 0 auto;
            padding: 24px 24px 60px;
        }

        /* ─── SWAGGER-UI CLEAN CUSTOM STYLING ─── */
        .swagger-ui .topbar { display: none !important; }

        .swagger-ui {
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            color: var(--text) !important;
        }

        .swagger-ui .info {
            margin: 10px 0 20px 0 !important;
        }

        .swagger-ui .info .title {
            font-family: 'Inter', sans-serif !important;
            font-weight: 800 !important;
            color: var(--brand-900) !important;
            font-size: 22px !important;
        }

        .swagger-ui .scheme-container {
            background: var(--card) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 16px 20px !important;
            margin: 16px 0 24px !important;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04) !important;
        }

        .swagger-ui .opblock-tag {
            font-family: 'Inter', sans-serif !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            color: var(--brand-900) !important;
            border-bottom: 2px solid var(--border) !important;
            padding: 12px 0 8px !important;
            margin: 24px 0 12px !important;
        }

        .swagger-ui .opblock-tag small {
            font-size: 12px !important;
            font-weight: 500 !important;
            color: var(--muted) !important;
        }

        .swagger-ui .opblock {
            border-radius: 10px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
            margin-bottom: 10px !important;
            border-width: 1px !important;
            transition: all 0.15s ease-in-out !important;
        }

        .swagger-ui .opblock:hover {
            box-shadow: 0 4px 12px rgba(3,92,163,0.08) !important;
        }

        .swagger-ui .opblock .opblock-summary-method {
            font-family: 'JetBrains Mono', monospace !important;
            font-weight: 700 !important;
            border-radius: 6px !important;
            text-shadow: none !important;
        }

        .swagger-ui .opblock .opblock-summary-path {
            font-family: 'JetBrains Mono', monospace !important;
            font-size: 13px !important;
            font-weight: 600 !important;
        }

        .swagger-ui .btn.authorize {
            background: var(--brand-700) !important;
            border-color: var(--brand-700) !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 600 !important;
        }

        .swagger-ui .btn.authorize svg {
            fill: #ffffff !important;
        }

        .swagger-ui .btn.execute {
            background: var(--brand-500) !important;
            border-color: var(--brand-500) !important;
            border-radius: 8px !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 600 !important;
        }

        .swagger-ui input[type=text],
        .swagger-ui textarea,
        .swagger-ui select {
            border-radius: 6px !important;
            border: 1px solid var(--border) !important;
            font-family: 'JetBrains Mono', monospace !important;
            font-size: 12px !important;
        }

        .swagger-ui section.models {
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
        }

        /* ─── FOOTER ─── */
        .api-footer {
            background: var(--brand-900);
            color: rgba(255,255,255,0.5);
            text-align: center;
            padding: 16px;
            font-size: 11px;
            letter-spacing: 0.3px;
        }
        .api-footer a {
            color: var(--brand-300);
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .api-info-stats { display: none; }
            .api-navbar { padding: 0 16px; }
            .api-content { padding: 16px; }
        }
    </style>
</head>
<body>

    <!-- TOP NAV -->
    <nav class="api-navbar">
        <div class="api-navbar-left">
            <div class="api-logo-icon">🏛️</div>
            <div class="api-title">
                Dukcapil Dompu REST API
                <small>Dinas Kependudukan &amp; Pencatatan Sipil</small>
            </div>
        </div>
        <div class="api-navbar-badges">
            <span class="api-badge api-badge-version">v1.0.0</span>
            <span class="api-badge api-badge-oas">OpenAPI 3.0</span>
        </div>
        <div class="api-navbar-right">
            <a href="/api/docs/json" target="_blank" class="api-nav-link api-nav-link-outline">
                📄 JSON Spec
            </a>
            <a href="/admin" class="api-nav-link api-nav-link-accent">
                ⚙️ CMS Admin
            </a>
        </div>
    </nav>

    <!-- INFO BAR -->
    <div class="api-info-bar">
        <div class="api-info-desc">
            REST API resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu. Menyediakan endpoint untuk data profil dinas, survei IKM, demografi kependudukan, berita, galeri, dan layanan publik.
        </div>
        <div class="api-info-stats">
            <div class="api-stat">
                <div class="api-stat-num">21</div>
                <div class="api-stat-label">Endpoints</div>
            </div>
            <div class="api-stat-divider"></div>
            <div class="api-stat">
                <div class="api-stat-num">10</div>
                <div class="api-stat-label">Tag Grup</div>
            </div>
            <div class="api-stat-divider"></div>
            <div class="api-stat">
                <div class="api-stat-num">🔒</div>
                <div class="api-stat-label">Bearer Auth</div>
            </div>
        </div>
    </div>

    <!-- SWAGGER UI -->
    <div class="api-content">
        <div id="swagger-ui"></div>
    </div>

    <!-- FOOTER -->
    <div class="api-footer">
        Dukcapil Dompu REST API &bull; Powered by <a href="https://swagger.io/tools/swagger-ui/" target="_blank">Swagger UI 5.11</a> &amp; <a href="https://zircote.github.io/swagger-php/" target="_blank">swagger-php 6</a>
        &bull; <a href="mailto:admin@dukcapil.dompukab.go.id">Hubungi Tim IT</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/api/docs/json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                tryItOutEnabled: true,
                displayRequestDuration: true,
                filter: true,
                syntaxHighlight: { theme: 'monokai' },
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                requestSnippetsEnabled: true,
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
