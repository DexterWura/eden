@extends('layouts.admin')

@section('title', 'Add ad')

@section('content')
    <h1>Add ad</h1>
    <form method="POST" action="{{ route('admin.ads.store') }}" class="form-max-600">
        @csrf
        <div class="form-group"><label>Slot *</label><select name="slot" required><option value="above_fold">Above fold</option><option value="in_feed">In-feed</option><option value="sidebar">Sidebar</option><option value="in_content">In-content</option></select></div>
        <div class="form-group"><label>Type *</label><select name="type" required><option value="adsense">AdSense</option><option value="zimadsense">ZimAdsense</option><option value="custom">Custom</option></select></div>
        <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ old('name') }}"></div>
        <div class="form-group"><label>Width (px, ZimAdsense)</label><input type="number" name="width" value="{{ old('width') }}" min="1"></div>
        <div class="form-group"><label>Height (px, ZimAdsense)</label><input type="number" name="height" value="{{ old('height') }}" min="1"></div>
        <div class="form-group"><label>Content (script/HTML)</label><textarea name="content" rows="6">{{ old('content') }}</textarea></div>
        <div class="form-group"><label>AdSense client (override)</label><input type="text" name="adsense_client" value="{{ old('adsense_client') }}"></div>
        <div class="form-group"><label>AdSense slot ID</label><input type="text" name="adsense_slot" value="{{ old('adsense_slot') }}"></div>
        <div class="form-group"><label>Expires at</label><input type="date" name="expires_at" value="{{ old('expires_at') }}"></div>
        <div class="form-group"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label></div>
        <button type="submit" class="btn">Create</button>
    </form>
    <p class="back-link"><a href="{{ route('admin.ads.index') }}" class="btn">Back</a></p>
@endsection
