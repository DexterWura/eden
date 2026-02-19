@extends('layouts.app')

@section('title', 'Blog')

@section('content')
    <h1>Blog</h1>
    <p>Latest posts from our community.</p>

    @if($posts->isEmpty())
        <p>No posts yet. Check back soon.</p>
    @else
        <ul class="blog-list">
            @foreach($posts as $post)
                <li>
                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                    <span class="blog-meta">{{ $post->published_at?->format('F j, Y') }}</span>
                    @if($post->meta_description)
                        <p class="blog-excerpt">{{ Str::limit($post->meta_description, 160) }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
        {{ $posts->links() }}
    @endif
@endsection
