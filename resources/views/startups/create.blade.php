@extends('layouts.app')

@section('title', 'Submit startup')

@section('content')
    <h1>Submit startup</h1>
    <form method="POST" action="{{ route('startups.store') }}" class="form-max">
        @csrf
        <div class="form-group">
            <label>Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5">{{ old('description') }}</textarea>
        </div>
        <div class="form-group">
            <label>Website URL</label>
            <input type="url" name="url" value="{{ old('url') }}">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id">
                <option value="">—</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Founder</label>
            <input type="text" name="founder" value="{{ old('founder') }}">
        </div>
        <div class="form-group">
            <label>Tags (comma-separated, optional)</label>
            <input type="text" name="tags" value="{{ old('tags') }}" placeholder="SaaS, Fintech">
        </div>
        <div class="form-group">
            <label>MRR (optional)</label>
            <input type="number" name="mrr" value="{{ old('mrr') }}" min="0" step="0.01">
        </div>
        <div class="form-group">
            <label>ARR (optional)</label>
            <input type="number" name="arr" value="{{ old('arr') }}" min="0" step="0.01">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_for_sale" value="1" {{ old('is_for_sale') ? 'checked' : '' }}> For sale</label>
        </div>
        <button type="submit" class="btn">Submit for approval</button>
    </form>
@endsection
