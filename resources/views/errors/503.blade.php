@extends('errors.layout')

@section('code', '503')
@section('title', 'Sistem Dalam Pemeliharaan')
@section('badge', 'MAINTENANCE MODE 503')
@section('message', 'Sistem portal layanan publik Disdukcapil Kabupaten Dompu saat ini sedang dalam pemeliharaan rutin atau peningkatan performa server. Layanan akan kembali normal dalam beberapa saat.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="200" r="160" stroke="#0284c7" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    
    <!-- Wrench & Gear Maintenance Graphic -->
    <g transform="translate(110, 100)">
        <!-- Rotating Gear -->
        <circle cx="90" cy="90" r="50" fill="url(#gearGrad)" stroke="#38bdf8" stroke-width="2" class="animate-spin-slow" />
        <circle cx="90" cy="90" r="20" fill="#0f172a" />
        
        <!-- Wrench Icon -->
        <path d="M40 140 L110 70 M100 60 L120 80" stroke="#38bdf8" stroke-width="12" stroke-linecap="round" />
    </g>

    <defs>
        <linearGradient id="gearGrad" x1="40" y1="40" x2="140" y2="140">
            <stop offset="0%" stop-color="#0284c7" />
            <stop offset="100%" stop-color="#082a4d" />
        </linearGradient>
    </defs>
</svg>
@endsection
