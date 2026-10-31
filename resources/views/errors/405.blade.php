@extends('errors.layout')

@section('code', '405')
@section('title', 'Metode Tidak Diizinkan')
@section('badge', 'METHOD NOT ALLOWED 405')
@section('message', 'Metode permintaan HTTP yang dikirimkan tidak diizinkan untuk rute alamat ini. Silakan kembali ke beranda dan ikuti alur navigasi yang tersedia.')

@section('illustration')
<svg class="w-full h-full max-w-[340px] max-h-[340px]" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="200" r="160" stroke="#f43f5e" stroke-width="1.5" stroke-dasharray="8 8" opacity="0.3" class="animate-spin-slow" />
    <g transform="translate(120, 110)">
        <circle cx="80" cy="80" r="60" fill="rgba(244, 63, 94, 0.1)" stroke="#f43f5e" stroke-width="3" />
        <line x1="38" y1="38" x2="122" y2="122" stroke="#f43f5e" stroke-width="5" stroke-linecap="round" />
    </g>
</svg>
@endsection
