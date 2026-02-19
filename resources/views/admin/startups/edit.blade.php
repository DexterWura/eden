@extends('layouts.admin')

@section('title', 'Edit: ' . $startup->name)

@section('content')
    <h1>Edit: {{ $startup->name }}</h1>
    <form method="POST" action="{{ route('admin.startups.update', $startup) }}" class="form-max">
        @csrf
        @method('PUT')
        <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ old('name', $startup->name) }}" required></div>
        <div class="form-group"><label>Slug *</label><input type="text" name="slug" value="{{ old('slug', $startup->slug) }}" required></div>
        @php $descWords = str_word_count(strip_tags($startup->description ?? '')); @endphp
        @if($descWords > 0 && $descWords < 300)
            <div class="alert alert-info alert-sm">Add more content to improve visibility (currently {{ $descWords }} words; 300+ recommended).</div>
        @endif
        <div class="form-group"><label>Description</label><textarea name="description" rows="5">{{ old('description', $startup->description) }}</textarea></div>
        <div class="form-group"><label>URL</label><input type="url" name="url" value="{{ old('url', $startup->url) }}"></div>
        <div class="form-group"><label>Category</label><select name="category_id"><option value="">—</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id', $startup->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="form-group"><label>Founder</label><input type="text" name="founder" value="{{ old('founder', $startup->founder) }}"></div>
        @if(!empty($platforms))
            <h2 class="section-title-sm">Founder socials</h2>
            @foreach($platforms as $key => $info)
                <div class="form-group">
                    <label>{{ $info['founder_label'] }}</label>
                    <input type="url" name="founder_socials[{{ $key }}]" value="{{ old("founder_socials.{$key}", $startup->founder_socials[$key] ?? '') }}" placeholder="{{ $info['url_placeholder'] ?? '' }}">
                </div>
            @endforeach
            <h2 class="section-title-sm">Startup socials</h2>
            @foreach($platforms as $key => $info)
                <div class="form-group">
                    <label>{{ $info['startup_label'] }}</label>
                    <input type="url" name="startup_socials[{{ $key }}]" value="{{ old("startup_socials.{$key}", $startup->startup_socials[$key] ?? '') }}" placeholder="{{ $info['url_placeholder'] ?? '' }}">
                </div>
            @endforeach
        @endif
        <div class="form-group"><label>Tags (comma-separated)</label><input type="text" name="tags" value="{{ old('tags', $startup->tags) }}" placeholder="SaaS, Fintech"></div>
        <div class="form-group"><label>MRR</label><input type="number" name="mrr" value="{{ old('mrr', $startup->mrr) }}" min="0" step="0.01"></div>
        <div class="form-group"><label>ARR</label><input type="number" name="arr" value="{{ old('arr', $startup->arr) }}" min="0" step="0.01"></div>
        <div class="form-group"><label>Status</label><select name="status">@foreach(['seedling'=>'New','sapling'=>'Growing','flourishing'=>'Flourishing','wilted'=>'Dormant'] as $v=>$l)<option value="{{ $v }}" {{ old('status', $startup->status) == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="form-group"><label><input type="checkbox" name="is_for_sale" value="1" {{ old('is_for_sale', $startup->is_for_sale) ? 'checked' : '' }}> For sale</label></div>
        <div class="form-group"><label><input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $startup->is_featured) ? 'checked' : '' }}> Featured</label></div>
        <button type="submit" class="btn">Save</button>
    </form>
    <p class="back-link"><a href="{{ route('admin.startups.index') }}" class="btn">Back</a></p>
@endsection
