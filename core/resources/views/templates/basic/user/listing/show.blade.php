@extends($activeTemplate . 'user.layouts.app')
@section('panel')
@if($listing->rejection_reason)
<div class="alert alert-danger d-flex align-items-start mb-4 py-3" role="alert">
    <i class="las la-times-circle fs-4 me-2 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <strong class="d-block mb-1">@lang('Rejection Reason')</strong>
        <p class="mb-2">{{ $listing->rejection_reason }}</p>
        <small class="d-block mb-2">@lang('After you make corrections, click Edit Listing and save — the listing will be resubmitted for review automatically.')</small>
        <a href="{{ route('user.listing.edit', $listing->id) }}" class="btn btn-sm btn--primary">
            <i class="las la-edit"></i> @lang('Edit & Resubmit')
        </a>
    </div>
</div>
@endif
@if($listing->is_deactivated && $listing->deactivation_reason)
<div class="alert alert-warning d-flex align-items-start mb-4 py-3" role="alert">
    <i class="las la-exclamation-triangle fs-4 me-2 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <strong class="d-block mb-1">@lang('Listing Deactivated')</strong>
        <p class="mb-0">{{ $listing->deactivation_reason }}</p>
    </div>
</div>
@endif
<div class="row">
    <div class="col-lg-8">
            <!-- Listing Details Card -->
            <div class="card custom--card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">{{ $listing->title }}</h5>
                    @php echo $listing->listingStatus @endphp
                </div>
                <div class="card-body">
                    <!-- Images Gallery: use primary image for main, then thumbnails -->
                    @if($listing->images->count() > 0)
                    @php
                        $primaryImg = $listing->images->where('is_primary', true)->first() ?? $listing->images->first();
                    @endphp
                    <div class="listing-gallery mb-4">
                        <div class="rounded overflow-hidden mb-3 bg-light" style="max-height: 420px;">
                            <img src="{{ getImage(getFilePath('listing') . '/' . $primaryImg->image) }}" 
                                 class="img-fluid w-100" style="max-height: 420px; width: 100%; object-fit: contain; display: block;" alt="{{ $listing->title }}">
                        </div>
                        @if($listing->images->count() > 1)
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            @foreach($listing->images as $image)
                                <div class="rounded border {{ $image->id === $primaryImg->id ? 'border-primary border-2' : '' }}" style="width: 72px; height: 54px; overflow: hidden; flex-shrink: 0;">
                                    <img src="{{ getImage(getFilePath('listing') . '/' . $image->image) }}" 
                                         class="w-100 h-100" style="object-fit: cover;" alt="">
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Basic Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <strong>@lang('Listing Number'):</strong>
                            <span class="ms-2">{{ $listing->listing_number }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>@lang('Business Type'):</strong>
                            <span class="ms-2 badge badge--primary">{{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>@lang('Sale Type'):</strong>
                            <span class="ms-2">{{ $listing->sale_type === 'auction' ? __('Auction') : __('Fixed Price') }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>@lang('Created'):</strong>
                            <span class="ms-2">{{ showDateTime($listing->created_at, 'd M, Y') }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h6 class="mb-2">@lang('Description')</h6>
                        <p class="text-muted">{{ $listing->description }}</p>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            @if($listing->questions->count() > 0)
            <div class="card custom--card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="las la-question-circle"></i> @lang('Questions')</h5>
                </div>
                <div class="card-body">
                    @foreach($listing->questions as $question)
                    <div class="question-item mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong>{{ $question->asker->username ?? 'User' }}</strong>
                            <small class="text-muted">{{ showDateTime($question->created_at, 'd M, Y') }}</small>
                        </div>
                        <p class="mb-2">{{ $question->question }}</p>
                        @if($question->answer)
                            <div class="bg-light p-2 rounded">
                                <small class="text-muted">@lang('Your Answer'):</small>
                                <p class="mb-0">{{ $question->answer }}</p>
                            </div>
                        @else
                            <form action="{{ route('user.listing.question.answer', $listing->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="question_id" value="{{ $question->id }}">
                                <div class="input-group">
                                    <input type="text" name="answer" class="form-control form-control-sm" 
                                           placeholder="@lang('Type your answer...')" required>
                                    <button type="submit" class="btn btn--primary btn-sm">@lang('Answer')</button>
                                </div>
                            </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Stats Card -->
            <div class="card custom--card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="las la-chart-bar"></i> @lang('Statistics')</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Views')</span>
                        <strong>{{ $stats['total_views'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Watchers')</span>
                        <strong>{{ $stats['total_watchers'] }}</strong>
                    </div>
                    @if($listing->sale_type === 'auction')
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Total Bids')</span>
                        <strong>{{ $stats['total_bids'] }}</strong>
                    </div>
                    @else
                    <div class="d-flex justify-content-between mb-3">
                        <span>@lang('Offers Received')</span>
                        <strong>{{ $stats['total_offers'] }}</strong>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <span>@lang('Questions')</span>
                        <strong>{{ $stats['total_questions'] }}</strong>
                    </div>
                    @if(isset($viewsLast7))
                    <hr>
                    <h6 class="mb-2">@lang('Views over time')</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">@lang('Last 7 days')</span>
                        <strong>{{ $viewsLast7 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">@lang('Previous 7 days')</span>
                        <strong>{{ $viewsPrevious7 }}</strong>
                    </div>
                    @if(!empty($viewsByDay))
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted d-block mb-1">@lang('Last 14 days by day')</small>
                        <div class="small">
                            @foreach($viewsByDay as $date => $count)
                                <div class="d-flex justify-content-between">
                                    <span>{{ \Carbon\Carbon::parse($date)->format('M j') }}</span>
                                    <span>{{ $count }} @lang('views')</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Pricing Card -->
            <div class="card custom--card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="las la-dollar-sign"></i> @lang('Pricing')</h5>
                </div>
                <div class="card-body">
                    @if($listing->sale_type === 'auction')
                        <div class="mb-3">
                            <small class="text-muted">@lang('Starting Bid')</small>
                            <h4 class="mb-0">{{ showAmount($listing->starting_bid) }}</h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">@lang('Current Bid')</small>
                            <h4 class="mb-0 text--base">{{ showAmount($listing->current_bid ?: $listing->starting_bid) }}</h4>
                        </div>
                        @if($listing->reserve_price > 0)
                        <div class="mb-3">
                            <small class="text-muted">@lang('Reserve Price')</small>
                            <h5 class="mb-0">{{ showAmount($listing->reserve_price) }}</h5>
                            @if($listing->hasReserveBeenMet())
                                <small class="text-success">@lang('Reserve Met!')</small>
                            @else
                                <small class="text-warning">@lang('Reserve Not Met')</small>
                            @endif
                        </div>
                        @endif
                        @if($listing->buy_now_price > 0)
                        <div class="mb-3">
                            <small class="text-muted">@lang('Buy Now Price')</small>
                            <h5 class="mb-0 text-success">{{ showAmount($listing->buy_now_price) }}</h5>
                        </div>
                        @endif
                        @if($listing->auction_end)
                        <div class="mb-3">
                            <small class="text-muted">@lang('Auction Ends')</small>
                            <h6 class="mb-0">{{ showDateTime($listing->auction_end, 'd M, Y H:i') }}</h6>
                            <small class="{{ $listing->auction_end->isPast() ? 'text-danger' : 'text-info' }}">
                                {{ $listing->auction_end->diffForHumans() }}
                            </small>
                        </div>
                        @endif
                    @else
                        <div class="mb-3">
                            <small class="text-muted">@lang('Asking Price')</small>
                            <h3 class="mb-0 text--base">{{ showAmount($listing->asking_price) }}</h3>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card custom--card">
                <div class="card-body">
                    @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_REJECTED, \App\Constants\Status::LISTING_ACTIVE]))
                        <a href="{{ route('user.listing.edit', $listing->id) }}" class="btn btn--primary w-100 mb-2">
                            <i class="las la-edit"></i> @lang('Edit Listing')
                        </a>
                    @endif
                    @if($listing->status === \App\Constants\Status::LISTING_ACTIVE && !$listing->is_deactivated)
                        <a href="{{ route('marketplace.listing.show', $listing->slug) }}" class="btn btn--dark w-100 mb-2" target="_blank">
                            <i class="las la-external-link-alt"></i> @lang('View Public Page')
                        </a>

                        @if(!empty($featuredFeePerDay) && $featuredFeePerDay > 0)
                            @if($listing->is_featured && $listing->featured_until && $listing->featured_until->isFuture())
                                <div class="alert alert-success py-2 px-3 mb-2">
                                    <i class="las la-star"></i>
                                    @lang('Featured until'):
                                    <strong>{{ showDateTime($listing->featured_until, 'd M, Y') }}</strong>
                                </div>
                            @endif

                            <button type="button" class="btn btn--warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#featureListingModal">
                                <i class="las la-star"></i>
                                {{ ($listing->is_featured && $listing->featured_until && $listing->featured_until->isFuture()) ? __('Extend Featured') : __('Feature Listing') }}
                            </button>
                        @endif
                    @endif
                    @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_ACTIVE]))
                        @if($listing->sale_type !== 'auction' || $listing->total_bids == 0)
                        <form action="{{ route('user.listing.cancel', $listing->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn--danger w-100" 
                                    onclick="return confirm('@lang('Are you sure you want to cancel this listing?')')">
                                <i class="las la-times"></i> @lang('Cancel Listing')
                            </button>
                        </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@if($listing->status === \App\Constants\Status::LISTING_ACTIVE && !$listing->is_deactivated && !empty($featuredFeePerDay) && $featuredFeePerDay > 0)
    <div class="modal fade" id="featureListingModal" tabindex="-1" aria-labelledby="featureListingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="featureListingModalLabel">
                        <i class="las la-star"></i>
                        {{ ($listing->is_featured && $listing->featured_until && $listing->featured_until->isFuture()) ? __('Extend Featured') : __('Feature Listing') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
                </div>
                <form action="{{ route('user.listing.feature', $listing->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">@lang('Days')</label>
                            <input type="number" name="days" id="featureDays" class="form-control" min="1" max="365" value="7" required>
                            <small class="text-muted d-block mt-1">
                                @lang('Fee per day'): <strong>{{ showAmount($featuredFeePerDay) }}</strong>
                            </small>
                            <small class="text-muted d-block">
                                @lang('Total'): <strong id="featureTotal">{{ showAmount($featuredFeePerDay * 7) }}</strong>
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">@lang('Payment Method')</label>
                            <select name="pay_via" class="form-select form--select" required>
                                <option value="1">@lang('Wallet') - {{ showAmount(auth()->user()->balance) }}</option>
                                <option value="2">@lang('Pay via Gateway')</option>
                            </select>
                        </div>
                        <div class="alert alert-info py-2 px-3 mb-0">
                            @lang('Pay from your wallet or pay via gateway to feature your listing for the selected number of days.')
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--warning">
                            <i class="las la-check"></i> @lang('Confirm')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            (function () {
                const feePerDay = Number("{{ (float) $featuredFeePerDay }}") || 0;
                const daysEl = document.getElementById('featureDays');
                const totalEl = document.getElementById('featureTotal');

                function formatAmount(val) {
                    // Display uses server-side showAmount; JS fallback keeps it simple.
                    try {
                        return new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
                    } catch (e) {
                        return (Math.round(val * 100) / 100).toFixed(2);
                    }
                }

                function updateTotal() {
                    const days = Math.max(1, Math.min(365, Number(daysEl.value || 0)));
                    const total = feePerDay * days;
                    totalEl.textContent = formatAmount(total);
                }

                if (daysEl && totalEl) {
                    daysEl.addEventListener('input', updateTotal);
                    updateTotal();
                }
            })();
        </script>
    @endpush
@endif

