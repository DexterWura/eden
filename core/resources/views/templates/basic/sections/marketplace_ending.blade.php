@php
    $content = getContent('marketplace_ending.content', true);
    if(!@$content->data_values->status) return;
    
    $limit = @$content->data_values->limit ?? 6;
    $endingListings = \App\Models\Listing::active()
        ->where('sale_type', 'auction')
        ->where('auction_end', '>', now())
        ->where('auction_end', '<', now()->addDays(3))
        ->with(['seller', 'primaryImage', 'listingCategory'])
        ->orderBy('auction_end', 'asc')
        ->take($limit)
        ->get();
@endphp

@if($endingListings->count() > 0)
<section class="marketplace-section py-5 bg--light">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="section-title mb-1">
                    <i class="las la-clock text-danger"></i>
                    {{ __(@$content->data_values->heading) }}
                </h3>
                <p class="section-subtitle text-muted mb-0">
                    {{ __(@$content->data_values->subheading) }}
                </p>
            </div>
            <a href="{{ route('marketplace.auctions') }}" class="btn btn-outline-danger btn-sm">
                @lang('View All Auctions') <i class="las la-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            @foreach($endingListings as $listing)
                @include('templates.basic.partials.listing_card', ['listing' => $listing])
            @endforeach
        </div>
    </div>
</section>
@endif

