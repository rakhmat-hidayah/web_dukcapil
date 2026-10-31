@extends('errors.layout')

@section('code', '403')
@section('title', 'Akses Ditolak')
@section('badge', 'ACCESS DENIED 403')
@section('message', 'Anda tidak memiliki hak akses untuk membuka halaman atau fitur ini. Silakan pastikan Anda telah login menggunakan akun resmi dengan hak otorisasi yang sesuai.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="200" r="160" stroke="#f43f5e" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    
    <!-- Shield Security Lock Center -->
    <g transform="translate(130, 90)">
        <path d="M70 10 L130 35 V90 C130 135 70 165 70 165 C70 165 10 135 10 90 V35 Z" fill="url(#shieldGrad)" stroke="#f43f5e" stroke-width="3" />
        
        <!-- Padlock inside shield -->
        <rect x="48" y="80" width="44" height="36" rx="6" fill="#0f172a" stroke="#ffffff" stroke-width="2" />
        <path d="M56 80 V65 C56 55 62 48 70 48 C78 48 84 55 84 65 V80" stroke="#ffffff" stroke-width="3" fill="none" stroke-linecap="round" />
        <circle cx="70" cy="95" r="4" fill="#f43f5e" />
    </g>

    <defs>
        <linearGradient id="shieldGrad" x1="10" y1="10" x2="130" y2="165">
            <stop offset="0%" stop-color="#be123c" />
            <stop offset="100%" stop-color="#4c0519" />
        </linearGradient>
    </defs>
</svg>
@endsection
