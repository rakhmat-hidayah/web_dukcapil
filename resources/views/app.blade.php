<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $theme = \App\Models\ThemeSetting::getAllCached();
        $siteTitle    = $theme['site_title'] ?? 'Dukcapil Dompu';
        $siteTagline  = $theme['site_tagline'] ?? 'Pelayanan Cepat, Tepat, dan Gratis';
        $siteDesc     = 'Website resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu, Nusa Tenggara Barat. Layanan KTP-el, KK, Akta Kelahiran, dan administrasi kependudukan lainnya.';
        $siteUrl      = rtrim(config('app.url'), '/');
        $siteLogo     = $siteUrl . '/android-chrome-512x512.png';
        $siteKeywords = 'Dukcapil Dompu, KTP-el, Kartu Keluarga, Akta Kelahiran, Administrasi Kependudukan, Disdukcapil Dompu, NTB, Pelayanan Publik';
    @endphp

    <title inertia>{{ $siteTitle }} - {{ $siteTagline }}</title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $siteTitle }} - {{ $siteTagline }}">
    <meta name="description" content="{{ $siteDesc }}">
    <meta name="keywords" content="{{ $siteKeywords }}">
    <meta name="author" content="Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="{{ $theme['primary_600'] ?? '#0274cb' }}">
    <link rel="canonical" href="{{ $siteUrl }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $siteUrl }}">
    <meta property="og:title" content="{{ $siteTitle }} - {{ $siteTagline }}">
    <meta property="og:description" content="{{ $siteDesc }}">
    <meta property="og:image" content="{{ $siteLogo }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="{{ $siteTitle }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $siteUrl }}">
    <meta name="twitter:title" content="{{ $siteTitle }} - {{ $siteTagline }}">
    <meta name="twitter:description" content="{{ $siteDesc }}">
    <meta name="twitter:image" content="{{ $siteLogo }}">

    <!-- Schema.org GovernmentOffice Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "GovernmentOffice",
        "name": "Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu",
        "alternateName": "Dukcapil Dompu",
        "url": "{{ $siteUrl }}",
        "logo": "{{ $siteLogo }}",
        "description": "{{ $siteDesc }}",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Jl. Mahoni No. 02, Kel. Badawa",
            "addressLocality": "Dompu",
            "addressRegion": "Nusa Tenggara Barat",
            "postalCode": "84211",
            "addressCountry": "ID"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "-8.5370",
            "longitude": "118.4624"
        },
        "openingHoursSpecification": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday"],
                "opens": "08:00",
                "closes": "15:30"
            },
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Friday",
                "opens": "08:00",
                "closes": "11:30"
            }
        ],
        "sameAs": ["{{ $siteUrl }}"]
    }
    </script>

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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <!-- Favicon Links (Transparent Emblem) -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

    <!-- Web App Manifest -->
    <link rel="manifest" href="/site.webmanifest">

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-gray-50 dark:bg-zinc-950 dark:text-zinc-50 transition-colors duration-200">
    @inertia
</body>
</html>
