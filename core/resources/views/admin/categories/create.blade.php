@extends('layouts.admin')

@section('title', 'Add category')

@section('content')
    <h1>Add category</h1>
    <form method="POST" action="{{ route('admin.categories.store') }}" class="form-max-400">
        @csrf
        <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ old('name') }}" required></div>
        <div class="form-group"><label>Slug (optional)</label><input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-generated"></div>
        <div class="form-group"><label>Icon path</label><input type="text" name="icon_path" value="{{ old('icon_path') }}"></div>
        <button type="submit" class="btn">Create</button>
    </form>
    <p class="back-link"><a href="{{ route('admin.categories.index') }}" class="btn">Back</a></p>
@endsection
