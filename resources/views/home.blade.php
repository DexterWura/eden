@extends('layouts.app')

@section('title', app(\App\Services\SettingService::class)->get('site_name', config('app.name')))

@section('content')
    <section class="hero-section">
        <div class="hero">
            <h1 class="hero-title hero-anim">{{ app(\App\Services\SettingService::class)->get('site_name', config('app.name')) }}</h1>
            <p class="hero-sub hero-anim hero-anim-delay-1">Tech startup directory</p>
            <form method="GET" action="{{ route('startups.index') }}" class="hero-search hero-anim hero-anim-delay-2">
                <input type="search" name="q" class="hero-search-input" placeholder="Search startups by name, description, or tags…" value="{{ request('q') }}" aria-label="Search startups">
                <button type="submit" class="btn btn-gold hero-search-btn">Search</button>
            </form>
            <p class="hero-search-hint hero-anim hero-anim-delay-2">Find startups by name, description, or tags.</p>
            <div class="hero-ctas hero-anim hero-anim-delay-3">
                <a href="{{ route('startups.create') }}" class="btn btn-gold hero-cta">Submit startup</a>
                <a href="{{ route('startups.index') }}" class="btn hero-cta hero-cta-secondary">Browse all</a>
            </div>
        </div>
    </section>
    @if(ad_slot('above_fold'))
        <div class="ad-slot">{!! ad_slot('above_fold') !!}</div>
    @endif
    <h2 class="section-title section-anim">Featured</h2>
    <div class="featured-grid">
        @forelse($featured as $s)
            <a href="{{ route('startups.show', $s->slug) }}" class="card card-link card-anim">
                <h3 class="card-title">{{ $s->name }}</h3>
                <p class="card-excerpt">{{ Str::limit(strip_tags($s->description), 100) }}</p>
                @if($s->category)<span class="badge badge-new">{{ $s->category->name }}</span>@endif
            </a>
        @empty
            <p class="featured-empty">No featured startups yet.</p>
        @endforelse
    </div>
    <p class="section-cta section-anim"><a href="{{ route('startups.index') }}" class="btn">View all startups</a></p>
    <h2 class="section-title section-anim">Categories</h2>
    <div class="categories-wrap categories-anim">
        @foreach($categories as $c)
            <a href="{{ route('category.show', $c->slug) }}" class="badge badge-new">{{ $c->name }}</a>
        @endforeach
    </div>
@endsection
