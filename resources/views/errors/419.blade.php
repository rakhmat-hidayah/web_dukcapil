@extends('errors.layout')

@section('code', '419')
@section('title', 'Sesi Berakhir (Page Expired)')
@section('badge', 'SESSION EXPIRED 419')
@section('message', 'Sesi keamanan formulir Anda telah berakhir karena tidak ada aktivitas dalam waktu tertentu. Silakan muat ulang halaman ini dan muat kembali formulir Anda.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="200" r="160" stroke="#f59e0b" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    
    <!-- Hourglass Timer Graphic -->
    <g transform="translate(130, 90)">
        <path d="M20 20 L120 20 L80 80 L120 140 L20 140 L60 80 Z" fill="url(#timerGrad)" stroke="#f59e0b" stroke-width="3" />
        <line x1="15" y1="20" x2="125" y2="20" stroke="#fef3c7" stroke-width="5" stroke-linecap="round" />
        <line x1="15" y1="140" x2="125" y2="140" stroke="#fef3c7" stroke-width="5" stroke-linecap="round" />
        <circle cx="70" cy="115" r="12" fill="#fbbf24" opacity="0.8" />
    </g>

    <defs>
        <linearGradient id="timerGrad" x1="20" y1="20" x2="120" y2="140">
            <stop offset="0%" stop-color="#b45309" />
            <stop offset="100%" stop-color="#451a03" />
        </linearGradient>
    </defs>
</svg>
@endsection
