@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('user.listing.create') }}" class="btn btn--base fw-bold" id="createListingBtn">
                    <i class="las la-plus"></i> @lang('Create Listing')
                </a>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">@lang('All Status')</option>
                            <option value="0" @selected(request('status') === '0')>@lang('Draft')</option>
                            <option value="1" @selected(request('status') === '1')>@lang('Pending')</option>
                            <option value="2" @selected(request('status') === '2')>@lang('Active')</option>
                            <option value="3" @selected(request('status') === '3')>@lang('Sold')</option>
                            <option value="4" @selected(request('status') === '4')>@lang('Expired')</option>
                            <option value="6" @selected(request('status') === '6')>@lang('Rejected')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="business_type" class="form-select">
                            <option value="">@lang('All Types')</option>
                            <option value="domain" @selected(request('business_type') == 'domain')>@lang('Domain')</option>
                            <option value="website" @selected(request('business_type') == 'website')>@lang('Website')</option>
                            <option value="social_media_account" @selected(request('business_type') == 'social_media_account')>@lang('Social Media')</option>
                            <option value="mobile_app" @selected(request('business_type') == 'mobile_app')>@lang('Mobile App')</option>
                            <option value="desktop_app" @selected(request('business_type') == 'desktop_app')>@lang('Desktop App')</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="@lang('Search...')">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn--base w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                    </div>
                </form>
            </div>
        </div>
        
            <!-- Listings Table -->
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th>@lang('Listing')</th>
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
                                        <span class="me-3">
                                            <x-table-thumb
                                                :src="$listing->images->first() ? getImage(getFilePath('listing') . '/' . $listing->images->first()->image) : null"
                                                alt=""
                                            />
                                        </span>
                                        <div>
                                            <strong>{{ Str::limit($listing->title, 40) }}</strong>
                                            @if($listing->is_featured && $listing->featured_until && $listing->featured_until->isFuture())
                                                <span class="badge bg-warning text-dark ms-1">@lang('Featured')</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">{{ $listing->listing_number }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $listing->sale_type === 'auction' ? __('Auction') : __('Fixed Price') }}
                                    </small>
                                </td>
                                <td>
                                    @if($listing->sale_type === 'auction')
                                        <strong>{{ showAmount($listing->current_bid ?: $listing->starting_bid) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $listing->total_bids }} @lang('bids')</small>
                                    @else
                                        <strong>{{ showAmount($listing->asking_price) }}</strong>
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
                                    @if($listing->rejection_reason)
                                        <br>
                                        <small class="text-danger">{{ Str::limit($listing->rejection_reason, 30) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('user.listing.show', $listing->id) }}" class="btn btn-sm btn-outline--primary" title="@lang('View')">
                                            <i class="las la-desktop"></i>
                                        </a>
                                        @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_REJECTED]))
                                            <a href="{{ route('user.listing.edit', $listing->id) }}" class="btn btn-sm btn-outline--info" title="@lang('Edit')">
                                                <i class="las la-edit"></i>
                                            </a>
                                        @endif
                                        @if(in_array($listing->status, [\App\Constants\Status::LISTING_DRAFT, \App\Constants\Status::LISTING_PENDING, \App\Constants\Status::LISTING_ACTIVE]))
                                            <form action="{{ route('user.listing.cancel', $listing->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger" 
                                                        onclick="return confirm('@lang('Are you sure?')')" title="@lang('Cancel')">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <x-empty-state
                                        title="No listings found"
                                        actionHref="{{ route('user.listing.create') }}"
                                        actionLabel="Create Your First Listing"
                                        imgMaxWidth="120"
                                    />
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            
            @if($listings->hasPages())
                <div class="mt-4">
                    {{ $listings->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

{{-- styles moved to assets/templates/basic/css/custom.css --}}

