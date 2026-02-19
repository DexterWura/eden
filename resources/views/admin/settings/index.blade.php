@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <h1>Settings</h1>
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="form-max">
        @csrf
        @method('PUT')
        <div class="form-group"><label>Site name *</label><input type="text" name="site_name" value="{{ old('site_name', $site_name) }}" required></div>
        <div class="form-group"><label>App URL *</label><input type="url" name="app_url" value="{{ old('app_url', $app_url) }}" required></div>
        <div class="form-group"><label>Timezone *</label><input type="text" name="timezone" value="{{ old('timezone', $timezone) }}" required></div>
        <div class="form-group"><label>Logo (upload)</label><input type="file" name="site_logo" accept="image/*"></div>
        @if($site_logo)<p>Current: <img src="{{ $site_logo }}" alt="" class="settings-logo"></p>@endif
        <div class="form-group"><label>AdSense client ID (global)</label><input type="text" name="adsense_client_id" value="{{ old('adsense_client_id', $adsense_client_id) }}"></div>
        <div class="form-group"><label>Site theme</label><select name="theme">@foreach($themes as $id => $label)<option value="{{ $id }}" {{ old('theme', $theme) === $id ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
        <button type="submit" class="btn">Save</button>
    </form>
@endsection
