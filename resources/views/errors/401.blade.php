@extends('errors.layout')

@section('code', '401')
@section('title', 'Tidak Memiliki Otorisasi')
@section('badge', 'UNAUTHORIZED 401')
@section('message', 'Anda belum memiliki sesi otorisasi yang valid untuk mengakses sistem ini. Silakan masuk terlebih dahulu menggunakan kredensial akun Anda yang sah.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="200" r="160" stroke="#38bdf8" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    <g transform="translate(130, 90)">
        <rect x="20" y="60" width="100" height="80" rx="12" fill="#0f2b48" stroke="#38bdf8" stroke-width="2" />
        <path d="M45 60 V40 C45 26 56 15 70 15 C84 15 95 26 95 40 V60" stroke="#38bdf8" stroke-width="3" fill="none" />
        <circle cx="70" cy="100" r="8" fill="#38bdf8" />
    </g>
</svg>
@endsection
