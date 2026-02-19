@extends('layouts.admin')

@section('title', 'Blog posts')

@section('content')
    <h1>Blog posts</h1>
    <p>All blog posts. Admins can edit any post via the Edit link (same as author flow).</p>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Startup</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posts as $post)
                <tr>
                    <td>{{ $post->title }}</td>
                    <td>{{ $post->user?->name ?? '—' }}</td>
                    <td>{{ $post->startup?->name ?? '—' }}</td>
                    <td>{{ $post->status }}</td>
                    <td>{{ $post->updated_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($post->status === 'published')
                            <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-sm">View</a>
                        @endif
                        <a href="{{ route('my.blog.edit', $post->slug) }}" class="btn btn-sm">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $posts->links() }}
@endsection
