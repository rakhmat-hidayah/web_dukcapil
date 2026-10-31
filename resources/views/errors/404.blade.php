@extends('errors.layout')

@section('code', '404')
@section('title', 'Halaman Tidak Ditemukan')
@section('badge', 'NOT FOUND 404')
@section('message', 'Mohon maaf, halaman atau berkas dokumen yang Anda cari tidak ditemukan atau telah dipindahkan ke alamat lain. Silakan periksa kembali tautan URL Anda.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="200" r="160" stroke="#0284c7" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    
    <!-- Magnifying Glass Search Graphic -->
    <g transform="translate(110, 110)">
        <circle cx="85" cy="85" r="65" stroke="url(#searchGrad)" stroke-width="16" fill="rgba(2,132,199,0.1)" />
        <line x1="135" y1="135" x2="195" y2="195" stroke="url(#handleGrad)" stroke-width="20" stroke-linecap="round" />
        <text x="85" y="100" text-anchor="middle" fill="#38bdf8" font-size="44" font-weight="900">?</text>
    </g>

    <!-- Document File Floating Left -->
    <g transform="translate(60, 120)" class="animate-float">
        <rect x="0" y="0" width="55" height="75" rx="8" fill="#0f2b48" stroke="#38bdf8" stroke-width="1.5" />
        <line x1="12" y1="20" x2="43" y2="20" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" />
        <line x1="12" y1="35" x2="35" y2="35" stroke="rgba(255,255,255,0.4)" stroke-width="3" stroke-linecap="round" />
        <line x1="12" y1="50" x2="28" y2="50" stroke="rgba(255,255,255,0.4)" stroke-width="3" stroke-linecap="round" />
    </g>

    <defs>
        <linearGradient id="searchGrad" x1="20" y1="20" x2="150" y2="150">
            <stop offset="0%" stop-color="#38bdf8" />
            <stop offset="100%" stop-color="#0274cb" />
        </linearGradient>
        <linearGradient id="handleGrad" x1="135" y1="135" x2="195" y2="195">
            <stop offset="0%" stop-color="#0284c7" />
            <stop offset="100%" stop-color="#082a4d" />
        </linearGradient>
    </defs>
</svg>
@endsection
