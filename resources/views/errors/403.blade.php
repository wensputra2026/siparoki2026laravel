@extends('errors.layout')

@section('title', '403 - Akses Ditolak')
@section('code', '403')
@section('icon')
    <i class="fa-solid fa-shield-halved"></i>
@endsection
@section('heading', 'Akses Dibatasi / Ditolak')
@section('message', 'Anda tidak memiliki hak akses atau wewenang yang memadai untuk membuka halaman ini. Pastikan Anda telah masuk (login) dengan akun petugas atau role yang sesuai dengan lingkup wilayah/tugas Anda.')
