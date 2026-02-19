@extends('layouts.admin')

@section('title', 'Submissions')

@section('content')
    <h1>Submissions</h1>
    <table>
        <thead><tr><th>Name</th><th>URL</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($submissions as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->url }}</td>
                    <td>{{ $s->submitted_at?->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.startups.edit', $s) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('admin.startups.approve', $s) }}" method="POST" class="form-inline">@csrf<button type="submit" class="btn btn-sm">Approve</button></form>
                        <form action="{{ route('admin.startups.reject', $s) }}" method="POST" class="form-inline">@csrf<button type="submit" class="btn btn-danger btn-sm">Reject</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $submissions->links() }}
@endsection
