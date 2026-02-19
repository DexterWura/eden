@extends('layouts.app')

@section('title', app(\App\Services\SettingService::class)->get('site_name', config('app.name')))

@section('content')
    <div class="hero">
        <h1 class="hero-title">{{ app(\App\Services\SettingService::class)->get('site_name', config('app.name')) }}</h1>
        <p class="hero-sub">Tech startup directory</p>
        <a href="{{ route('startups.create') }}" class="btn btn-gold hero-cta">Submit startup</a>
    </div>
    @if(ad_slot('above_fold'))
        <div class="ad-slot">{!! ad_slot('above_fold') !!}</div>
    @endif
    <div class="featured-grid">
        @forelse($featured as $s)
            <a href="{{ route('startups.show', $s->slug) }}" class="card card-link">
                <h3 class="card-title">{{ $s->name }}</h3>
                <p class="card-excerpt">{{ Str::limit(strip_tags($s->description), 100) }}</p>
                @if($s->category)<span class="badge badge-new">{{ $s->category->name }}</span>@endif
            </a>
        @empty
            <p class="featured-empty">No featured startups yet.</p>
        @endforelse
    </div>
    <p class="section-cta"><a href="{{ route('startups.index') }}" class="btn">View all startups</a></p>
    <h2 class="section-title">Categories</h2>
    <div class="categories-wrap">
        @foreach($categories as $c)
            <a href="{{ route('category.show', $c->slug) }}" class="badge badge-new">{{ $c->name }}</a>
        @endforeach
    </div>
@endsection
