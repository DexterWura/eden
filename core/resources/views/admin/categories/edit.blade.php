@extends('layouts.admin')

@section('title', 'Edit category')

@section('content')
    <h1>Edit: {{ $category->name }}</h1>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="form-max-400">
        @csrf
        @method('PUT')
        <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ old('name', $category->name) }}" required></div>
        <div class="form-group"><label>Slug *</label><input type="text" name="slug" value="{{ old('slug', $category->slug) }}" required></div>
        <div class="form-group"><label>Icon path</label><input type="text" name="icon_path" value="{{ old('icon_path', $category->icon_path) }}"></div>
        <button type="submit" class="btn">Save</button>
    </form>
    <p class="back-link"><a href="{{ route('admin.categories.index') }}" class="btn">Back</a></p>
@endsection
