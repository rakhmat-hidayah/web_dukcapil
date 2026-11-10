<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    @php
        $theme = \App\Models\ThemeSetting::getAllCached();
        $siteTitle    = $theme['site_title'] ?? 'Dukcapil Dompu';
        $siteTagline  = $theme['site_tagline'] ?? 'Pelayanan Cepat, Tepat, dan Gratis';
        $siteDesc     = 'Website Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu, Nusa Tenggara Barat. Layanan KTP-el, KK, Akta Kelahiran, dan administrasi kependudukan lainnya.';
        $siteUrl      = rtrim(config('app.url'), '/');
        $siteLogo     = $siteUrl . '/android-chrome-512x512.png';
        $siteKeywords = 'Dukcapil Dompu, KTP-el, Kartu Keluarga, Akta Kelahiran, Administrasi Kependudukan, Disdukcapil Dompu, NTB, Pelayanan Publik';

        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOffice',
            'name' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu',
            'alternateName' => 'Dukcapil Dompu',
            'url' => $siteUrl,
            'logo' => $siteLogo,
            'description' => $siteDesc,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl. Soekarno Hatta No. 9',
                'addressLocality' => 'Dompu',
                'addressRegion' => 'Nusa Tenggara Barat',
                'postalCode' => '84211',
                'addressCountry' => 'ID',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '-8.5370',
                'longitude' => '118.4624',
            ],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                    'opens' => '08:00',
                    'closes' => '15:30',
                ],
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => 'Friday',
                    'opens' => '08:00',
                    'closes' => '11:30',
                ],
            ],
            'sameAs' => [$siteUrl],
        ];
    @endphp

    @if(isset($seoData))
        <title inertia>{{ $seoData['title'] }}</title>
        <meta name="title" content="{{ $seoData['title'] }}">
        <meta name="description" content="{{ $seoData['description'] }}">
        <meta name="author" content="{{ $seoData['og']['author'] ?? 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu' }}">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="{{ $theme['primary_600'] ?? '#0274cb' }}">
        <link rel="canonical" href="{{ $seoData['canonical'] }}">

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="{{ $seoData['og']['type'] ?? 'article' }}">
        <meta property="og:url" content="{{ $seoData['og']['url'] }}">
        <meta property="og:title" content="{{ $seoData['og']['title'] }}">
        <meta property="og:description" content="{{ $seoData['og']['description'] }}">
        <meta property="og:image" content="{{ $seoData['og']['image'] }}">
        <meta property="og:image:secure_url" content="{{ $seoData['og']['image'] }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="{{ $seoData['og']['image_width'] }}">
        <meta property="og:image:height" content="{{ $seoData['og']['image_height'] }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:site_name" content="{{ $seoData['og']['site_name'] }}">
        @if(!empty($seoData['og']['published_time']))
            <meta property="article:published_time" content="{{ $seoData['og']['published_time'] }}">
        @endif
        @if(!empty($seoData['og']['modified_time']))
            <meta property="article:modified_time" content="{{ $seoData['og']['modified_time'] }}">
        @endif
        @if(!empty($seoData['og']['author']))
            <meta property="article:author" content="{{ $seoData['og']['author'] }}">
        @endif
        @if(!empty($seoData['og']['section']))
            <meta property="article:section" content="{{ $seoData['og']['section'] }}">
        @endif
        @if(!empty($seoData['og']['tags']))
            @foreach($seoData['og']['tags'] as $tag)
                <meta property="article:tag" content="{{ $tag }}">
            @endforeach
        @endif

        <!-- Twitter Card -->
        <meta name="twitter:card" content="{{ $seoData['twitter']['card'] }}">
        <meta name="twitter:url" content="{{ $seoData['twitter']['url'] }}">
        <meta name="twitter:title" content="{{ $seoData['twitter']['title'] }}">
        <meta name="twitter:description" content="{{ $seoData['twitter']['description'] }}">
        <meta name="twitter:image" content="{{ $seoData['twitter']['image'] }}">
        <meta name="twitter:image:alt" content="{{ $seoData['twitter']['image_alt'] }}">
        <meta name="twitter:site" content="{{ $seoData['twitter']['site'] }}">

        <!-- Schema.org NewsArticle Structured Data -->
        <script type="application/ld+json">
        {!! json_encode($seoData['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @else
        <title inertia>{{ $siteTitle }} - {{ $siteTagline }}</title>

        <!-- Primary Meta Tags -->
        <meta name="title" content="{{ $siteTitle }} - {{ $siteTagline }}">
        <meta name="description" content="{{ $siteDesc }}">
        <meta name="keywords" content="{{ $siteKeywords }}">
        <meta name="author" content="Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="{{ $theme['primary_600'] ?? '#0274cb' }}">
        <link rel="canonical" href="{{ request()->url() }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ request()->url() }}">
        <meta property="og:title" content="{{ $siteTitle }} - {{ $siteTagline }}">
        <meta property="og:description" content="{{ $siteDesc }}">
        <meta property="og:image" content="{{ $siteLogo }}">
        <meta property="og:image:width" content="512">
        <meta property="og:image:height" content="512">
        <meta property="og:locale" content="id_ID">
        <meta property="og:site_name" content="{{ $siteTitle }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ request()->url() }}">
        <meta name="twitter:title" content="{{ $siteTitle }} - {{ $siteTagline }}">
        <meta name="twitter:description" content="{{ $siteDesc }}">
        <meta name="twitter:image" content="{{ $siteLogo }}">

        <!-- Schema.org GovernmentOffice Structured Data -->
        <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endif

    <!-- Dynamic Theme Customizer Styling Injection Hook -->
    <style id="dynamic-theme-variables">
        :root {
            --primary-50: {{ $theme['primary_50'] ?? '#f0f7ff' }};
            --primary-100: {{ $theme['primary_100'] ?? '#e0effe' }};
            --primary-200: {{ $theme['primary_200'] ?? '#bae0fd' }};
            --primary-300: {{ $theme['primary_300'] ?? '#7cc7fc' }};
            --primary-400: {{ $theme['primary_400'] ?? '#38abfa' }};
            --primary-500: {{ $theme['primary_500'] ?? '#0e91eb' }};
            --primary-600: {{ $theme['primary_600'] ?? '#0274cb' }};
            --primary-700: {{ $theme['primary_700'] ?? '#035ca3' }};
            --primary-800: {{ $theme['primary_800'] ?? '#074f87' }};
            --primary-900: {{ $theme['primary_900'] ?? '#0c4270' }};
            --primary-950: {{ $theme['primary_950'] ?? '#082a4a' }};

            --secondary-50: {{ $theme['secondary_50'] ?? '#fafaf9' }};
            --secondary-100: {{ $theme['secondary_100'] ?? '#f5f5f4' }};
            --secondary-200: {{ $theme['secondary_200'] ?? '#e7e5e4' }};
            --secondary-300: {{ $theme['secondary_300'] ?? '#d6d3d1' }};
            --secondary-400: {{ $theme['secondary_400'] ?? '#a8a29e' }};
            --secondary-505: {{ $theme['secondary_500'] ?? '#78716c' }}; /* fallbacks */
            --secondary-500: {{ $theme['secondary_500'] ?? '#78716c' }};
            --secondary-600: {{ $theme['secondary_600'] ?? '#57534e' }};
            --secondary-700: {{ $theme['secondary_700'] ?? '#44403c' }};
            --secondary-800: {{ $theme['secondary_800'] ?? '#292524' }};
            --secondary-900: {{ $theme['secondary_900'] ?? '#1c1917' }};
            --secondary-950: {{ $theme['secondary_950'] ?? '#0c0a09' }};

            --accent-50: {{ $theme['accent_50'] ?? '#fffbeb' }};
            --accent-100: {{ $theme['accent_100'] ?? '#fef3c7' }};
            --accent-200: {{ $theme['accent_200'] ?? '#fde047' }};
            --accent-300: {{ $theme['accent_300'] ?? '#facc15' }};
            --accent-400: {{ $theme['accent_400'] ?? '#eab308' }};
            --accent-500: {{ $theme['accent_500'] ?? '#ca8a04' }};
            --accent-600: {{ $theme['accent_600'] ?? '#a16207' }};
            --accent-700: {{ $theme['accent_700'] ?? '#854d0e' }};
            --accent-800: {{ $theme['accent_800'] ?? '#713f12' }};
            --accent-900: {{ $theme['accent_900'] ?? '#451a03' }};

            --font-family: '{{ $theme['font_family'] ?? 'Plus Jakarta Sans' }}', 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
        }
    </style>

    <!-- Optimized Non-Blocking Google Fonts Loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">
    </noscript>

    <!-- Favicon Links (Transparent Emblem) -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- PWA Web App Manifest & Mobile Standalone App Meta Tags -->
    <link rel="manifest" href="/site.webmanifest">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Dukcapil Dompu">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="format-detection" content="telephone=no">

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="min-h-full font-sans antialiased text-gray-900 bg-gray-50 dark:bg-zinc-950 dark:text-zinc-50 transition-colors duration-200 overflow-x-hidden w-full max-w-full">
    <!-- Instant First-Paint Brand Splash Screen (Eliminates Blank White Screen during Initial Load) -->
    <div id="initial-page-preloader" style="position:fixed;inset:0;z-index:999999;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#ffffff;transition:opacity .35s ease-out;pointer-events:none;">
        <div style="text-align:center;padding:24px;max-width:320px;">
            <img src="/favicon.png" alt="Logo Disdukcapil" style="width:64px;height:64px;margin:0 auto 14px auto;display:block;animation:preloaderPulse 1.6s infinite ease-in-out;" onerror="this.style.display='none'">
            <div style="font-family:system-ui,-apple-system,BlinkMacSystemFont,sans-serif;font-weight:700;font-size:15px;color:#0c4270;letter-spacing:-0.01em;margin-bottom:4px;">Disdukcapil Kabupaten Dompu</div>
            <div style="font-family:system-ui,-apple-system,BlinkMacSystemFont,sans-serif;font-size:12px;color:#64748b;margin-bottom:18px;">Memuat Layanan Kependudukan...</div>
            <div style="width:140px;height:4px;background:#e2e8f0;border-radius:9999px;margin:0 auto;overflow:hidden;position:relative;">
                <div style="position:absolute;top:0;bottom:0;width:40%;background:#0274cb;border-radius:9999px;animation:preloaderProgress 1.4s infinite ease-in-out;"></div>
            </div>
        </div>
    </div>
    <style>
        @keyframes preloaderPulse { 0%, 100% { transform: scale(1); opacity: 0.95; } 50% { transform: scale(1.06); opacity: 1; } }
        @keyframes preloaderProgress { 0% { left: -40%; width: 40%; } 50% { left: 40%; width: 50%; } 100% { left: 100%; width: 30%; } }
        @media (prefers-color-scheme: dark) {
            #initial-page-preloader { background: #09090b !important; }
            #initial-page-preloader div > div:nth-child(2) { color: #f1f5f9 !important; }
            #initial-page-preloader div > div:nth-child(3) { color: #94a3b8 !important; }
            #initial-page-preloader div > div:nth-child(4) { background: #27272a !important; }
        }
    </style>
    @inertia

    <!-- Preloader Auto-Dismiss Failsafe (Guarantees dismiss within 1.5s max) -->
    <script>
        setTimeout(function() {
            var pre = document.getElementById('initial-page-preloader');
            if (pre) {
                pre.style.opacity = '0';
                setTimeout(function() { if (pre && pre.parentNode) pre.remove(); }, 350);
            }
        }, 1500);
    </script>
</body>
</html>
