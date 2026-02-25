@php
    $content = getContent('marketplace_domains.content', true);
    if(!@$content->data_values->status) return;
    
    $limit = @$content->data_values->limit ?? 4;
    $domainListings = \App\Models\Listing::active()
        ->where('business_type', 'domain')
        ->with(['seller', 'primaryImage', 'listingCategory'])
        ->latest('approved_at')
        ->take($limit)
        ->get();
@endphp

@if($domainListings->count() > 0)
<section class="marketplace-section py-5">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="section-title mb-1">
                    <i class="las la-globe text--base"></i>
                    {{ __(@$content->data_values->heading) }}
                </h3>
                <p class="section-subtitle text-muted mb-0">
                    {{ __(@$content->data_values->subheading) }}
                </p>
            </div>
            <a href="{{ route('marketplace.type', 'domain') }}" class="btn btn-outline-primary btn-sm">
                @lang('View All Domains') <i class="las la-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            @foreach($domainListings as $listing)
                @include('templates.basic.partials.listing_card', ['listing' => $listing])
            @endforeach
        </div>
    </div>
</section>
@endif

