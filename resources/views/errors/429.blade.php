@extends('errors.layout')

@section('code', '429')
@section('title', 'Terlalu Banyak Permintaan')
@section('badge', 'TOO MANY REQUESTS 429')
@section('message', 'Sistem keamanan mendeteksi jumlah permintaan yang terlalu banyak dari perangkat atau jaringan Anda dalam waktu singkat. Silakan tunggu beberapa saat sebelum mencoba kembali.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="200" r="160" stroke="#f59e0b" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    <g transform="translate(110, 100)">
        <circle cx="90" cy="90" r="70" fill="rgba(245, 158, 11, 0.1)" stroke="#f59e0b" stroke-width="3" />
        <path d="M90 40 V90 L125 110" stroke="#f59e0b" stroke-width="4" stroke-linecap="round" />
        <circle cx="90" cy="90" r="6" fill="#f59e0b" />
    </g>
</svg>
@endsection
