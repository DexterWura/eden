@extends('layouts.app')

@section('title', $startup->name . ' - ' . app(\App\Services\SettingService::class)->get('site_name', config('app.name')))

@section('content')
    @push('meta')
        <meta name="description" content="{{ Str::limit(strip_tags($startup->description), 160) }}">
        <link rel="canonical" href="{{ url()->current() }}">
        <script type="application/ld+json">@json([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $startup->name,
            'url' => $startup->url,
            'description' => Str::limit(strip_tags($startup->description), 200),
        ])</script>
    @endpush
    <div class="startup-layout {{ $startup->status === 'wilted' ? 'startup-wilted' : '' }}">
        <div>
            @if($startup->status === 'wilted')
                <p class="alert alert-info">This listing is currently dormant (website may be offline).</p>
            @endif
            <h1>{{ $startup->name }}</h1>
            <div class="startup-badges">
                @if($startup->category)<span class="badge badge-new">{{ $startup->category->name }}</span>@endif
                @if($startup->is_featured)<span class="badge badge-featured">Featured</span>@endif
                @if($startup->is_sponsored ?? false)<span class="badge badge-sponsored">Sponsored</span>@endif
                @if($startup->user_id)<span class="badge badge-verified">Verified</span>@endif
                <span class="badge badge-trending">{{ ucfirst($startup->status) }}</span>
            </div>
            @if($startup->url)
                <p><a href="{{ $startup->url }}" target="_blank" rel="noopener">{{ $startup->url }}</a></p>
            @endif
            @php $descWords = str_word_count(strip_tags($startup->description ?? '')); @endphp
            @if($descWords > 0 && $descWords < 300 && auth()->check() && $startup->user_id === auth()->id())
                <div class="alert alert-info">Add more content to improve visibility (currently {{ $descWords }} words; 300+ recommended). <a href="{{ route('admin.startups.edit', $startup) }}">Edit</a></div>
            @endif
            @php
                $desc = (string) ($startup->description ?? '');
                $paras = preg_split('/\n\s*\n/', $desc, 4);
                $firstBlock = implode("\n\n", array_slice($paras, 0, 2));
                $rest = implode("\n\n", array_slice($paras, 2));
            @endphp
            <div class="startup-description-block">{!! nl2br(e($firstBlock)) !!}</div>
            @if(ad_slot('in_content'))
                <div class="ad-slot ad-slot-in-content">{!! ad_slot('in_content') !!}</div>
            @endif
            @if($rest !== '')
                <div class="startup-description-block">{!! nl2br(e($rest)) !!}</div>
            @endif
            @if($startup->founder)<p><strong>Founder:</strong> {{ $startup->founder }}</p>@endif
            @php
                $platforms = config('social_platforms.platforms', []);
                $founderSocials = array_filter($startup->founder_socials ?? []);
                $startupSocials = array_filter($startup->startup_socials ?? []);
            @endphp
            @if(!empty($founderSocials))
                <p><strong>Connect with founder:</strong>
                    @foreach($founderSocials as $key => $url)
                        @if(!empty($url) && isset($platforms[$key]))
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $platforms[$key]['name'] }}</a>@if(!$loop->last)<span class="social-sep"> · </span>@endif
                        @endif
                    @endforeach
                </p>
            @endif
            @if(!empty($startupSocials))
                <p><strong>Follow:</strong>
                    @foreach($startupSocials as $key => $url)
                        @if(!empty($url) && isset($platforms[$key]))
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $platforms[$key]['name'] }}</a>@if(!$loop->last)<span class="social-sep"> · </span>@endif
                        @endif
                    @endforeach
                </p>
            @endif
            @if($startup->tags)
                <p><strong>Tags:</strong> {{ $startup->tags }}</p>
            @endif
            @if($startup->mrr)<p><strong>MRR:</strong> ${{ number_format($startup->mrr) }}</p>@endif
            @if($startup->arr)<p><strong>ARR:</strong> ${{ number_format($startup->arr) }}</p>@endif
            @if($startup->is_for_sale)
                <p><a href="https://flipit.co.zw" target="_blank" rel="noopener" class="btn btn-gold">For sale (Flipit.co.zw)</a></p>
            @endif
            <p class="startup-votes-row"><strong>{{ $startup->votes_count }}</strong> upvotes</p>
            @include('partials.share-buttons', ['url' => route('startups.show', $startup->slug), 'title' => $startup->name])
            @auth
                @if($startup->user_id !== auth()->id())
                    <form action="{{ route('vote.store', $startup) }}" method="POST" class="form-inline">
                        @csrf
                        <button type="submit" class="btn">Upvote</button>
                    </form>
                @endif
                @if(!$startup->user_id || $startup->user_id !== auth()->id())
                    <a href="{{ route('claim.create', $startup->slug) }}" class="btn btn-gold">Claim this startup</a>
                @elseif($startup->user_id === auth()->id())
                    <a href="{{ route('my.startups.edit', $startup->slug) }}" class="btn">Edit</a>
                @endif
            @else
                <p><a href="{{ route('login') }}">Login</a> to upvote or claim.</p>
            @endauth
        </div>
        <aside>
            @if(ad_slot('sidebar'))
                <div class="ad-slot ad-slot-sidebar">{!! ad_slot('sidebar') !!}</div>
            @endif
        </aside>
    </div>
@endsection
