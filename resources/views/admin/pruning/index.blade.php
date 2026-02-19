@extends('layouts.admin')

@section('title', 'Pruning')

@section('content')
    <h1>Pruning</h1>
    <p>Find startups by URL pattern or empty description, then delete in bulk.</p>
    <form method="GET" action="{{ route('admin.pruning.index') }}" class="filters-row">
        <div class="form-group form-group-inline"><label>URL pattern (e.g. %spam%)</label><input type="text" name="url_pattern" value="{{ request('url_pattern') }}" placeholder="%pattern%"></div>
        <div class="form-group form-group-inline"><label><input type="checkbox" name="empty_description" value="1" {{ request('empty_description') ? 'checked' : '' }}> Empty description</label></div>
        <button type="submit" class="btn">Search</button>
    </form>
    @if($startups->isNotEmpty())
        <form method="POST" action="{{ route('admin.pruning.destroy') }}">
            @csrf
            <table>
                <thead><tr><th><input type="checkbox" id="selectAll"></th><th>Name</th><th>URL</th><th>Description</th></tr></thead>
                <tbody>
                    @foreach($startups as $s)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $s->id }}" class="row-check"></td>
                            <td>{{ $s->name }}</td>
                            <td>{{ $s->url }}</td>
                            <td>{{ Str::limit($s->description, 50) ?: '(empty)' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-danger btn-danger-mt">Delete selected</button>
        </form>
    @else
        <p>No matching startups.</p>
    @endif
@endsection
