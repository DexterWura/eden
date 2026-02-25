@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row gy-4">
                @if($escrow->listing)
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1"><a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}" class="text-decoration-none">{{ $escrow->listing->title }}</a></h5>
                                    <p class="text-muted mb-0">@lang('Listing #'): {{ $escrow->listing->listing_number }}</p>
                                </div>
                                <a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}" class="btn btn--base btn-sm">
                                    <i class="las la-external-link-alt"></i> @lang('View Listing')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-12">
                    @php
                        $user = auth()->user();
                        $isSeller = $escrow->seller_id == $user->id;
                        $isBuyer = $escrow->buyer_id == $user->id;
                        $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
                        $pendingMilestones = $escrow->milestones->filter(function($m) use ($hasApprovalStatus, $isSeller, $isBuyer) {
                            if ($hasApprovalStatus) {
                                return $m->approval_status === 'pending';
                            }
                            return !($m->approved_by_seller && $m->approved_by_buyer);
                        });
                        $needsUserApproval = $pendingMilestones->filter(function($m) use ($isSeller, $isBuyer) {
                            return ($isSeller && !$m->approved_by_seller) || ($isBuyer && !$m->approved_by_buyer);
                        });
                    @endphp
                    
                    @if($needsUserApproval->count() > 0)
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-start">
                            <i class="las la-info-circle fs-4 me-2 mt-1"></i>
                            <div>
                                <strong>@lang('Action Required'):</strong>
                                @if($isBuyer)
                                    <p class="mb-0">@lang('You have') {{ $needsUserApproval->count() }} @lang('milestone(s) created by the seller that need your approval. Please review and approve or reject them below.')</p>
                                @else
                                    <p class="mb-0">@lang('You have') {{ $needsUserApproval->count() }} @lang('milestone(s) created by the buyer that need your approval. Please review and approve or reject them below.')</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        @if ($escrow->status == Status::ESCROW_ACCEPTED && $escrow->restAmount() && $escrow->buyer_id == auth()->id())
                            <button class="btn btn--base btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newModal">
                                <i class="las la-plus"></i> @lang('Create Milestone')
                            </button>
                        @else
                            <div></div>
                        @endif
                    </div>

                    <div class="card b-radius--10">
                        <div class="card-body p-0">
                            <div class="table-responsive--md table-responsive">
                                <table class="table table--light style--two">
                                    <thead>
                                        <tr>
                                            <th>@lang('Date')</th>
                                            <th>@lang('Note')</th>
                                            <th>@lang('Amount')</th>
                                            <th>@lang('Approval Status')</th>
                                            <th>@lang('Payment Status')</th>
                                            <th>@lang('Action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($milestones as $milestone)
                                            @php
                                                $hasApprovalStatus = \Illuminate\Support\Facades\Schema::hasColumn('milestones', 'approval_status');
                                                $isApproved = $hasApprovalStatus ? ($milestone->approval_status === 'approved') : ($milestone->approved_by_seller && $milestone->approved_by_buyer);
                                                $isPending = $hasApprovalStatus ? ($milestone->approval_status === 'pending') : (!($milestone->approved_by_seller && $milestone->approved_by_buyer));
                                                $isRejected = $hasApprovalStatus && $milestone->approval_status === 'rejected';
                                                
                                                $user = auth()->user();
                                                $isSeller = $escrow->seller_id == $user->id;
                                                $isBuyer = $escrow->buyer_id == $user->id;
                                                
                                                // Check if user needs to approve
                                                $needsSellerApproval = $isSeller && !$milestone->approved_by_seller && !$isRejected;
                                                $needsBuyerApproval = $isBuyer && !$milestone->approved_by_buyer && !$isRejected;
                                                $canApprove = ($needsSellerApproval || $needsBuyerApproval) && $isPending;
                                                $canReject = ($isSeller || $isBuyer) && $isPending && !$isApproved;
                                                
                                                // Check if created by buyer or seller
                                                $createdBy = isset($milestone->requested_by) ? $milestone->requested_by : ($milestone->user_id == $escrow->seller_id ? 'seller' : 'buyer');
                                            @endphp
                                            <tr>
                                                <td>{{ showDateTime($milestone->created_at, 'Y-m-d') }}</td>

                                                <td>
                                                    {{ $milestone->note }}
                                                    @if($isRejected && isset($milestone->rejection_reason))
                                                        <br><small class="text-danger"><i class="las la-times-circle"></i> @lang('Rejected'): {{ $milestone->rejection_reason }}</small>
                                                    @endif
                                                </td>

                                                <td>{{ showAmount($milestone->amount) }}</td>

                                                <td>
                                                    @if($isApproved)
                                                        <span class="badge badge--success">
                                                            <i class="las la-check-circle"></i> @lang('Approved')
                                                        </span>
                                                    @elseif($isRejected)
                                                        <span class="badge badge--danger">
                                                            <i class="las la-times-circle"></i> @lang('Rejected')
                                                        </span>
                                                    @else
                                                        <span class="badge badge--warning">
                                                            <i class="las la-clock"></i> @lang('Pending Approval')
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            @if($createdBy == 'buyer')
                                                                @lang('Created by buyer')
                                                            @else
                                                                @lang('Created by seller')
                                                            @endif
                                                            <br>
                                                            @if(!$milestone->approved_by_seller)
                                                                <span class="text-danger">@lang('Seller: Pending')</span>
                                                            @else
                                                                <span class="text-success">@lang('Seller: ✓')</span>
                                                            @endif
                                                            @if(!$milestone->approved_by_buyer)
                                                                <span class="text-danger"> | @lang('Buyer: Pending')</span>
                                                            @else
                                                                <span class="text-success"> | @lang('Buyer: ✓')</span>
                                                            @endif
                                                        </small>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($milestone->payment_status == Status::MILESTONE_FUNDED)
                                                        <span class="badge badge--success">@lang('Funded')</span>
                                                    @else
                                                        @if ($milestone->deposit && $milestone->deposit->status == Status::PAYMENT_PENDING)
                                                            <span class="badge badge--warning">@lang('Payment Pending')</span>
                                                        @else
                                                            <span class="badge badge--danger">@lang('Unfunded')</span>
                                                        @endif
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="button--group">
                                                        @if($canApprove)
                                                            <form action="{{ route('user.escrow.milestone.approve', $milestone->id) }}" method="POST" class="d-inline" onsubmit="return confirm('@lang('Are you sure you want to approve this milestone?')');">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline--success" title="@lang('Approve Milestone')">
                                                                    <i class="las la-check"></i> @lang('Approve')
                                                                </button>
                                                            </form>
                                                            @if($needsSellerApproval)
                                                                <small class="d-block text-muted mt-1" style="font-size: 0.75rem;">@lang('Your approval needed')</small>
                                                            @elseif($needsBuyerApproval)
                                                                <small class="d-block text-muted mt-1" style="font-size: 0.75rem;">@lang('Your approval needed')</small>
                                                            @endif
                                                        @endif
                                                        
                                                        @if($canReject)
                                                            <button type="button" class="btn btn-sm btn-outline--danger rejectBtn" data-id="{{ $milestone->id }}" title="@lang('Reject Milestone')">
                                                                <i class="las la-times"></i> @lang('Reject')
                                                            </button>
                                                        @endif
                                                        
                                                        @if ($isBuyer && $isApproved && $escrow->restAmount() && $milestone->payment_status != Status::MILESTONE_FUNDED)
                                                            <button class="btn btn-sm btn-outline--primary payBtn" @disabled(optional($milestone->deposit)->status == Status::PAYMENT_PENDING) data-id="{{ $milestone->id }}" title="@lang('Pay Milestone')">
                                                                <i class="las la-money-bill-wave"></i> @lang('Pay')
                                                            </button>
                                                        @endif
                                                        
                                                        @if(($milestone->user_id == $user->id || !$isApproved) && $milestone->payment_status != Status::MILESTONE_FUNDED)
                                                            <form action="{{ route('user.escrow.milestone.delete', $milestone->id) }}" method="POST" class="d-inline" onsubmit="return confirm('@lang('Are you sure you want to delete this milestone?')');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline--danger" title="@lang('Delete Milestone')">
                                                                    <i class="las la-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pagination removed - milestones are not paginated --}}
            </div>
        </div>

    <div class="modal custom--modal fade " id="newModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('New Milestone')</h5>
                    <button role="button" class="close"><i class="las la-times" data-bs-dismiss="modal"></i></button>
                </div>
                <form action="{{ route('user.escrow.milestone.create', $escrow->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Note')</label>
                            <input type="text" name="note" placeholder="@lang('Enter note')" class="form-control form--control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">@lang('Rest Amount')</label>
                            <input type="text" class="form-control form--control" value="{{ $restAmount }}" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">@lang('Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" class="form-control form--control" name="amount" required>
                                <span class="input-group-text ">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base w-100 h-45 fw-bold">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Milestone Modal --}}
    <div class="modal fade" id="rejectModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Milestone')</h5>
                    <button role="button" class="close"><i class="las la-times" data-bs-dismiss="modal"></i></button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Rejection Reason') <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control form--control" rows="3" placeholder="@lang('Please provide a reason for rejecting this milestone')" required></textarea>
                            <small class="text-muted">@lang('This will be shared with the other party')</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--danger">@lang('Reject Milestone')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade " id="payModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Pay Milestone')</h5>
                    <button role="button" class="close"><i class="las la-times" data-bs-dismiss="modal"></i></button>
                </div>

                <form action="" method="POST">
                    @csrf
                    <div class="modal-body ">
                        <div class="form-group select2-parent">
                            <label class="d-block mb-2 sm-text">@lang('Select Payment Type')</label>
                            <select name="pay_via" class="form-select form--select select2-basic" data-minimum-results-for-search="-1" required>
                                <option value="1">@lang('Wallet') - {{ showAmount(auth()->user()->balance) }}
                                </option>
                                <option value="2">@lang('Pay via Gateway')</option>
                            </select>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base h-45 w-100 fw-bold">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict"

            $('.payBtn').on('click', function() {
                var modal = $('#payModal');
                modal.find('form')[0].action = `{{ route('user.escrow.milestone.pay', '') }}/${$(this).data('id')}`;
                modal.modal('show');
            });

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('form')[0].action = `{{ route('user.escrow.milestone.reject', '') }}/${$(this).data('id')}`;
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        span.select2.select2-container{
            width: 100% !important;
        }
        
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
