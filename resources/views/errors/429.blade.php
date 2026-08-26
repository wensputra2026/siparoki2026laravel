@extends('errors.layout')

@section('title', '429 - Terlalu Banyak Permintaan')
@section('code', '429')
@section('icon')
    <i class="fa-solid fa-gauge-simple-high"></i>
@endsection
@section('heading', 'Batas Permintaan Tercapai')
@section('message', 'Sistem mendeteksi terlalu banyak permintaan dalam waktu yang sangat singkat dari alamat IP Anda (Rate Limit). Harap tunggu beberapa saat sebelum mencoba memuat halaman kembali.')
