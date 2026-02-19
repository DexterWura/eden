@extends('layouts.app')

@section('title', $post->title)
@section('meta_description', $post->meta_description ?: Str::limit(strip_tags($post->body), 160))

@push('meta')
    <link rel="canonical" href="{{ route('blog.show', $post->slug) }}">
    <meta property="og:title" content="{{ $post->title }}">
    @if($post->meta_description)
        <meta property="og:description" content="{{ $post->meta_description }}">
    @endif
    <meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="{{ $post->published_at?->toIso8601String() }}">
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->title,
        'description' => $post->meta_description ?: Str::limit(strip_tags($post->body), 160),
        'datePublished' => $post->published_at?->toIso8601String(),
        'url' => route('blog.show', $post->slug),
    ]) !!}</script>
@endpush

@section('content')
    <article class="blog-article">
        <h1>{{ $post->title }}</h1>
        <p class="blog-meta">
            {{ $post->published_at?->format('F j, Y') }}
            @if($post->startup)
                · <a href="{{ route('startups.show', $post->startup->slug) }}">{{ $post->startup->name }}</a>
            @endif
        </p>
        <div class="blog-body">
            {!! nl2br(e($post->body)) !!}
        </div>
    </article>
    <p><a href="{{ route('blog.index') }}">← Back to blog</a></p>
@endsection
