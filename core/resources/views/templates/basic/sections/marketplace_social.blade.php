@php
    $content = getContent('marketplace_social.content', true);
    if(!@$content->data_values->status) return;
    
    $limit = @$content->data_values->limit ?? 4;
    $socialListings = \App\Models\Listing::active()
        ->where('business_type', 'social_media_account')
        ->with(['seller', 'primaryImage', 'listingCategory'])
        ->latest('approved_at')
        ->take($limit)
        ->get();
@endphp

@if($socialListings->count() > 0)
<section class="marketplace-section py-5 bg--light">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="section-title mb-1">
                    <i class="las la-share-alt text-primary"></i>
                    {{ __(@$content->data_values->heading) }}
                </h3>
                <p class="section-subtitle text-muted mb-0">
                    {{ __(@$content->data_values->subheading) }}
                </p>
            </div>
            <a href="{{ route('marketplace.type', 'social_media_account') }}" class="btn btn-outline-primary btn-sm">
                @lang('View All Social Accounts') <i class="las la-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            @foreach($socialListings as $listing)
                @include('templates.basic.partials.listing_card', ['listing' => $listing])
            @endforeach
        </div>
    </div>
</section>
@endif

