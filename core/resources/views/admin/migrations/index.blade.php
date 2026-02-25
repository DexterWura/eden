@extends('layouts.admin')

@section('title', 'Migrations')

@section('content')
    <h1>Migrations</h1>
    @if($hasPending)
        <p class="text-danger">You have pending migrations.</p>
        <form method="POST" action="{{ route('admin.migrations.run') }}" class="form-row">
            @csrf
            <button type="submit" class="btn">Run migrations</button>
        </form>
    @else
        <p class="text-success">Database is up to date.</p>
    @endif
    <h2>Status</h2>
    <pre class="pre-block">{{ $output }}</pre>
@endsection
