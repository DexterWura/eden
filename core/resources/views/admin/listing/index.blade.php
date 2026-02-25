@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-end">
                        @if(request()->routeIs('admin.listing.index'))
                            <div class="col-xl-2 col-lg-3 col-md-4">
                                <label class="form-label">@lang('Status')</label>
                                <select name="status" class="form-control">
                                    <option value="">@lang('All')</option>
                                    <option value="{{ \App\Constants\Status::LISTING_PENDING }}" @selected(request('status') == \App\Constants\Status::LISTING_PENDING)>@lang('Pending')</option>
                                    <option value="{{ \App\Constants\Status::LISTING_ACTIVE }}" @selected(request('status') == \App\Constants\Status::LISTING_ACTIVE)>@lang('Active')</option>
                                    <option value="{{ \App\Constants\Status::LISTING_SOLD }}" @selected(request('status') == \App\Constants\Status::LISTING_SOLD)>@lang('Sold')</option>
                                    <option value="{{ \App\Constants\Status::LISTING_REJECTED }}" @selected(request('status') == \App\Constants\Status::LISTING_REJECTED)>@lang('Rejected')</option>
                                    <option value="{{ \App\Constants\Status::LISTING_EXPIRED }}" @selected(request('status') == \App\Constants\Status::LISTING_EXPIRED)>@lang('Expired')</option>
                                    <option value="{{ \App\Constants\Status::LISTING_CANCELLED }}" @selected(request('status') == \App\Constants\Status::LISTING_CANCELLED)>@lang('Cancelled')</option>
                                </select>
                            </div>
                        @endif

                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <label class="form-label">@lang('Business Type')</label>
                            <select name="business_type" class="form-control">
                                <option value="">@lang('All')</option>
                                <option value="domain" @selected(request('business_type') == 'domain')>@lang('Domain')</option>
                                <option value="website" @selected(request('business_type') == 'website')>@lang('Website')</option>
                                <option value="social_media_account" @selected(request('business_type') == 'social_media_account')>@lang('Social Media')</option>
                                <option value="mobile_app" @selected(request('business_type') == 'mobile_app')>@lang('Mobile App')</option>
                                <option value="desktop_app" @selected(request('business_type') == 'desktop_app')>@lang('Desktop App')</option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <label class="form-label">@lang('Sale Type')</label>
                            <select name="sale_type" class="form-control">
                                <option value="">@lang('All')</option>
                                <option value="fixed_price" @selected(request('sale_type') == 'fixed_price')>@lang('Fixed Price')</option>
                                <option value="auction" @selected(request('sale_type') == 'auction')>@lang('Auction')</option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <label class="form-label">@lang('Featured')</label>
                            <select name="featured" class="form-control">
                                <option value="">@lang('All')</option>
                                <option value="active" @selected(request('featured') == 'active')>@lang('Featured (Active)')</option>
                                <option value="yes" @selected(request('featured') == 'yes')>@lang('Featured (Flagged)')</option>
                                <option value="no" @selected(request('featured') == 'no')>@lang('Not Featured')</option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <label class="form-label">@lang('From')</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4">
                            <label class="form-label">@lang('To')</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-xl-4 col-lg-6">
                            <label class="form-label">@lang('Search')</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="@lang('Title, listing #, username, email')">
                        </div>

                        <div class="col-xl-2 col-lg-3">
                            <button type="submit" class="btn btn--primary w-100">
                                <i class="las la-filter"></i> @lang('Filter')
                            </button>
                        </div>
                        <div class="col-xl-2 col-lg-3">
                            <a href="{{ url()->current() }}" class="btn btn--dark w-100">
                                <i class="las la-redo-alt"></i> @lang('Reset')
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Listing')</th>
                                    <th>@lang('Seller')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Stats')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listings as $listing)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($listing->images->first())
                                                    <img src="{{ getImage(getFilePath('listing') . '/' . $listing->images->first()->image) }}" 
                                                         alt="" class="me-3" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;">
                                                @endif
                                                <div>
                                                    <span class="fw-bold">{{ Str::limit($listing->title, 40) }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $listing->listing_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($listing->user)
                                                <a href="{{ route('admin.users.detail', $listing->user->id) }}">
                                                    {{ $listing->user->username }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge--secondary">
                                                {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                                            </span>
                                            <br>
                                            <small>{{ $listing->sale_type === 'auction' ? 'Auction' : 'Fixed Price' }}</small>
                                        </td>
                                        <td>
                                            @if($listing->sale_type === 'auction')
                                                {{ showAmount($listing->current_bid ?: $listing->starting_bid) }}
                                                <br>
                                                <small>{{ $listing->total_bids }} @lang('bids')</small>
                                            @else
                                                {{ showAmount($listing->asking_price) }}
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                <i class="las la-eye"></i> {{ $listing->view_count }}<br>
                                                <i class="las la-heart"></i> {{ $listing->watchlist_count }}
                                            </small>
                                        </td>
                                        <td>
                                            @php echo $listing->listingStatus @endphp
                                            @if($listing->is_featured)
                                                <br><span class="badge badge--warning">@lang('Featured')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.listing.details', $listing->id) }}" 
                                                   class="btn btn-sm btn-outline--primary">
                                                    <i class="las la-eye"></i> @lang('Details')
                                                </a>
                                                
                                                @if($listing->status == \App\Constants\Status::LISTING_PENDING)
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn"
                                                            data-action="{{ route('admin.listing.approve', $listing->id) }}"
                                                            data-question="@lang('Are you sure to approve this listing?')">
                                                        <i class="las la-check"></i> @lang('Approve')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--danger rejectBtn"
                                                            data-id="{{ $listing->id }}">
                                                        <i class="las la-times"></i> @lang('Reject')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($listings->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($listings) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Listing')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Rejection Reason')</label>
                            <textarea name="reason" class="form-control" rows="4" required 
                                      placeholder="@lang('Provide a reason for rejection...')"></textarea>
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
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    {{-- Search is included in the filter bar above --}}
@endpush

@push('script')
<script>
    (function($) {
        "use strict";
        
        $('.rejectBtn').on('click', function() {
            var id = $(this).data('id');
            var url = "{{ route('admin.listing.reject', ':id') }}";
            url = url.replace(':id', id);
            
            $('#rejectForm').attr('action', url);
            $('#rejectModal').modal('show');
        });
    })(jQuery);
</script>
@endpush

