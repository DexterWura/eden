@extends('layouts.admin')

@section('title', 'Startups')

@section('content')
    <h1>Startups</h1>
    <p><a href="{{ route('admin.startups.create') }}" class="btn">Add startup</a></p>
    <form method="GET" class="form-row">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search" class="search-input">
        <button type="submit" class="btn">Search</button>
    </form>
    <table>
        <thead><tr><th>Name</th><th>Category</th><th>Status</th><th>Approved</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($startups as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->category?->name ?? '—' }}</td>
                    <td>{{ $s->status }}</td>
                    <td>{{ $s->approved_at ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('startups.show', $s->slug) }}" class="btn btn-sm">View</a>
                        <a href="{{ route('admin.startups.edit', $s) }}" class="btn btn-sm">Edit</a>
                        @if(!$s->approved_at)
                            <form action="{{ route('admin.startups.approve', $s) }}" method="POST" class="form-inline">@csrf<button type="submit" class="btn btn-sm">Approve</button></form>
                        @endif
                        <form action="{{ route('admin.startups.destroy', $s) }}" method="POST" class="form-inline" onsubmit="return confirm('Delete?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm">Delete</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $startups->withQueryString()->links() }}
@endsection
