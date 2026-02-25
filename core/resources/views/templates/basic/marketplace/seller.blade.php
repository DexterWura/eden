@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Seller Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="avatar bg--base rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 100px; height: 100px;">
                            <span class="text-white" style="font-size: 2.5rem;">
                                {{ strtoupper(substr($seller->username, 0, 1)) }}
                            </span>
                        </div>
                        
                        <h4 class="mb-1">{{ $seller->fullname }}</h4>
                        <p class="text-muted mb-2">{{ '@' . $seller->username }}</p>
                        
                        @if($stats['is_verified'])
                            <span class="badge bg-success mb-3">
                                <i class="las la-check-circle"></i> @lang('Verified Seller')
                            </span>
                        @endif
                        
                        @if($seller->bio)
                            <p class="text-muted small">{{ $seller->bio }}</p>
                        @endif
                        
                        <hr>
                        
                        <div class="row text-center g-2">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <h5 class="mb-0">{{ $stats['total_sales'] }}</h5>
                                    <small class="text-muted">@lang('Sales')</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <h5 class="mb-0">{{ $stats['total_listings'] }}</h5>
                                    <small class="text-muted">@lang('Listings')</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <h5 class="mb-0">
                                        @if($stats['avg_rating'] > 0)
                                            {{ number_format($stats['avg_rating'], 1) }}
                                            <i class="las la-star text-warning"></i>
                                        @else
                                            N/A
                                        @endif
                                    </h5>
                                    <small class="text-muted">@lang('Rating')</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-start">
                            <p class="mb-1">
                                <i class="las la-calendar text-muted"></i> 
                                @lang('Member since') {{ $stats['member_since']->format('M Y') }}
                            </p>
                            <p class="mb-1">
                                <i class="las la-comments text-muted"></i> 
                                {{ $stats['total_reviews'] }} @lang('reviews')
                            </p>
                            @if($seller->website)
                                <p class="mb-0">
                                    <i class="las la-globe text-muted"></i>
                                    <a href="{{ $seller->website }}" target="_blank" rel="nofollow">
                                        {{ parse_url($seller->website, PHP_URL_HOST) }}
                                    </a>
                                </p>
                            @endif
                        </div>
                        @auth
                            @if($seller->id != auth()->id())
                                <hr>
                                <a href="{{ route('user.report.create', ['type' => 'user', 'id' => $seller->id]) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="las la-flag"></i> @lang('Report this user')
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
                
                <!-- Reviews Summary -->
                @if($reviews->count() > 0)
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">@lang('Recent Reviews')</h5>
                    </div>
                    <div class="card-body">
                        @foreach($reviews as $review)
                            <div class="review-item {{ !$loop->last ? 'border-bottom pb-3 mb-3' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $review->reviewer->username ?? 'Anonymous' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="las la-star {{ $i <= $review->overall_rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mb-0 small">{{ Str::limit($review->review, 150) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Seller Listings -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">@lang('Active Listings') ({{ $listings->total() }})</h4>
                </div>
                
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
                    <div class="text-center py-5 bg-white rounded">
                        <img src="{{ asset('assets/images/empty_list.png') }}" alt="" style="max-width: 150px;">
                        <h5 class="mt-3">@lang('No active listings')</h5>
                        <p class="text-muted">@lang('This seller has no active listings at the moment')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

