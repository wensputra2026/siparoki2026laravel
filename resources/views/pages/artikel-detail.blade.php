@extends('layouts.app')

@php
    $detailType = $detailType ?? 'berita';
    $body = $item->isi ?? $item->konten ?? '';
    $description = $item->meta_description ?? $item->excerpt ?? Str::limit(strip_tags($body), 160);
    $imagePath = $item->gambar ?? null;
    $imageUrl = null;

    if ($imagePath) {
        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            $imageUrl = $imagePath;
        } else {
            $cleanImagePath = ltrim($imagePath, '/');
            $base = basename($cleanImagePath);
            if (str_starts_with($cleanImagePath, 'uploads/konten/') || str_starts_with($cleanImagePath, 'assets/uploads/konten/')) {
                $imageUrl = asset($cleanImagePath);
            } elseif (file_exists(public_path('uploads/konten/' . $base))) {
                $imageUrl = asset('uploads/konten/' . $base);
            } elseif (file_exists(public_path('assets/uploads/konten/' . $base))) {
                $imageUrl = asset('assets/uploads/konten/' . $base);
            } elseif (str_starts_with($cleanImagePath, 'storage/') || str_starts_with($cleanImagePath, 'assets/') || str_starts_with($cleanImagePath, 'uploads/')) {
                $imageUrl = asset($cleanImagePath);
            } else {
                $imageUrl = asset('uploads/konten/' . $base);
            }
        }
    } elseif (!empty($globalLogo)) {
        $imageUrl = Str::startsWith($globalLogo, ['http://', 'https://']) ? $globalLogo : url($globalLogo);
    }

    $publishedAt = $item->tanggal_publish ?? $item->created_at ?? now();
    $backRoute = $detailType === 'berita' ? route('berita') : route('artikel');
    $backLabel = $detailType === 'berita' ? 'Kembali ke Berita' : 'Kembali ke Artikel';
    $relatedRouteName = $detailType === 'berita' ? 'berita.detail' : 'artikel.detail';
    $categoryClass = Str::contains(strtolower($item->kategori ?? ''), 'pengumuman') ? 'category-announcement' : 'category-news';
@endphp

@section('title', ($item->meta_title ?? $item->judul) . ' - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', $description)
@section('keywords', trim(($item->tags ?? '') . ', ' . ($item->kategori ?? '') . ', paroki, gereja katolik'))
@section('og_type', 'article')
@section('image', $imageUrl)
@section('article_meta')
    <meta property="article:published_time" content="{{ \Carbon\Carbon::parse($publishedAt)->toIso8601String() }}">
    @if(!empty($item->updated_at))
        <meta property="article:modified_time" content="{{ \Carbon\Carbon::parse($item->updated_at)->toIso8601String() }}">
    @endif
    @if(!empty($item->penulis))
        <meta property="article:author" content="{{ $item->penulis }}">
    @endif
    @if(!empty($item->kategori))
        <meta property="article:section" content="{{ $item->kategori }}">
    @endif
@endsection
@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $item->judul,
            'description' => $description,
            'image' => $imageUrl ? [$imageUrl] : [],
            'datePublished' => \Carbon\Carbon::parse($publishedAt)->toIso8601String(),
            'dateModified' => \Carbon\Carbon::parse($item->updated_at ?? $publishedAt)->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $item->penulis ?? 'Admin'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $globalNamaParoki ?? 'SIPAROKI',
                'logo' => ['@type' => 'ImageObject', 'url' => !empty($globalLogo) ? url($globalLogo) : null],
            ],
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 18px; color: #ffffff; line-height: 1.25; margin-left: auto; margin-right: auto;">
            {{ $item->judul }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 10px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="{{ $backRoute }}" style="color: white; text-decoration: none; font-weight: 500;">{{ $detailType === 'berita' ? 'Berita' : 'Artikel' }}</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 20px; border-radius: 25px; font-size: 0.85rem; font-weight: 700;">
                    {{ $item->judul }}
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Main Detail Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 70px; background: #f4faf9;">
    <div class="container">
        
        <!-- Back Link Above Card -->
        <div style="margin-bottom: 25px;">
            <a href="{{ $backRoute }}" class="back-link" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-teal, #00897b); font-weight: 600; text-decoration: none; font-size: 0.95rem; transition: transform 0.2s;">
                <i class="fas fa-arrow-left"></i> {{ $backLabel }}
            </a>
        </div>

        <div style="display: grid; grid-template-columns: minmax(0, 3fr) minmax(320px, 1.15fr); gap: 32px; align-items: start;">
            
            <!-- Left: Main Article Card -->
            <div>
                <div class="detail-content" style="background: #ffffff; padding: 35px 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    
                    <!-- 1. Category Pill Badge -->
                    <div style="margin-bottom: 20px;">
                        <span style="display: inline-block; background: #5c6bc0; color: #ffffff; padding: 6px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $item->kategori ?? 'BERITA' }}
                        </span>
                    </div>

                    <!-- 2. Meta Info Row -->
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 24px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eef2f6; font-size: 0.9rem; color: #64748b;">
                        <span style="display: inline-flex; align-items: center; gap: 7px;">
                            <i class="far fa-calendar-alt" style="color: #ff9800; font-size: 1rem;"></i> 
                            {{ \Carbon\Carbon::parse($publishedAt)->translatedFormat('d M Y') }}
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 7px;">
                            <i class="far fa-user" style="color: #ff9800; font-size: 1rem;"></i> 
                            {{ $item->penulis ?? 'Super Administrator' }}
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 7px;">
                            <i class="far fa-eye" style="color: #ff9800; font-size: 1rem;"></i> 
                            {{ number_format((int) ($item->views ?? 1), 0, ',', '.') }} views
                        </span>
                    </div>

                    <!-- 3. Featured Image Below Meta -->
                    @if($imageUrl)
                        <div style="border-radius: 12px; overflow: hidden; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                            <img src="{{ $imageUrl }}" alt="{{ $item->judul }}" style="width: 100%; max-height: 480px; object-fit: cover; display: block;">
                        </div>
                    @endif

                    <!-- 4. Article HTML Body -->
                    <div class="article-text" style="color: #334155; font-size: 1rem; line-height: 1.85;">
                        {!! $body !!}
                    </div>

                    <!-- 5. Tags / Labels -->
                    @if(!empty($item->tags))
                        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eef2f6; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-right: 4px;">
                                <i class="fas fa-tags" style="color: var(--primary-teal, #00897b);"></i> Label:
                            </span>
                            @foreach(explode(',', $item->tags) as $tag)
                                @if(trim($tag))
                                    <span style="background: #f1f5f9; color: #475569; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                        #{{ trim($tag) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Komentar & Tanggapan Section Konoha Style -->
                <div class="comments-section" id="commentsSection" style="margin-top: 32px; background: #ffffff; padding: 35px 38px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; padding-bottom: 16px; border-bottom: 2px solid #f1f5f9; flex-wrap: wrap; gap: 10px;">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 10px;">
                            <i class="far fa-comments" style="color: var(--primary-teal, #00897b); font-size: 1.35rem;"></i> 
                            Komentar &amp; Diskusi (<span id="commentCount">2</span>)
                        </h3>
                        <a href="#commentFormBox" class="btn btn-sm" style="background: rgba(0, 137, 123, 0.1); color: var(--primary-teal, #00897b); font-weight: 600; border-radius: 20px; font-size: 0.8rem; padding: 6px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-pen"></i> Tulis Komentar
                        </a>
                    </div>

                    <!-- List Komentar -->
                    <div id="commentsList" style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 35px;">
                        
                        <!-- Sample Comment 1 -->
                        <div class="comment-item" id="comment-1" style="background: #f8fafc; border-radius: 12px; padding: 20px 22px; border: 1px solid #e2e8f0; transition: all 0.2s;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-teal, #00897b), #004d40); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(0,137,123,0.25); flex-shrink: 0;">
                                        YM
                                    </div>
                                    <div>
                                        <h5 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">Yohanes Maria</h5>
                                        <span style="font-size: 0.75rem; color: #94a3b8;"><i class="far fa-clock me-1"></i> 2 jam yang lalu</span>
                                    </div>
                                </div>
                                <button type="button" class="reply-btn" onclick="openReplyForm('1', 'Yohanes Maria')" style="background: white; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 14px; font-size: 0.78rem; font-weight: 600; color: var(--primary-teal, #00897b); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-reply"></i> Balas
                                </button>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 0.9rem; line-height: 1.65;">
                                Renungan dan penjelasan yang sangat mendalam tentang Sakramen Ekaristi. Sangat bermanfaat sebagai bahan katekese bersama keluarga di lingkungan KUB.
                            </p>

                            <!-- Nested Replies for Comment 1 -->
                            <div class="replies-container" id="replies-1" style="margin-top: 15px; padding-left: 18px; border-left: 3px solid var(--primary-orange, #ff9800); display: flex; flex-direction: column; gap: 12px;">
                                <div class="reply-item" style="background: #ffffff; border-radius: 10px; padding: 14px 18px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-orange, #ff9800), #e65100); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.78rem; flex-shrink: 0;">
                                            AD
                                        </div>
                                        <div>
                                            <h6 style="margin: 0; font-size: 0.88rem; font-weight: 700; color: #1e293b;">
                                                Admin Paroki <span style="background: rgba(0, 137, 123, 0.12); color: var(--primary-teal, #00897b); font-size: 10px; padding: 2px 8px; border-radius: 10px; margin-left: 4px; font-weight: 700;">Pengelola</span>
                                            </h6>
                                            <span style="font-size: 0.72rem; color: #94a3b8;"><i class="far fa-clock me-1"></i> 1 jam yang lalu</span>
                                        </div>
                                    </div>
                                    <p style="margin: 0; color: #475569; font-size: 0.85rem; line-height: 1.55;">
                                        <span style="color: var(--primary-teal, #00897b); font-weight: 600;">@Yohanes Maria</span> Amin, terima kasih atas tanggapan positifnya. Semoga berkat Ekaristi selalu menguatkan persekutuan iman kita.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Comment 2 -->
                        <div class="comment-item" id="comment-2" style="background: #f8fafc; border-radius: 12px; padding: 20px 22px; border: 1px solid #e2e8f0; transition: all 0.2s;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #4338ca); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(99,102,241,0.25); flex-shrink: 0;">
                                        TA
                                    </div>
                                    <div>
                                        <h5 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">Theresia Avila</h5>
                                        <span style="font-size: 0.75rem; color: #94a3b8;"><i class="far fa-clock me-1"></i> 4 jam yang lalu</span>
                                    </div>
                                </div>
                                <button type="button" class="reply-btn" onclick="openReplyForm('2', 'Theresia Avila')" style="background: white; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 14px; font-size: 0.78rem; font-weight: 600; color: var(--primary-teal, #00897b); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-reply"></i> Balas
                                </button>
                            </div>
                            <p style="margin: 0; color: #475569; font-size: 0.9rem; line-height: 1.65;">
                                Informasi dan warta yang sangat mendidik. Mohon jadwal katekese persiapan komuni pertama juga diupdate di portal ini ya. Terima kasih!
                            </p>

                            <!-- Nested Replies for Comment 2 -->
                            <div class="replies-container" id="replies-2" style="margin-top: 15px; padding-left: 18px; border-left: 3px solid var(--primary-orange, #ff9800); display: flex; flex-direction: column; gap: 12px;"></div>
                        </div>

                    </div>

                    <!-- Formulir Tulis Komentar / Balas Komentar -->
                    <div id="commentFormBox" style="background: #f8fafc; border-radius: 14px; padding: 25px 28px; border: 1px solid #e2e8f0;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 8px;">
                            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                <i class="far fa-comment-dots" style="color: var(--primary-orange, #ff9800); font-size: 1.2rem;"></i> 
                                <span id="formTitle">Tinggalkan Komentar</span>
                            </h4>
                            <div id="replyBadge" style="display: none; align-items: center; gap: 8px; background: rgba(255, 152, 0, 0.15); padding: 4px 14px; border-radius: 20px; font-size: 0.78rem; color: #c2410c; font-weight: 600;">
                                <span>Membalas: <strong id="replyTargetName"></strong></span>
                                <button type="button" onclick="cancelReply()" style="background: none; border: none; color: #991b1b; cursor: pointer; font-size: 1rem; padding: 0 0 0 4px; line-height: 1; font-weight: bold;" title="Batal membalas">&times;</button>
                            </div>
                        </div>

                        <form id="commentForm" onsubmit="handleCommentSubmit(event)">
                            <input type="hidden" id="replyParentId" value="">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div>
                                    <label for="commentName" style="display: block; font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Nama Lengkap <span style="color: #ef4444;">*</span></label>
                                    <input type="text" id="commentName" class="form-control" placeholder="Contoh: Maria Goretti" required style="border-radius: 10px; border: 1px solid #cbd5e1; padding: 10px 14px; font-size: 0.88rem; width: 100%;">
                                </div>
                                <div>
                                    <label for="commentEmail" style="display: block; font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Email (Opsional)</label>
                                    <input type="email" id="commentEmail" class="form-control" placeholder="nama@email.com" style="border-radius: 10px; border: 1px solid #cbd5e1; padding: 10px 14px; font-size: 0.88rem; width: 100%;">
                                </div>
                            </div>

                            <div style="margin-bottom: 18px;">
                                <label for="commentMessage" style="display: block; font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Pesan Komentar <span style="color: #ef4444;">*</span></label>
                                <textarea id="commentMessage" class="form-control" rows="4" placeholder="Tuliskan komentar atau tanggapan Anda di sini..." required style="border-radius: 10px; border: 1px solid #cbd5e1; padding: 12px 14px; font-size: 0.88rem; width: 100%; resize: vertical;"></textarea>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                                <span style="font-size: 0.78rem; color: #94a3b8;">
                                    <i class="fas fa-shield-alt text-success me-1"></i> Komentar santun &amp; saling membangun.
                                </span>
                                <button type="submit" id="btnSubmitComment" class="btn" style="background: linear-gradient(135deg, var(--primary-teal, #00897b), #004d40); color: white; border-radius: 25px; padding: 10px 26px; font-size: 0.88rem; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(0,137,123,0.3); transition: all 0.3s; cursor: pointer;">
                                    <i class="fas fa-paper-plane"></i> Kirim Komentar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Script Komentar & Balasan Interaktif -->
                <script>
                    var articleSlug = "{{ $item->slug }}";
                    var storageKey = "paroki_comments_" + articleSlug;

                    function getInitials(name) {
                        if (!name) return "U";
                        var parts = name.trim().split(" ");
                        if (parts.length >= 2) {
                            return (parts[0][0] + parts[1][0]).toUpperCase();
                        }
                        return name.substring(0, 2).toUpperCase();
                    }

                    function getRandomGradient() {
                        var gradients = [
                            "linear-gradient(135deg, #00897b, #004d40)",
                            "linear-gradient(135deg, #ff9800, #e65100)",
                            "linear-gradient(135deg, #6366f1, #4338ca)",
                            "linear-gradient(135deg, #0284c7, #0369a1)",
                            "linear-gradient(135deg, #ec4899, #be185d)"
                        ];
                        return gradients[Math.floor(Math.random() * gradients.length)];
                    }

                    function openReplyForm(parentId, targetName) {
                        document.getElementById('replyParentId').value = parentId;
                        document.getElementById('replyTargetName').textContent = '@' + targetName;
                        document.getElementById('replyBadge').style.display = 'inline-flex';
                        document.getElementById('formTitle').textContent = 'Balas Komentar @' + targetName;
                        document.getElementById('commentMessage').placeholder = 'Tuliskan balasan Anda untuk @' + targetName + '...';

                        var formBox = document.getElementById('commentFormBox');
                        if (formBox) {
                            formBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        setTimeout(function() {
                            document.getElementById('commentMessage').focus();
                        }, 400);
                    }

                    function cancelReply() {
                        document.getElementById('replyParentId').value = '';
                        document.getElementById('replyBadge').style.display = 'none';
                        document.getElementById('formTitle').textContent = 'Tinggalkan Komentar';
                        document.getElementById('commentMessage').placeholder = 'Tuliskan komentar atau tanggapan Anda di sini...';
                    }

                    function updateCommentCount() {
                        var totalComments = document.querySelectorAll('.comment-item').length;
                        var totalReplies = document.querySelectorAll('.reply-item').length;
                        var countEl = document.getElementById('commentCount');
                        if (countEl) {
                            countEl.textContent = totalComments + totalReplies;
                        }
                    }

                    function loadStoredComments() {
                        try {
                            var data = localStorage.getItem(storageKey);
                            if (!data) return;
                            var items = JSON.parse(data);
                            if (!Array.isArray(items)) return;

                            items.forEach(function(c) {
                                if (c.parentId) {
                                    addReplyToDOM(c.parentId, c.name, c.message, c.time || 'Baru saja', false);
                                } else {
                                    addCommentToDOM(c.id, c.name, c.message, c.time || 'Baru saja', false);
                                }
                            });
                            updateCommentCount();
                        } catch(e) {}
                    }

                    function saveCommentToStorage(item) {
                        try {
                            var data = localStorage.getItem(storageKey);
                            var items = data ? JSON.parse(data) : [];
                            items.push(item);
                            localStorage.setItem(storageKey, JSON.stringify(items));
                        } catch(e) {}
                    }

                    function addCommentToDOM(id, name, message, time, isNew) {
                        var list = document.getElementById('commentsList');
                        if (!list) return;

                        var initials = getInitials(name);
                        var bg = getRandomGradient();

                        var div = document.createElement('div');
                        div.className = 'comment-item';
                        div.id = 'comment-' + id;
                        div.style.background = '#f8fafc';
                        div.style.borderRadius = '12px';
                        div.style.padding = '20px 22px';
                        div.style.border = '1px solid #e2e8f0';
                        div.style.transition = 'all 0.2s';

                        div.innerHTML = 
                            '<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">' +
                                '<div style="display: flex; align-items: center; gap: 12px;">' +
                                    '<div style="width: 42px; height: 42px; border-radius: 50%; background: ' + bg + '; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(0,0,0,0.15); flex-shrink: 0;">' +
                                        initials +
                                    '</div>' +
                                    '<div>' +
                                        '<h5 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">' + escapeHtml(name) + '</h5>' +
                                        '<span style="font-size: 0.75rem; color: #94a3b8;"><i class="far fa-clock me-1"></i> ' + time + '</span>' +
                                    '</div>' +
                                '</div>' +
                                '<button type="button" class="reply-btn" onclick="openReplyForm(\'' + id + '\', \'' + escapeHtml(name) + '\')" style="background: white; border: 1px solid #cbd5e1; border-radius: 20px; padding: 4px 14px; font-size: 0.78rem; font-weight: 600; color: var(--primary-teal, #00897b); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px;">' +
                                    '<i class="fas fa-reply"></i> Balas' +
                                '</button>' +
                            '</div>' +
                            '<p style="margin: 0; color: #475569; font-size: 0.9rem; line-height: 1.65;">' +
                                escapeHtml(message) +
                            '</p>' +
                            '<div class="replies-container" id="replies-' + id + '" style="margin-top: 15px; padding-left: 18px; border-left: 3px solid var(--primary-orange, #ff9800); display: flex; flex-direction: column; gap: 12px;"></div>';

                        list.appendChild(div);

                        if (isNew) {
                            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }

                    function addReplyToDOM(parentId, name, message, time, isNew) {
                        var container = document.getElementById('replies-' + parentId);
                        if (!container) return;

                        var initials = getInitials(name);
                        var bg = getRandomGradient();

                        var div = document.createElement('div');
                        div.className = 'reply-item';
                        div.style.background = '#ffffff';
                        div.style.borderRadius = '10px';
                        div.style.padding = '14px 18px';
                        div.style.border = '1px solid #e2e8f0';

                        div.innerHTML = 
                            '<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">' +
                                '<div style="width: 32px; height: 32px; border-radius: 50%; background: ' + bg + '; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.78rem; flex-shrink: 0;">' +
                                    initials +
                                '</div>' +
                                '<div>' +
                                    '<h6 style="margin: 0; font-size: 0.88rem; font-weight: 700; color: #1e293b;">' +
                                        escapeHtml(name) +
                                    '</h6>' +
                                    '<span style="font-size: 0.72rem; color: #94a3b8;"><i class="far fa-clock me-1"></i> ' + time + '</span>' +
                                '</div>' +
                            '</div>' +
                            '<p style="margin: 0; color: #475569; font-size: 0.85rem; line-height: 1.55;">' +
                                escapeHtml(message) +
                            '</p>';

                        container.appendChild(div);

                        if (isNew) {
                            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }

                    function escapeHtml(text) {
                        var div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    }

                    function handleCommentSubmit(e) {
                        e.preventDefault();
                        var nameInput = document.getElementById('commentName');
                        var emailInput = document.getElementById('commentEmail');
                        var messageInput = document.getElementById('commentMessage');
                        var parentIdInput = document.getElementById('replyParentId');

                        var name = nameInput.value.trim();
                        var email = emailInput.value.trim();
                        var message = messageInput.value.trim();
                        var parentId = parentIdInput.value.trim();

                        if (!name || !message) return;

                        var newId = 'c_' + Date.now();
                        var item = {
                            id: newId,
                            parentId: parentId || null,
                            name: name,
                            email: email,
                            message: message,
                            time: 'Baru saja'
                        };

                        if (parentId) {
                            addReplyToDOM(parentId, name, message, 'Baru saja', true);
                        } else {
                            addCommentToDOM(newId, name, message, 'Baru saja', true);
                        }

                        saveCommentToStorage(item);
                        updateCommentCount();

                        // Reset form
                        messageInput.value = '';
                        cancelReply();

                        // Success notification
                        var btn = document.getElementById('btnSubmitComment');
                        var origHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="fas fa-check"></i> Komentar Terkirim!';
                        btn.style.background = '#10b981';
                        setTimeout(function() {
                            btn.innerHTML = origHtml;
                            btn.style.background = 'linear-gradient(135deg, var(--primary-teal, #00897b), #004d40)';
                        }, 2000);
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        loadStoredComments();
                        updateCommentCount();
                    });
                </script>
            </div>

            <!-- Right: Sidebar Column (Complete Paroki Widgets) -->
            <div style="display: flex; flex-direction: column; gap: 24px;">

                <!-- 1. WIDGET PENCARIAN -->
                <div style="background: #ffffff; padding: 24px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-top: 4px solid var(--primary-orange, #ff9800);">
                    <h4 style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 1.1rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-search" style="color: var(--primary-orange, #ff9800);"></i> Pencarian
                    </h4>
                    <form action="/berita" method="GET">
                        <div style="position: relative;">
                            <input type="text" name="search" placeholder="Cari berita atau artikel..." style="width: 100%; padding: 11px 44px 11px 16px; border-radius: 25px; border: 1px solid #cbd5e1; font-size: 0.88rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-teal, #00897b)'" onblur="this.style.borderColor='#cbd5e1'">
                            <button type="submit" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border-radius: 50%; background: var(--primary-teal, #00897b); color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; transition: background 0.2s;" onmouseover="this.style.background='var(--primary-orange, #ff9800)'" onmouseout="this.style.background='var(--primary-teal, #00897b)'">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. WIDGET KATEGORI -->
                <div style="background: #ffffff; padding: 24px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 4px solid var(--primary-teal, #00897b);">
                    <h4 style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 1.1rem; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-folder-open" style="color: var(--primary-orange, #ff9800);"></i> Kategori
                    </h4>
                    @if(isset($categories) && $categories->isNotEmpty())
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                            @foreach($categories as $cat)
                                <li>
                                    <a href="/berita?category={{ urlencode($cat->kategori) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #334155; font-size: 0.88rem; font-weight: 500; background: #f8fafc; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,137,123,0.08)'; this.style.color='var(--primary-teal, #00897b)';" onmouseout="this.style.background='#f8fafc'; this.style.color='#334155';">
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-angle-right" style="color: var(--primary-orange, #ff9800); font-size: 0.8rem;"></i>
                                            {{ $cat->kategori }}
                                        </span>
                                        <span style="background: rgba(0,137,123,0.12); color: var(--primary-teal, #00897b); padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">
                                            {{ $cat->total }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">Belum ada kategori</p>
                    @endif
                </div>

                <!-- 3. WIDGET BERITA TERBARU -->
                <div style="background: #ffffff; padding: 24px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 4px solid var(--primary-orange, #ff9800);">
                    <h4 style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 1.1rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="far fa-newspaper" style="color: var(--primary-orange, #ff9800);"></i> Berita Terbaru
                    </h4>
                    @if(isset($recentNews) && $recentNews->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            @foreach($recentNews as $rn)
                                @php
                                    $rnDate = $rn->tanggal_publish ?? $rn->created_at ?? now();
                                    $rnUrl = strtolower($rn->tipe ?? '') === 'artikel' ? '/artikel/' . $rn->slug : '/berita/' . $rn->slug;
                                @endphp
                                <div style="background: #f8fafc; border-radius: 10px; padding: 12px 14px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 3px 10px rgba(0,0,0,0.05)'" onmouseout="this.style.boxShadow='none'">
                                    <a href="{{ $rnUrl }}" style="text-decoration: none;" onmouseover="this.querySelector('h5').style.color='var(--primary-teal, #00897b)'" onmouseout="this.querySelector('h5').style.color='#1e293b'">
                                        <h5 style="color: #1e293b; font-weight: 700; font-size: 0.9rem; line-height: 1.45; margin: 0 0 6px; transition: color 0.2s; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $rn->judul }}
                                        </h5>
                                    </a>
                                    <div style="font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 5px;">
                                        <i class="far fa-calendar-alt" style="color: var(--primary-orange, #ff9800);"></i> 
                                        {{ \Carbon\Carbon::parse($rnDate)->translatedFormat('d M Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">Belum ada berita terbaru</p>
                    @endif
                </div>

                <!-- 4. WIDGET ARSIP BERITA -->
                <div style="background: #ffffff; padding: 24px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 4px solid var(--primary-teal, #00897b);">
                    <h4 style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 1.1rem; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                        <i class="far fa-calendar-check" style="color: var(--primary-orange, #ff9800);"></i> Arsip Berita
                    </h4>
                    @if(isset($archive) && $archive->isNotEmpty())
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;">
                            @foreach($archive as $arc)
                                <li>
                                    <a href="/berita?month={{ $arc->month_key }}" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #334155; font-size: 0.88rem; font-weight: 500; background: #f8fafc; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,137,123,0.08)'; this.style.color='var(--primary-teal, #00897b)';" onmouseout="this.style.background='#f8fafc'; this.style.color='#334155';">
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <i class="far fa-calendar-alt" style="color: var(--primary-orange, #ff9800); font-size: 0.8rem;"></i>
                                            {{ $arc->label }}
                                        </span>
                                        <span style="background: rgba(245,158,11,0.15); color: #d97706; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">
                                            {{ $arc->total }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">Belum ada arsip</p>
                    @endif
                </div>

                <!-- 5. WIDGET TAGS -->
                <div style="background: #ffffff; padding: 24px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 4px solid var(--primary-orange, #ff9800);">
                    <h4 style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 1.1rem; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-tags" style="color: var(--primary-orange, #ff9800);"></i> Tags
                    </h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @php
                            $defaultTags = ['kegiatan', 'misa', 'paroki', 'pengumuman', 'sakramen', 'orangtua'];
                            $displayTags = !empty($tags) && count($tags) > 0 ? $tags : $defaultTags;
                        @endphp
                        @foreach($displayTags as $tag)
                            <a href="/berita?tag={{ urlencode(trim($tag)) }}" style="background: #f1f5f9; color: #334155; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-decoration: none; border: 1px solid #e2e8f0; transition: all 0.2s;" onmouseover="this.style.background='var(--primary-teal, #00897b)'; this.style.color='#ffffff'; this.style.borderColor='var(--primary-teal, #00897b)';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#334155'; this.style.borderColor='#e2e8f0';">
                                #{{ trim($tag) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- 6. WIDGET IKUTI KAMI -->
                <div style="background: #ffffff; padding: 24px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-top: 4px solid var(--primary-teal, #00897b);">
                    <h4 style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 1.1rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-share-nodes" style="color: var(--primary-orange, #ff9800);"></i> Ikuti Kami
                    </h4>
                    <p style="font-size: 0.84rem; color: #64748b; margin-bottom: 16px; line-height: 1.5;">
                        Terhubung bersama komunitas Paroki melalui kanal media sosial resmi kami:
                    </p>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <a href="https://facebook.com" target="_blank" rel="noopener" style="width: 38px; height: 38px; border-radius: 50%; background: #1877f2; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" style="width: 38px; height: 38px; border-radius: 50%; background: #ff0000; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener" style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp ?? $telepon ?? '6281234567890') }}" target="_blank" rel="noopener" style="width: 38px; height: 38px; border-radius: 50%; background: #25d366; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- 7. WIDGET KATA SAMBUTAN PASTOR PAROKI -->
                @php
                    $imamWidgetImage = file_exists(public_path('assets/frontend/siparoki/images/default-pastor.jpg'))
                        ? asset('assets/frontend/siparoki/images/default-pastor.jpg')
                        : (file_exists(public_path('assets/frontend/siparoki/images/default-principal.jpg'))
                            ? asset('assets/frontend/siparoki/images/default-principal.jpg')
                            : asset('images/pastor-avatar.svg'));
                @endphp
                <div style="background: #ffffff; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #eef2f6;">
                    <div style="background: #ff7a00; padding: 24px 20px 0; text-align: center; position: relative;">
                        <span style="position: absolute; top: 12px; left: 12px; background: rgba(0,0,0,0.25); color: #ffffff; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.5px;">KATA SAMBUTAN</span>
                        <img src="{{ !empty($pastor_foto) ? $pastor_foto : $imamWidgetImage }}" alt="Pastor Paroki" style="width: 140px; height: 140px; object-fit: cover; display: block; margin: 10px auto 0;">
                    </div>
                    <div style="padding: 22px 20px; text-align: center;">
                        <h5 style="font-weight: 800; color: #0c4a6e; font-size: 1.05rem; margin-bottom: 4px;">Pastor Paroki</h5>
                        <p style="font-size: 0.82rem; font-weight: 700; color: var(--primary-teal, #00897b); margin-bottom: 12px;">Pastor Paroki {{ $globalNamaParoki ?? 'SIPAROKI' }}</p>
                        <p style="font-size: 0.84rem; color: #64748b; line-height: 1.55; font-style: italic; margin-bottom: 18px;">
                            "Salve, Salam Sehat dan Berkah Dalem. Selamat Datang di Website Resmi {{ $globalNamaParoki ?? 'SIPAROKI' }}."
                        </p>
                        <a href="/sambutan" style="display: inline-flex; align-items: center; gap: 6px; background: #0c4a6e; color: #ffffff; padding: 8px 22px; border-radius: 20px; font-weight: 700; font-size: 0.82rem; text-decoration: none; box-shadow: 0 4px 12px rgba(12,74,110,0.2); transition: all 0.2s;" onmouseover="this.style.background='#075985'" onmouseout="this.style.background='#0c4a6e'">
                            Baca Selengkapnya <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
