@extends('layouts.app')

@section('title', 'My blog')

@section('content')
    <h1>My blog</h1>
    <p><a href="{{ route('my.blog.create') }}" class="btn">New post</a></p>

    @if($posts->isEmpty())
        <p>You have no posts yet. <a href="{{ route('my.blog.create') }}">Write your first post</a>.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->status }}</td>
                        <td>{{ $post->updated_at->format('M j, Y') }}</td>
                        <td>
                            @if($post->status === 'published')
                                <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-sm">View</a>
                            @endif
                            <a href="{{ route('my.blog.edit', $post->slug) }}" class="btn btn-sm">Edit</a>
                            <form action="{{ route('my.blog.destroy', $post) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this post?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $posts->links() }}
    @endif
@endsection
