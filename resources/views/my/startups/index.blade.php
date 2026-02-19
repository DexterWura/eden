@extends('layouts.app')

@section('title', 'My startups')

@section('content')
    <h1>My startups</h1>
    <p><a href="{{ route('startups.index') }}" class="btn">Browse all startups</a></p>
    @if($startups->isEmpty())
        <p>You have not claimed or created any startups yet. <a href="{{ route('startups.create') }}">Submit a startup</a> or <a href="{{ route('startups.index') }}">claim an existing listing</a>.</p>
    @else
        <table>
            <thead><tr><th>Name</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($startups as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->category?->name ?? '—' }}</td>
                        <td>{{ $s->status }}</td>
                        <td>
                            <a href="{{ route('startups.show', $s->slug) }}" class="btn btn-sm">View</a>
                            <a href="{{ route('my.startups.edit', $s->slug) }}" class="btn btn-sm">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $startups->links() }}
    @endif
@endsection
