@extends('errors.layout')

@section('code', '500')
@section('title', 'Internal Server Error')
@section('badge', 'SERVER ERROR 500')
@section('message', 'Mohon maaf, terjadi gangguan internal pada sistem saat memproses permintaan Anda. Tim teknis kami telah menerima notifikasi dan sedang melakukan penanganan secara intensif. Silakan coba kembali beberapa saat lagi.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Outer Orbit Circle -->
    <circle cx="200" cy="200" r="160" stroke="#0284c7" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    <circle cx="200" cy="200" r="120" stroke="#38bdf8" stroke-width="1" opacity="0.2" />

    <!-- Cloud Graphic Top -->
    <g transform="translate(140, 50)">
        <path d="M20 50 C20 35, 35 25, 50 25 C60 15, 80 15, 90 25 C105 25, 115 35, 115 50 C125 50, 130 60, 130 70 C130 85, 115 90, 100 90 L30 90 C15 90, 5 80, 5 65 C5 55, 15 50, 20 50 Z" fill="url(#cloudGrad)" opacity="0.85" filter="url(#dropShadow)" />
    </g>

    <!-- 3D Server Stack Center -->
    <!-- Server Unit 1 (Top) -->
    <g transform="translate(100, 130)">
        <rect x="0" y="0" width="200" height="42" rx="10" fill="url(#serverGrad1)" stroke="rgba(255,255,255,0.2)" stroke-width="1" />
        <circle cx="25" cy="21" r="5" fill="#38bdf8" />
        <circle cx="42" cy="21" r="5" fill="#34d399" />
        <rect x="65" y="16" width="70" height="10" rx="3" fill="rgba(255,255,255,0.1)" />
        <rect x="150" y="16" width="30" height="10" rx="3" fill="#0284c7" />
    </g>

    <!-- Server Unit 2 (Middle - Active Alert) -->
    <g transform="translate(100, 185)">
        <rect x="0" y="0" width="200" height="42" rx="10" fill="url(#serverGrad2)" stroke="rgba(244,63,94,0.4)" stroke-width="1.5" />
        <circle cx="25" cy="21" r="5" fill="#f43f5e" class="animate-ping" />
        <circle cx="25" cy="21" r="5" fill="#f43f5e" />
        <circle cx="42" cy="21" r="5" fill="#fbbf24" />
        <rect x="65" y="16" width="70" height="10" rx="3" fill="rgba(244,63,94,0.2)" />
        <rect x="150" y="16" width="30" height="10" rx="3" fill="#f43f5e" />
    </g>

    <!-- Server Unit 3 (Bottom) -->
    <g transform="translate(100, 240)">
        <rect x="0" y="0" width="200" height="42" rx="10" fill="url(#serverGrad1)" stroke="rgba(255,255,255,0.2)" stroke-width="1" />
        <circle cx="25" cy="21" r="5" fill="#38bdf8" />
        <circle cx="42" cy="21" r="5" fill="#34d399" />
        <rect x="65" y="16" width="70" height="10" rx="3" fill="rgba(255,255,255,0.1)" />
        <rect x="150" y="16" width="30" height="10" rx="3" fill="#0284c7" />
    </g>

    <!-- Database Cylinder Node Bottom Left -->
    <g transform="translate(60, 290)">
        <path d="M0 10 C0 4.47, 17.9 0, 40 0 C62.1 0, 80 4.47, 80 10 L80 40 C80 45.53, 62.1 50, 40 50 C17.9 50, 0 45.53, 0 40 Z" fill="url(#dbGrad)" opacity="0.9" />
        <ellipse cx="40" cy="10" rx="40" ry="10" fill="#38bdf8" opacity="0.6" />
    </g>

    <!-- Warning Triangle Icon Badge Floating Right -->
    <g transform="translate(260, 275)" class="animate-bounce">
        <circle cx="35" cy="35" r="30" fill="#0f172a" stroke="#f43f5e" stroke-width="2" />
        <path d="M35 20 L50 48 L20 48 Z" fill="#f43f5e" />
        <text x="35" y="44" text-anchor="middle" fill="#ffffff" font-size="16" font-weight="bold">!</text>
    </g>

    <!-- Network Lines Connecting Nodes -->
    <path d="M200 95 L200 130" stroke="#38bdf8" stroke-width="2" stroke-dasharray="4 4" />
    <path d="M100 315 L140 282" stroke="#0284c7" stroke-width="2" />

    <!-- Gradients & Filters Definition -->
    <defs>
        <linearGradient id="cloudGrad" x1="0" y1="0" x2="130" y2="90">
            <stop offset="0%" stop-color="#38bdf8" />
            <stop offset="100%" stop-color="#0284c7" />
        </linearGradient>

        <linearGradient id="serverGrad1" x1="0" y1="0" x2="200" y2="42">
            <stop offset="0%" stop-color="#0f2b48" />
            <stop offset="100%" stop-color="#1e3a5f" />
        </linearGradient>

        <linearGradient id="serverGrad2" x1="0" y1="0" x2="200" y2="42">
            <stop offset="0%" stop-color="#2a121d" />
            <stop offset="100%" stop-color="#4c1d24" />
        </linearGradient>

        <linearGradient id="dbGrad" x1="0" y1="0" x2="80" y2="50">
            <stop offset="0%" stop-color="#0284c7" />
            <stop offset="100%" stop-color="#075985" />
        </linearGradient>

        <filter id="dropShadow" x="-10" y="-10" width="160" height="120">
            <feDropShadow dx="0" dy="8" stdDeviation="6" flood-color="#000000" flood-opacity="0.4" />
        </filter>
    </defs>
</svg>
@endsection
