@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-8">
            <!-- Listing Info -->
            <div class="card mb-4">
                <div class="card-header bg--primary">
                    <h5 class="card-title text-white mb-0">@lang('Listing Information')</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">@lang('Listing #')</div>
                        <div class="col-md-9">{{ $listing->listing_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">@lang('Title')</div>
                        <div class="col-md-9">{{ $listing->title }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">@lang('Business Type')</div>
                        <div class="col-md-9">{{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">@lang('Sale Type')</div>
                        <div class="col-md-9">{{ $listing->sale_type === 'auction' ? 'Auction' : 'Fixed Price' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">@lang('Status')</div>
                        <div class="col-md-9">@php echo $listing->listingStatus @endphp</div>
                    </div>
                    @if($listing->rejection_reason)
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">@lang('Rejection Reason')</div>
                            <div class="col-md-9 text-danger">{{ $listing->rejection_reason }}</div>
                        </div>
                    @endif
                    @if($listing->is_deactivated)
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">@lang('Deactivation Status')</div>
                            <div class="col-md-9">
                                <span class="badge bg--danger">@lang('Deactivated')</span>
                                @if($listing->deactivated_at)
                                    <small class="text-muted d-block">@lang('Deactivated on') {{ $listing->deactivated_at->format('M d, Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                        @if($listing->deactivation_reason)
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">@lang('Deactivation Reason')</div>
                                <div class="col-md-9 text-warning">{{ $listing->deactivation_reason }}</div>
                            </div>
                        @endif
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">@lang('Description')</div>
                        <div class="col-md-9">{!! nl2br(e($listing->description)) !!}</div>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Pricing')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($listing->sale_type === 'auction')
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <small class="text-muted d-block">@lang('Starting Bid')</small>
                                    <strong class="fs-5">{{ showAmount($listing->starting_bid) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <small class="text-muted d-block">@lang('Current Bid')</small>
                                    <strong class="fs-5 text--primary">{{ showAmount($listing->current_bid) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <small class="text-muted d-block">@lang('Reserve Price')</small>
                                    <strong class="fs-5">{{ showAmount($listing->reserve_price) }}</strong>
                                </div>
                            </div>
                        @else
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center">
                                    <small class="text-muted d-block">@lang('Asking Price')</small>
                                    <strong class="fs-4 text--primary">{{ showAmount($listing->asking_price) }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Financials -->
            @if($listing->monthly_revenue > 0 || $listing->monthly_profit > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Financials')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">@lang('Monthly Revenue')</small>
                                <strong>{{ showAmount($listing->monthly_revenue) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">@lang('Monthly Profit')</small>
                                <strong>{{ showAmount($listing->monthly_profit) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">@lang('Monthly Visitors')</small>
                                <strong>{{ number_format($listing->monthly_visitors) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">@lang('Page Views')</small>
                                <strong>{{ number_format($listing->monthly_page_views) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Images -->
            @if($listing->images->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Images')</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($listing->images as $image)
                            <div class="col-md-4">
                                <img src="{{ getImage(getFilePath('listing') . '/' . $image->image) }}" 
                                     alt="" class="img-fluid rounded">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <div class="col-lg-4">
            <!-- Seller Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Seller Information')</h5>
                </div>
                <div class="card-body">
                    @if($listing->user)
                        <div class="text-center mb-3">
                            <div class="avatar avatar--lg bg--primary rounded-circle d-inline-flex align-items-center justify-content-center">
                                <span class="text-white fs-3">{{ strtoupper(substr($listing->user->username, 0, 1)) }}</span>
                            </div>
                        </div>
                        <table class="table table-borderless">
                            <tr>
                                <td>@lang('Username')</td>
                                <td>
                                    <a href="{{ route('admin.users.detail', $listing->user->id) }}">
                                        {{ $listing->user->username }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('Email')</td>
                                <td>{{ $listing->user->email }}</td>
                            </tr>
                            <tr>
                                <td>@lang('Joined')</td>
                                <td>{{ $listing->user->created_at->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted">@lang('User not found')</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Actions')</h5>
                </div>
                <div class="card-body">
                    @if($listing->status == \App\Constants\Status::LISTING_PENDING)
                        <form action="{{ route('admin.listing.approve', $listing->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn--success w-100">
                                <i class="las la-check"></i> @lang('Approve Listing')
                            </button>
                        </form>
                        <button type="button" class="btn btn--danger w-100 mb-2 rejectBtn" data-id="{{ $listing->id }}">
                            <i class="las la-times"></i> @lang('Reject Listing')
                        </button>
                    @endif

                    @if($listing->status == \App\Constants\Status::LISTING_ACTIVE)
                        @if($listing->is_deactivated)
                            <form action="{{ route('admin.listing.reactivate', $listing->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn--success w-100" 
                                        onclick="return confirm('@lang('Are you sure to reactivate this listing?')')">
                                    <i class="las la-check-circle"></i> @lang('Reactivate Listing')
                                </button>
                            </form>
                        @else
                            @if(!$listing->is_featured)
                                <button type="button" class="btn btn--warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#featureModal">
                                    <i class="las la-star"></i> @lang('Feature Listing')
                                </button>
                            @else
                                <form action="{{ route('admin.listing.unfeature', $listing->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn--secondary w-100">
                                        <i class="las la-star-half-alt"></i> @lang('Remove Featured')
                                    </button>
                                </form>
                            @endif

                            <button type="button" class="btn btn--info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#verifyModal">
                                <i class="las la-check-circle"></i> @lang('Verify Listing')
                            </button>

                            @if($listing->sale_type === 'auction' && $listing->auction_end && $listing->auction_end->isFuture())
                                <button type="button" class="btn btn--primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#extendModal">
                                    <i class="las la-clock"></i> @lang('Extend Auction')
                                </button>
                            @endif

                            <button type="button" class="btn btn--warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                                <i class="las la-pause-circle"></i> @lang('Deactivate Listing')
                            </button>

                            <button type="button" class="btn btn--danger w-100 cancelBtn" data-id="{{ $listing->id }}">
                                <i class="las la-ban"></i> @lang('Cancel Listing')
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Statistics')</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h4 class="mb-0">{{ $listing->view_count }}</h4>
                                <small class="text-muted">@lang('Views')</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h4 class="mb-0">{{ $listing->watchlist_count }}</h4>
                                <small class="text-muted">@lang('Watchers')</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h4 class="mb-0">{{ $listing->total_bids }}</h4>
                                <small class="text-muted">@lang('Bids')</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Listing')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
                </div>
                <form action="{{ route('admin.listing.reject', $listing->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Rejection Reason')</label>
                            <textarea name="reason" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--danger">@lang('Reject')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Feature Modal -->
    <div class="modal fade" id="featureModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Feature Listing')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
                </div>
                <form action="{{ route('admin.listing.feature', $listing->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Feature Duration (Days)')</label>
                            <input type="number" name="days" class="form-control" value="7" min="1" max="365" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--warning">@lang('Feature')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Verify Modal -->
    <div class="modal fade" id="verifyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Verify Listing')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
                </div>
                <form action="{{ route('admin.listing.verify', $listing->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_verified" value="1" class="form-check-input" 
                                       @checked($listing->is_verified)>
                                @lang('Listing Verified')
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="revenue_verified" value="1" class="form-check-input"
                                       @checked($listing->revenue_verified)>
                                @lang('Revenue Verified')
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="traffic_verified" value="1" class="form-check-input"
                                       @checked($listing->traffic_verified)>
                                @lang('Traffic Verified')
                            </label>
                        </div>
                        <div class="form-group">
                            <label>@lang('Verification Notes')</label>
                            <textarea name="verification_notes" class="form-control" rows="3">{{ $listing->verification_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--success">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Extend Auction Modal -->
    <div class="modal fade" id="extendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Extend Auction')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
                </div>
                <form action="{{ route('admin.listing.extend.auction', $listing->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Extend by (Hours)')</label>
                            <input type="number" name="hours" class="form-control" value="24" min="1" max="720" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Extend')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Deactivate Modal -->
    <div class="modal fade" id="deactivateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Deactivate Listing')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
                </div>
                <form action="{{ route('admin.listing.deactivate', $listing->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="las la-exclamation-triangle"></i> @lang('The listing will be hidden from buyers but can be reactivated later.')
                        </div>
                        <div class="form-group">
                            <label>@lang('Deactivation Reason') <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="4" required placeholder="@lang('Please provide a reason for deactivating this listing...')"></textarea>
                            <small class="text-muted">@lang('This reason will be sent to the seller via email notification.')</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--warning">@lang('Deactivate')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal (for active listings requiring reason) -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Cancel Listing')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
                </div>
                <form id="cancelForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="las la-exclamation-triangle"></i> @lang('This action cannot be undone. The listing will be permanently cancelled.')
                        </div>
                        <div class="form-group">
                            <label>@lang('Cancellation Reason') <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="4" required placeholder="@lang('Please provide a reason for cancelling this listing...')"></textarea>
                            <small class="text-muted">@lang('This reason will be sent to the seller via email notification.')</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--danger">@lang('Cancel Listing')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.listing.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-undo"></i> @lang('Back to List')
    </a>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";
        
        $('.rejectBtn').on('click', function() {
            $('#rejectModal').modal('show');
        });

        $('.cancelBtn').on('click', function() {
            var listingId = $(this).data('id');
            $('#cancelForm').attr('action', '{{ route("admin.listing.cancel", ":id") }}'.replace(':id', listingId));
            $('#cancelModal').modal('show');
        });
    })(jQuery);
</script>
@endpush

