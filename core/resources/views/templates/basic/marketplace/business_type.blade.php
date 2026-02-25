@extends($activeTemplate . 'layouts.frontend')
@section('content')
<!-- Categories -->
@if($categories->count() > 0)
<section class="py-4 bg-light">
    <div class="container">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="{{ route('marketplace.type', $type) }}" class="btn btn-sm {{ !request('category') ? 'btn--base' : 'btn-outline-secondary' }}">
                @lang('All')
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('marketplace.category', $cat->slug) }}" class="btn btn-sm btn-outline-secondary">
                    {{ $cat->name }} ({{ $cat->listings_count }})
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-5">
    <div class="container">
        <!-- Results Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="mb-0 text-muted">
                {{ $listings->total() }} @lang('listings found')
            </p>
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted me-2">@lang('Sort'):</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" @selected(request('sort') == 'newest' || !request('sort'))>@lang('Newest')</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" @selected(request('sort') == 'price_low')>@lang('Price: Low')</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" @selected(request('sort') == 'price_high')>@lang('Price: High')</option>
                </select>
            </div>
        </div>
        
        <!-- Listings Grid -->
        @if($listings->count() > 0)
            <div class="row g-4">
                @foreach($listings as $listing)
                    @include($activeTemplate . 'partials.listing_card', ['listing' => $listing])
                @endforeach
            </div>
            
            @if($listings->hasPages())
                <div class="mt-4">
                    {{ $listings->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <img src="{{ asset('assets/images/empty_list.png') }}" alt="No listings" class="mb-3" style="max-width: 200px;">
                <h5>@lang('No listings in this category')</h5>
                <p class="text-muted">@lang('Check back soon or browse other categories')</p>
                <a href="{{ route('marketplace.browse') }}" class="btn btn--base">
                    <i class="las la-search"></i> @lang('Browse All Listings')
                </a>
            </div>
        @endif
    </div>
</section>
@endsection

@push('style')
<style>
    .bg--gradient {
        background: linear-gradient(135deg, var(--base-color) 0%, #1a1a2e 100%);
    }
</style>
@endpush

