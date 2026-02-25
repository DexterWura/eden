@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('user.offer.index') }}" class="btn btn--base btn-sm">
                    <i class="las la-paper-plane"></i> @lang('Offers Made')
                </a>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">@lang('All Status')</option>
                                <option value="{{ \App\Constants\Status::OFFER_PENDING }}" @selected(request('status') == \App\Constants\Status::OFFER_PENDING)>@lang('Pending')</option>
                                <option value="{{ \App\Constants\Status::OFFER_ACCEPTED }}" @selected(request('status') == \App\Constants\Status::OFFER_ACCEPTED)>@lang('Accepted')</option>
                                <option value="{{ \App\Constants\Status::OFFER_REJECTED }}" @selected(request('status') == \App\Constants\Status::OFFER_REJECTED)>@lang('Rejected')</option>
                                <option value="{{ \App\Constants\Status::OFFER_COUNTERED }}" @selected(request('status') == \App\Constants\Status::OFFER_COUNTERED)>@lang('Countered')</option>
                                <option value="{{ \App\Constants\Status::OFFER_EXPIRED }}" @selected(request('status') == \App\Constants\Status::OFFER_EXPIRED)>@lang('Expired')</option>
                                <option value="{{ \App\Constants\Status::OFFER_CANCELLED }}" @selected(request('status') == \App\Constants\Status::OFFER_CANCELLED)>@lang('Cancelled')</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="@lang('Search by listing title...')">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn--base w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                        </div>
                        <div class="col-md-3 text-end">
                            @if(request()->has('status') || request()->has('search'))
                                <a href="{{ route('user.offer.received') }}" class="btn btn-outline-secondary w-100">
                                    <i class="las la-times"></i> @lang('Clear')
                                </a>
                            @endif
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
                            <th>@lang('Buyer')</th>
                            <th>@lang('Offer')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Date')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offers as $offer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-3">
                                            <x-table-thumb
                                                :src="$offer->listing->images->first() ? getImage(getFilePath('listing') . '/' . $offer->listing->images->first()->image) : null"
                                                alt=""
                                            />
                                        </span>
                                        <div>
                                            <a href="{{ route('marketplace.listing.show', $offer->listing->slug) }}">
                                                {{ Str::limit($offer->listing->title, 40) }}
                                            </a>
                                            <br>
                                            <small class="text-muted">@lang('Asking'): {{ showAmount($offer->listing->asking_price) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('marketplace.seller', $offer->buyer->username ?? '') }}">
                                        {{ $offer->buyer->username ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ showAmount($offer->amount) }}</strong>
                                    @if($offer->message)
                                        <br>
                                        <small class="text-muted" title="{{ $offer->message }}">
                                            <i class="las la-comment"></i> {{ Str::limit($offer->message, 30) }}
                                        </small>
                                    @endif
                                </td>
                                <td>@php echo $offer->offerStatus @endphp</td>
                                <td>{{ $offer->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="button--group">
                                        @if($offer->status == \App\Constants\Status::OFFER_PENDING)
                                            <form action="{{ route('user.offer.accept', $offer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--success" 
                                                        onclick="return confirm('@lang('Accept this offer for') {{ showAmount($offer->amount) }}?')">
                                                    <i class="las la-check"></i> @lang('Accept')
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline--info counterBtn"
                                                    data-id="{{ $offer->id }}" data-amount="{{ $offer->amount }}">
                                                <i class="las la-exchange-alt"></i> @lang('Counter')
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline--danger rejectBtn"
                                                    data-id="{{ $offer->id }}">
                                                <i class="las la-times"></i> @lang('Reject')
                                            </button>
                                        @elseif($offer->status == \App\Constants\Status::OFFER_ACCEPTED && $offer->escrow_id)
                                            <a href="{{ route('user.escrow.details', $offer->escrow_id) }}" class="btn btn-sm btn-outline--primary">
                                                <i class="las la-hand-holding-usd"></i> @lang('View Escrow')
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <x-empty-state
                                        title="No offers received yet"
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
            
            @if($offers->hasPages())
                <div class="mt-4">{{ $offers->links() }}</div>
            @endif
        </div>
    </div>

<!-- Counter Offer Modal -->
<div class="modal fade" id="counterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Counter Offer')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="counterForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">@lang('Original Offer')</label>
                        <input type="text" class="form-control" id="originalAmount" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('Your Counter Offer') <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">{{ gs()->cur_sym }}</span>
                            <input type="number" name="counter_amount" class="form-control" step="0.01" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('Message')</label>
                        <textarea name="counter_message" class="form-control" rows="3" 
                                  placeholder="@lang('Explain your counter offer...')"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--base fw-bold">@lang('Send Counter Offer')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Reject Offer')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">@lang('Reason') (@lang('optional'))</label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="@lang('Why are you rejecting this offer?')"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-danger">@lang('Reject Offer')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .btn--base {
        background: #{{ gs('base_color', '4bea76') }} !important;
        color: #fff !important;
        font-weight: 600 !important;
        border: none !important;
    }
    .btn--base:hover {
        background: #{{ gs('base_color', '4bea76') }} !important;
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
    }
</style>
@endpush

@push('script')
<script>
    $(document).ready(function() {
        $('.counterBtn').on('click', function() {
            var id = $(this).data('id');
            var amount = $(this).data('amount');
            var url = "{{ route('user.offer.counter', ':id') }}";
            url = url.replace(':id', id);
            
            $('#counterForm').attr('action', url);
            $('#originalAmount').val('{{ gs()->cur_sym }}' + parseFloat(amount).toFixed(2));
            $('#counterModal').modal('show');
        });
        
        $('.rejectBtn').on('click', function() {
            var id = $(this).data('id');
            var url = "{{ route('user.offer.reject', ':id') }}";
            url = url.replace(':id', id);
            
            $('#rejectForm').attr('action', url);
            $('#rejectModal').modal('show');
        });
    });
</script>
@endpush

