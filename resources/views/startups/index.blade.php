@extends('layouts.app')

@section('title', 'Startups')

@section('content')
    <h1>Startups</h1>
    <form method="GET" action="{{ route('startups.index') }}" class="filters-bar">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search">
        <select name="category">
            <option value="">All categories</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">All statuses</option>
            <option value="seedling" {{ request('status') == 'seedling' ? 'selected' : '' }}>New</option>
            <option value="sapling" {{ request('status') == 'sapling' ? 'selected' : '' }}>Growing</option>
            <option value="flourishing" {{ request('status') == 'flourishing' ? 'selected' : '' }}>Flourishing</option>
            <option value="wilted" {{ request('status') == 'wilted' ? 'selected' : '' }}>Dormant</option>
        </select>
        <label><input type="checkbox" name="for_sale" value="1" {{ request('for_sale') ? 'checked' : '' }}> For sale</label>
        <button type="submit" class="btn">Filter</button>
    </form>
    @if(ad_slot('above_fold'))
        <div class="ad-slot">{!! ad_slot('above_fold') !!}</div>
    @endif
    <div class="startup-grid">
        @foreach($startups as $index => $s)
            @if($index > 0 && $index % 5 === 0 && ad_slot('in_feed'))
                <div class="ad-slot">{!! ad_slot('in_feed') !!}</div>
            @endif
            <a href="{{ route('startups.show', $s->slug) }}" class="card card-link {{ $s->status === 'wilted' ? 'card-wilted' : '' }}">
                <h3 class="card-title">{{ $s->name }}</h3>
                <p class="card-excerpt">{{ Str::limit(strip_tags($s->description), 120) }}</p>
                <div class="card-meta">
                    @if($s->category)<span class="badge badge-new">{{ $s->category->name }}</span>@endif
                    @if($s->is_featured)<span class="badge badge-featured">Featured</span>@endif
                    @if($s->user_id)<span class="badge badge-verified">Verified</span>@endif
                    @if($s->mrr)<span class="card-mrr">MRR: ${{ number_format($s->mrr) }}</span>@endif
                </div>
                <p class="card-votes"><strong>{{ $s->votes_count ?? $s->votes()->count() }}</strong> upvotes</p>
            </a>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $startups->withQueryString()->links() }}</div>
@endsection
