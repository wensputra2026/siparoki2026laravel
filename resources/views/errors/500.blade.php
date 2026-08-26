@extends('errors.layout')

@section('title', '500 - Kesalahan Server')
@section('code', '500')
@section('icon')
    <i class="fa-solid fa-triangle-exclamation"></i>
@endsection
@section('heading', 'Terjadi Kendala pada Server')
@section('message', 'Mohon maaf, sistem mengalami kendala pemrosesan internal pada permintaan Anda. Log error telah dicatat secara otomatis untuk ditinjau oleh tim administrator paroki. Silakan coba kembali beberapa saat lagi.')
