@extends('layouts.app')

@section('title', $category->name . ' - Startups')

@section('content')
    <h1>{{ $category->name }}</h1>
    <div class="category-grid">
        @foreach($startups as $index => $s)
            @if($index > 0 && $index % 5 === 0 && ad_slot('in_feed'))
                <div class="ad-slot">{!! ad_slot('in_feed') !!}</div>
            @endif
            <a href="{{ route('startups.show', $s->slug) }}" class="card card-link">
                <h3 class="card-title">{{ $s->name }}</h3>
                <p class="card-excerpt">{{ Str::limit(strip_tags($s->description), 120) }}</p>
                @if($s->mrr)<span class="card-mrr">MRR: ${{ number_format($s->mrr) }}</span>@endif
            </a>
        @endforeach
    </div>
    {{ $startups->links() }}
@endsection
