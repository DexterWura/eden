@php
    $content = getContent('marketplace_new.content', true);
    if(!@$content->data_values->status) return;
    
    $limit = @$content->data_values->limit ?? 6;
    $newListings = \App\Models\Listing::active()
        ->with(['seller', 'primaryImage', 'listingCategory'])
        ->latest('approved_at')
        ->take($limit)
        ->get();
@endphp

@if($newListings->count() > 0)
<section class="marketplace-section py-5 bg--light">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="section-title mb-1">
                    <i class="las la-plus-circle text-success"></i>
                    {{ __(@$content->data_values->heading) }}
                </h3>
                <p class="section-subtitle text-muted mb-0">
                    {{ __(@$content->data_values->subheading) }}
                </p>
            </div>
            <a href="{{ route('marketplace.browse') }}?sort=newest" class="btn btn-outline-success btn-sm">
                @lang('View All') <i class="las la-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            @foreach($newListings->take(6) as $listing)
                @include('templates.basic.partials.listing_card', ['listing' => $listing])
            @endforeach
        </div>
    </div>
</section>
@endif

