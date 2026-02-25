@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <h1>Categories</h1>
    <p><a href="{{ route('admin.categories.create') }}" class="btn">Add category</a></p>
    <table>
        <thead><tr><th>Name</th><th>Slug</th><th>Startups</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($categories as $c)
                <tr>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->slug }}</td>
                    <td>{{ $c->startups_count ?? 0 }}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $c) }}" method="POST" class="form-inline" onsubmit="return confirm('Delete?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm">Delete</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $categories->links() }}
@endsection
