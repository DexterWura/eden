@php
    $content = getContent('marketplace_popular.content', true);
    if(!@$content->data_values->status) return;
    
    $limit = @$content->data_values->limit ?? 6;
    $popularListings = \App\Models\Listing::active()
        ->with(['seller', 'primaryImage', 'listingCategory'])
        ->orderByRaw('(view_count + watchlist_count * 2 + total_bids * 5) DESC')
        ->take($limit)
        ->get();
@endphp

@if($popularListings->count() > 0)
<section class="marketplace-section py-5">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="section-title mb-1">
                    <i class="las la-fire text-danger"></i>
                    {{ __(@$content->data_values->heading) }}
                </h3>
                <p class="section-subtitle text-muted mb-0">
                    {{ __(@$content->data_values->subheading) }}
                </p>
            </div>
            <a href="{{ route('marketplace.browse') }}?sort=popular" class="btn btn-outline-primary btn-sm">
                @lang('View All') <i class="las la-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            @foreach($popularListings->take(6) as $listing)
                @include('templates.basic.partials.listing_card', ['listing' => $listing])
            @endforeach
        </div>
    </div>
</section>
@endif

