@extends('layouts.public')

@section('title', 'Galeri Video - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Dokumentasi video misa, perayaan sakramental, dan kegiatan pastoral ' . ($globalNamaParoki ?? 'SIPAROKI') . '.')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="st-badge"><i class="fas fa-play me-1"></i> Video Paroki</span>
        <h1>Dokumentasi Audio Visual</h1>
        <p>Tayangan misa, perayaan sakramental, dan liputan peristiwa pastoral.</p>
    </div>
</section>

<section class="content-section public-video-page">
    <div class="container">
        @if(isset($videos) && $videos->count())
            <div class="public-video-grid">
                @foreach($videos as $video)
                    @php
                        $title = $video->judul ?? $video->nama ?? 'Video Dokumentasi';
                        $youtubeUrl = $video->youtube_url ?? '';
                        $embedUrl = null;
                        if ($youtubeUrl && preg_match('/(?:youtu\.be\/|v=|embed\/|shorts\/)([A-Za-z0-9_-]{6,})/', $youtubeUrl, $match)) {
                            $embedUrl = 'https://www.youtube.com/embed/' . $match[1];
                        }
                    @endphp
                    <article class="public-video-card">
                        <div class="public-video-frame">
                            @if($embedUrl)
                                <iframe src="{{ $embedUrl }}" title="{{ $title }}" allowfullscreen loading="lazy"></iframe>
                            @else
                                <div class="public-video-placeholder">
                                    <i class="fas fa-video"></i>
                                </div>
                            @endif
                        </div>
                        <div class="public-video-body">
                            <h3>{{ $title }}</h3>
                            @if(!empty($video->deskripsi))
                                <p>{{ Str::limit(strip_tags($video->deskripsi), 120) }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if(method_exists($videos, 'hasPages') && $videos->hasPages())
                <div class="public-pagination">
                    {{ $videos->links() }}
                </div>
            @endif
        @else
            <div class="gallery-empty">
                <i class="fas fa-video"></i>
                <h3>Video dokumentasi belum tersedia</h3>
                <p>Dokumentasi audio visual akan tampil di halaman ini setelah dipublikasikan oleh pengelola paroki.</p>
            </div>
        @endif
    </div>
</section>
@endsection
