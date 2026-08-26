@extends('errors.layout')

@section('title', '419 - Sesi Kedaluwarsa')
@section('code', '419')
@section('icon')
    <i class="fa-solid fa-clock-rotate-left"></i>
@endsection
@section('heading', 'Sesi Keamanan Kedaluwarsa')
@section('message', 'Sesi keamanan halaman atau formulir Anda telah berakhir karena tidak ada aktivitas dalam waktu tertentu (CSRF Token Expired). Silakan segarkan (refresh) halaman ini atau login kembali untuk melanjutkan.')
