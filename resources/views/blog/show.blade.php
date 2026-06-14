@extends('layouts.public')

@section('head')
<title>{{ $post['title'] }} | Blog TBA</title>
<meta name="description" content="{{ $post['excerpt'] }}">
<link rel="canonical" href="https://tbasystent.pl/blog/{{ $post['slug'] }}">
<meta property="og:title" content="{{ $post['title'] }}">
<meta property="og:description" content="{{ $post['excerpt'] }}">
<meta property="og:image" content="https://tbasystent.pl/og-image.png">
<meta property="og:url" content="https://tbasystent.pl/blog/{{ $post['slug'] }}">
<meta property="og:type" content="article">
<meta name="robots" content="index, follow">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{ addslashes($post['title']) }}",
  "description": "{{ addslashes($post['excerpt']) }}",
  "datePublished": "{{ $post['date_iso'] }}",
  "author": { "@type": "Organization", "@id": "https://tbasystent.pl/#organization" },
  "publisher": { "@id": "https://tbasystent.pl/#organization" },
  "url": "https://tbasystent.pl/blog/{{ $post['slug'] }}"
}
</script>
<style>
.article-wrap { max-width: 720px; margin: 0 auto; padding: 64px 48px 96px; }
.article-breadcrumb { font-size: 13px; color: rgba(255,255,255,0.3); margin-bottom: 32px; }
.article-breadcrumb a { color: rgba(255,255,255,0.3); text-decoration: none; }
.article-breadcrumb a:hover { color: rgba(255,255,255,0.6); }
.article-tag { font-size: 11px; color: #4ade80; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px; }
.article-wrap h1 { font-size: 42px; font-weight: 900; letter-spacing: -1px; line-height: 1.15; margin-bottom: 16px; }
.article-meta { font-size: 13px; color: rgba(255,255,255,0.3); margin-bottom: 48px; padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.07); }
.article-body h2 { font-size: 24px; font-weight: 800; margin: 40px 0 14px; color: #4ade80; }
.article-body h3 { font-size: 18px; font-weight: 700; margin: 28px 0 10px; color: rgba(255,255,255,0.85); }
.article-body p { font-size: 16px; color: rgba(255,255,255,0.65); line-height: 1.8; margin-bottom: 18px; }
.article-body ul, .article-body ol { font-size: 16px; color: rgba(255,255,255,0.65); line-height: 1.8; margin: 0 0 18px; padding-left: 24px; }
.article-body li { margin-bottom: 6px; }
.article-body strong { color: rgba(255,255,255,0.85); }
.article-cta { background: linear-gradient(135deg, rgba(74,222,128,0.06), rgba(45,27,78,0.1)); border: 1px solid rgba(74,222,128,0.2); border-radius: 14px; padding: 32px; margin: 48px 0; text-align: center; }
.article-cta h3 { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
.article-cta p { font-size: 14px; color: rgba(255,255,255,0.5); margin-bottom: 20px; }
.btn-green { background: #4ade80; color: #0d1117; border-radius: 8px; padding: 12px 24px; font-size: 14px; font-weight: 800; text-decoration: none; display: inline-block; }
.btn-green:hover { background: #22c55e; }
@media (max-width: 768px) {
    .article-wrap { padding: 40px 20px 64px; }
    .article-wrap h1 { font-size: 28px; }
}
</style>
@endsection

@section('content')

<div class="article-wrap">
    <div class="article-breadcrumb">
        <a href="/">Strona główna</a> › <a href="{{ route('blog.index') }}">Blog</a> › {{ $post['category'] }}
    </div>
    <div class="article-tag">{{ $post['category'] }}</div>
    <h1>{{ $post['title'] }}</h1>
    <div class="article-meta">{{ $post['date'] }} · {{ $post['read_time'] }} min czytania</div>

    <div class="article-body">
        {!! $post['body'] !!}
    </div>

    <div class="article-cta">
        <h3>Wypróbuj TBA za darmo</h3>
        <p>Zarządzaj zleceniami, klientami i wycenami — pierwsze 30 dni bezpłatnie.</p>
        <a href="/admin/register" class="btn-green">Zacznij 30 dni za darmo</a>
    </div>
</div>

@include('partials.public-footer')

@endsection
