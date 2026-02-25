@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="alert alert-info mb-4">
                <i class="las la-info-circle"></i> 
                <strong>@lang('Note'):</strong> 
                @lang('Payment escrow is automatically created when you purchase a listing, win an auction, or have an offer accepted.')
            </div>

            {{-- Filter Section --}}
            <div class="card b-radius--10 mb-4">
                <div class="card-body">
                    <form action="{{ route('user.escrow.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">@lang('Status')</label>
                            <select name="status" class="form-select form--select">
                                <option value="">@lang('All Statuses')</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>@lang('Accepted')</option>
                                <option value="not_accepted" {{ request('status') == 'not_accepted' ? 'selected' : '' }}>@lang('Not Accepted')</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>@lang('Completed')</option>
                                <option value="disputed" {{ request('status') == 'disputed' ? 'selected' : '' }}>@lang('Disputed')</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>@lang('Cancelled')</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">@lang('Role')</label>
                            <select name="role" class="form-select form--select">
                                <option value="">@lang('All')</option>
                                <option value="buying" {{ request('role') == 'buying' ? 'selected' : '' }}>@lang('Buying')</option>
                                <option value="selling" {{ request('role') == 'selling' ? 'selected' : '' }}>@lang('Selling')</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">@lang('Search')</label>
                            <input type="text" name="search" class="form-control form--control" placeholder="@lang('Search by escrow number, listing title...')" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn--base w-100">
                                    <i class="las la-filter"></i> @lang('Filter')
                                </button>
                                <a href="{{ route('user.escrow.index') }}" class="btn btn--secondary w-100" title="@lang('Reset')">
                                    <i class="las la-redo-alt"></i>
                                </a>
                            </div>
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
                                <th>@lang('Transaction ID')</th>
                                <th>@lang('Seller / Buyer')</th>
                                <th>@lang('Purchase Amount')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($escrows as $escrow)
                                <tr>
                                    <td>
                                        @if($escrow->listing)
                                            <a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}" class="text-decoration-none">
                                                <strong>{{ $escrow->listing->title }}</strong>
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $escrow->listing->listing_number }}</small>
                                        @else
                                            {{ __($escrow->title) }}
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $escrow->escrow_number }}</small>
                                    </td>
                                    <td>
                                        @if ($escrow->buyer_id == auth()->user()->id)
                                            <span class="badge badge--info">@lang('Buying from')</span><br>
                                            <strong>{{ __(@$escrow->seller->username ?? $escrow->invitation_mail) }}</strong>
                                        @else
                                            <span class="badge badge--success">@lang('Selling to')</span><br>
                                            <strong>{{ __(@$escrow->buyer->username ?? $escrow->invitation_mail) }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ showAmount($escrow->amount) }}</strong>
                                        @if($escrow->charge > 0)
                                            <br><small class="text-muted">@lang('Fee'): {{ showAmount($escrow->charge) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php echo $escrow->escrowStatus @endphp
                                    </td>
                                    <td>
                                        <a href="{{ route('user.escrow.details', $escrow->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-desktop"></i> @lang('View')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <div class="py-5">
                                            <i class="las la-shopping-cart" style="font-size: 3rem; color: #ccc;"></i>
                                            <p class="mt-3">@lang('No purchases yet. Start browsing the marketplace!')</p>
                                            <a href="{{ route('marketplace.index') }}" class="btn btn--base mt-2">@lang('Browse Listings')</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if ($escrows->hasPages())
                <div class="mt-3">
                    {{ $escrows->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
