@extends('layouts.app')

@section('title', 'New blog post')

@section('content')
    <h1>New blog post</h1>

    <form method="POST" action="{{ route('my.blog.store') }}" class="form-max-600">
        @csrf
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="255">
            @error('title')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label>Slug (optional — leave blank to generate from title)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" maxlength="255" placeholder="url-friendly-slug">
            @error('slug')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea name="body" rows="12" required>{{ old('body') }}</textarea>
            @error('body')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label>Meta description (SEO, max 320 chars)</label>
            <input type="text" name="meta_description" value="{{ old('meta_description') }}" maxlength="320" placeholder="Short summary for search results">
            @error('meta_description')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label>Link to startup (optional)</label>
            <select name="startup_id">
                <option value="">— None —</option>
                @foreach($startups as $s)
                    <option value="{{ $s->id }}" {{ old('startup_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
        </div>
        <button type="submit" class="btn">Save post</button>
        <a href="{{ route('my.blog.index') }}" class="btn">Cancel</a>
    </form>
@endsection
