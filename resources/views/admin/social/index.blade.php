@extends('layouts.admin')

@section('title', 'Social / OAuth')

@section('content')
    <h1>Social / OAuth</h1>
    <p class="page-sub">Configure app credentials for LinkedIn, Facebook, and Instagram. When set, the app can use these to resolve social handles and fetch founder name and other details from profiles.</p>

    <form method="POST" action="{{ route('admin.social.update') }}" class="form-max-600">
        @csrf
        @method('PUT')

        @foreach($platforms as $key => $info)
            <h2 class="section-title-sm">{{ $info['name'] }}</h2>
            <div class="form-group">
                <label>App ID / Client ID</label>
                <input type="text" name="social_{{ $key }}_app_id" value="{{ old("social_{$key}_app_id", $credentials[$key]['app_id'] ?? '') }}" placeholder="From {{ $info['name'] }} developer console" autocomplete="off">
            </div>
            <div class="form-group">
                <label>App Secret / Client Secret</label>
                <input type="password" name="social_{{ $key }}_app_secret" value="" placeholder="Leave blank to keep current" autocomplete="new-password">
            </div>
        @endforeach

        <button type="submit" class="btn">Save</button>
    </form>
@endsection
