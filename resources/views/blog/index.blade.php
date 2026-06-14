@extends('layouts.public')

@section('head')
<title>Blog — porady dla małych firm usługowych | TBA</title>
<meta name="description" content="Praktyczne porady dla firm sprzątających, remontowych i usługowych: jak wyceniać zlecenia, zarządzać klientami i rozwijać firmę.">
<link rel="canonical" href="https://tbasystent.pl/blog">
<meta property="og:title" content="Blog TBA — porady dla małych firm usługowych">
<meta property="og:image" content="https://tbasystent.pl/og-image.png">
<meta name="robots" content="index, follow">
<style>
.blog-hero { padding: 72px 48px 56px; text-align: center; }
.blog-hero h1 { font-size: 46px; font-weight: 900; letter-spacing: -1px; margin-bottom: 16px; }
.blog-hero h1 em { font-style: normal; color: #4ade80; }
.blog-hero p { font-size: 17px; color: rgba(255,255,255,0.5); max-width: 460px; margin: 0 auto; line-height: 1.6; }

.blog-list { max-width: 800px; margin: 0 auto; padding: 0 48px 96px; }
.blog-card { display: block; border-bottom: 1px solid rgba(255,255,255,0.06); padding: 32px 0; text-decoration: none; transition: padding-left .2s; }
.blog-card:hover { padding-left: 8px; }
.blog-tag { font-size: 11px; color: #4ade80; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
.blog-card h2 { font-size: 22px; font-weight: 700; color: #fff; margin-bottom: 10px; line-height: 1.3; }
.blog-card p { font-size: 14px; color: rgba(255,255,255,0.45); line-height: 1.65; margin-bottom: 14px; }
.blog-meta { font-size: 12px; color: rgba(255,255,255,0.25); }

@media (max-width: 768px) {
    .blog-hero { padding: 48px 20px 36px; }
    .blog-hero h1 { font-size: 30px; }
    .blog-list { padding: 0 20px 64px; }
}
</style>
@endsection

@section('content')

<section class="blog-hero">
    <div style="font-size: 11px; color: #4ade80; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 16px;">Blog</div>
    <h1>Porady dla <em>małych firm usługowych</em></h1>
    <p>Jak wyceniać zlecenia, zarządzać klientami i rozwijać firmę sprzątającą lub usługową w Polsce.</p>
</section>

<div class="blog-list">
    @foreach ($posts as $post)
    <a href="{{ route('blog.show', $post['slug']) }}" class="blog-card">
        <div class="blog-tag">{{ $post['category'] }}</div>
        <h2>{{ $post['title'] }}</h2>
        <p>{{ $post['excerpt'] }}</p>
        <div class="blog-meta">{{ $post['date'] }} · {{ $post['read_time'] }} min czytania</div>
    </a>
    @endforeach
</div>

@include('partials.public-footer')

@endsection
