@extends('layouts.admin')

@section('title', 'Edit ad')

@section('content')
    <h1>Edit ad</h1>
    <form method="POST" action="{{ route('admin.ads.update', $ad) }}" class="form-max-600">
        @csrf
        @method('PUT')
        <div class="form-group"><label>Slot *</label><select name="slot" required>@foreach(['above_fold'=>'Above fold','in_feed'=>'In-feed','sidebar'=>'Sidebar','in_content'=>'In-content'] as $v=>$l)<option value="{{ $v }}" {{ old('slot', $ad->slot) == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="form-group"><label>Type *</label><select name="type" required>@foreach(['adsense'=>'AdSense','zimadsense'=>'ZimAdsense','custom'=>'Custom'] as $v=>$l)<option value="{{ $v }}" {{ old('type', $ad->type) == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ old('name', $ad->name) }}"></div>
        <div class="form-group"><label>Width (px)</label><input type="number" name="width" value="{{ old('width', $ad->width) }}" min="1"></div>
        <div class="form-group"><label>Height (px)</label><input type="number" name="height" value="{{ old('height', $ad->height) }}" min="1"></div>
        <div class="form-group"><label>Content</label><textarea name="content" rows="6">{{ old('content', $ad->content) }}</textarea></div>
        <div class="form-group"><label>AdSense client</label><input type="text" name="adsense_client" value="{{ old('adsense_client', $ad->adsense_client) }}"></div>
        <div class="form-group"><label>AdSense slot ID</label><input type="text" name="adsense_slot" value="{{ old('adsense_slot', $ad->adsense_slot) }}"></div>
        <div class="form-group"><label>Expires at</label><input type="date" name="expires_at" value="{{ old('expires_at', $ad->expires_at?->format('Y-m-d')) }}"></div>
        <div class="form-group"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $ad->is_active) ? 'checked' : '' }}> Active</label></div>
        <button type="submit" class="btn">Save</button>
    </form>
    <p class="back-link"><a href="{{ route('admin.ads.index') }}" class="btn">Back</a></p>
@endsection
