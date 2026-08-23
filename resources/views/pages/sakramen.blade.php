@extends('layouts.app')

@section('title', 'Pengajuan Sakramen - SIPAROKI')
@section('description', 'Formulir pendaftaran dan pengajuan sakramen online Paroki')

@section('content')
<div class="bg-amber-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Pengajuan Sakramen</h1>
        <p class="text-amber-100 mt-2">Ajukan permohonan sakramen secara online</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @livewire('form-sakramen')
</div>
@endsection
