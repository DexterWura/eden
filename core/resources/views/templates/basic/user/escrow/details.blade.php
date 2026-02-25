@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row gy-4">

                @if($escrow->listing)
                <div class="col-md-12 mb-4">
                    <div class="card custom--card">
                        <div class="card-header bg--base">
                            <h5 class="text-white mb-0">
                                <i class="las la-store"></i> 
                                @lang('Listing Details')
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                <div class="flex-grow-1 min-w-0">
                                    <h4 class="mb-1"><a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}" class="text-decoration-none">{{ $escrow->listing->title }}</a></h4>
                                    @if($escrow->listing->tagline)
                                        <p class="text-muted small mb-2">{{ $escrow->listing->tagline }}</p>
                                    @endif
                                    <div class="d-flex flex-wrap gap-3 text-muted small">
                                        <span>@lang('Listing #') {{ $escrow->listing->listing_number }}</span>
                                        <span>{{ ucfirst(str_replace('_', ' ', $escrow->listing->business_type)) }}</span>
                                        @if($escrow->listing->sale_type == 'auction')
                                            <span>@lang('Auction')</span>
                                        @else
                                            <span>@lang('Fixed Price')</span>
                                        @endif
                                    </div>
                                    @if($escrow->buyer_id == auth()->id() && (($escrow->payment_mode ?? 'system') !== 'direct' || ($escrow->external_amount ?? 0) <= 0))
                                        <div class="mt-3 pt-3 border-top">
                                            <span class="text-muted small me-3">@lang('What you pay'):</span>
                                            <span class="me-3"><span class="text-muted small">@lang('Sale')</span> <strong>{{ showAmount($escrow->amount) }}</strong></span>
                                            <span class="me-3"><span class="text-muted small">@lang('Fee')</span> <strong>{{ showAmount($escrow->buyer_charge ?? 0) }}</strong></span>
                                            <span><span class="text-muted small">@lang('Total')</span> <strong class="text--base">{{ showAmount($escrow->amount + ($escrow->buyer_charge ?? 0)) }}</strong></span>
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}" class="btn btn--base btn-sm flex-shrink-0">
                                    <i class="las la-external-link-alt"></i> @lang('View Listing')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($escrow->status == Status::ESCROW_COMPLETED && isset($canLeaveReview) && $canLeaveReview)
                <div class="col-md-12 mb-3">
                    <div class="alert alert-success d-flex align-items-center justify-content-between flex-wrap gap-2 mb-0">
                        <span><i class="las la-star fs-4 me-2"></i> @lang('How was your experience? Leave a review for the seller to help other buyers.')</span>
                        <a href="{{ route('user.review.create', $escrow->id) }}" class="btn btn--base btn-sm">@lang('Leave a Review')</a>
                    </div>
                </div>
                @elseif($escrow->status == Status::ESCROW_COMPLETED && isset($hasLeftReview) && $hasLeftReview)
                <div class="col-md-12 mb-3">
                    <div class="alert alert-info d-flex align-items-center mb-0">
                        <i class="las la-check-circle fs-4 me-2"></i> @lang('You have left a review for this transaction. Thank you!')
                    </div>
                </div>
                @endif

                @if($escrow->listing && $escrow->listing->sale_type == 'auction' && $escrow->milestones->count() == 0 && $escrow->status == Status::ESCROW_ACCEPTED)
                <div class="col-md-12 mb-3">
                    <div class="alert alert-info d-flex align-items-center mb-0">
                        <i class="las la-info-circle fs-4 me-2"></i>
                        <div>
                            <strong>@lang('Payment Options Available'):</strong>
                            @if($escrow->buyer_id == auth()->user()->id)
                                @lang('You can either pay the full amount now or set up milestones to pay in stages. Choose the option that works best for you.')
                            @else
                                @lang('The buyer can either pay the full amount or set up milestones. You can also create milestones if needed.')
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-header bg--base d-flex flex-wrap align-items-center justify-content-between">
                            <h6 class="text-white">
                                @if ($escrow->buyer_id == auth()->user()->id)
                                    @lang('Purchase Details')
                                @else
                                    @lang('Sale Details')
                                @endif
                            </h6>

                            @if ($escrow->status != Status::ESCROW_NOT_ACCEPTED)
                                @php
                                    $hasMilestones = $escrow->milestones->count() > 0;
                                    $isBuyer = $escrow->buyer_id == auth()->user()->id;
                                    $isSeller = $escrow->seller_id == auth()->user()->id;
                                    $isDirectPayment = ($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0;
                                @endphp
                                
                                @if($isBuyer)
                                    @php
                                        $totalAmount = $escrow->amount + $escrow->buyer_charge;
                                        $remainingAmount = $totalAmount - $escrow->paid_amount;
                                        
                                        $hasFundedMilestones = $hasMilestones && $escrow->milestones->where('payment_status', \App\Constants\Status::MILESTONE_FUNDED)->count() > 0;
                                        $canPayFull = $remainingAmount > 0 && !$hasFundedMilestones;
                                    @endphp
                                    @if($canPayFull && !$isDirectPayment)
                                        <button type="button" class="btn btn-sm btn--dark" data-bs-toggle="modal" data-bs-target="#payFullModal">
                                            <i class="las la-money-bill-wave"></i>
                                            {{ __('Pay Full Amount') }}
                                        </button>
                                    @endif
                                    @if($escrow->status == Status::ESCROW_COMPLETED)
                                        @if($hasMilestones && !$isDirectPayment)
                                            <a href="{{ route('user.escrow.milestone.index', $escrow->id) }}" class="btn btn-sm btn--dark">
                                                <i class="las la-list"></i> @lang('View Milestones')
                                            </a>
                                        @endif
                                    @else
                                        @if(!$hasMilestones && !$isDirectPayment)
                                            <a href="{{ route('user.escrow.milestone.index', $escrow->id) }}" class="btn btn-sm btn--dark">
                                                <i class="las la-tasks"></i> @lang('Set Up Milestones')
                                            </a>
                                        @elseif(!$isDirectPayment)
                                            <a href="{{ route('user.escrow.milestone.index', $escrow->id) }}" class="btn btn-sm btn--dark">
                                                <i class="las la-list"></i> @lang('View Milestones')
                                            </a>
                                        @endif
                                    @endif
                                @elseif($isSeller)
                                    {{-- Seller: show View Milestones only if milestones exist (completed or not) --}}
                                    @if($hasMilestones && !$isDirectPayment)
                                        <a href="{{ route('user.escrow.milestone.index', $escrow->id) }}" class="btn btn-sm btn--dark">
                                            <i class="las la-list"></i> @lang('View Milestones')
                                        </a>
                                    @endif
                                @endif
                            @endif
                        </div>

                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @if($escrow->buyer_id == auth()->id() && ($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0)
                                    <div class="list-group-item">
                                        <div class="alert alert-warning border-0 mb-0">
                                            <strong>@lang('Direct Payment')</strong>
                                            <div class="mt-1">@lang('This seller will be paid outside the platform. Use the External Payment Link below to pay the seller directly.')</div>
                                            <div class="mt-2 small">
                                                <div class="d-flex justify-content-between"><span>@lang('Sale price (paid to seller)'):</span> <strong>{{ showAmount($escrow->external_amount) }}</strong></div>
                                                <div class="d-flex justify-content-between"><span>@lang('Platform fee'):</span> <strong>{{ showAmount(0) }}</strong></div>
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between fw-bold"><span>@lang('Total you pay'):</span> <strong>{{ showAmount($escrow->external_amount) }}</strong></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Transaction ID')</small>
                                    <span>{{ $escrow->escrow_number }}</span>
                                </div>

                                @if($escrow->listing)
                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Listing')</small>
                                    <span><a href="{{ route('marketplace.listing.show', $escrow->listing->slug) }}">{{ $escrow->listing->title }}</a></span>
                                </div>
                                @else
                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Title')</small>
                                    <span>{{ $escrow->title }}</span>
                                </div>
                                @endif

                                <div class="list-group-item">
                                    @if ($escrow->buyer_id == auth()->id())
                                        <small class="text-muted">@lang('Seller')</small>
                                        <span>{{ __(@$escrow->seller->username ?? $escrow->invitation_mail) }}</span>
                                    @else
                                        <small class="text-muted">@lang('Buyer')</small>
                                        {{ __(@$escrow->buyer->username ?? $escrow->invitation_mail) }}
                                    @endif
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Charge Payer')</small>
                                    @if ($escrow->charge_payer == Status::CHARGE_PAYER_SELLER)
                                        <span class="badge badge--dark">@lang('Seller')</span>
                                    @elseif($escrow->charge_payer == Status::CHARGE_PAYER_BUYER)
                                        <span class="badge badge--info">@lang('Buyer')</span>
                                    @else
                                        <span class="badge badge--success">@lang('50%-50%')</span>
                                    @endif
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Status')</small>
                                    @php echo $escrow->escrowStatus @endphp
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Amount')</small>
                                    @if(($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0)
                                        <span>{{ showAmount($escrow->external_amount) }}</span>
                                        <div class="text-muted small">@lang('Sale amount (paid outside platform)')</div>
                                    @else
                                        <span>{{ showAmount($escrow->amount) }}</span>
                                    @endif
                                </div>

                                @if(($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0 && $escrow->buyer_id == auth()->id() && $escrow->external_payment_link)
                                    <div class="list-group-item">
                                        <small class="text-muted">@lang('External Payment Link')</small>
                                        <div class="mt-1">
                                            <a href="{{ $escrow->external_payment_link }}" target="_blank" rel="noopener" class="btn btn-sm btn--base">
                                                <i class="las la-external-link-alt"></i> @lang('Pay Seller')
                                            </a>
                                        </div>
                                        <div class="text-muted small mt-2">@lang('Use this link to pay the seller directly outside the platform.')</div>
                                    </div>
                                @endif

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Charge')</small>
                                    @if(($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0)
                                        <span>{{ showAmount(0) }}</span>
                                    @else
                                        <span>{{ showAmount($escrow->charge) }}</span>
                                    @endif
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Created Milestone')</small>
                                    <span>
                                        {{ showAmount($escrow->milestones->sum('amount')) }}

                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Milestone Funded')</small>
                                    <span>
                                        {{ showAmount($escrow->milestones->where('payment_status', Status::MILESTONE_FUNDED)->sum('amount')) }}

                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Milestone Unfunded')</small>
                                    <span>
                                        {{ showAmount($escrow->milestones->where('payment_status', Status::MILESTONE_UNFUNDED)->sum('amount')) }}

                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Paid Amount')</small>
                                    <span class="fw-bold text--success">
                                        @if(($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0)
                                            {{ showAmount(0) }}
                                        @else
                                            {{ showAmount($escrow->paid_amount) }}
                                        @endif
                                    </span>
                                </div>

                                <div class="list-group-item">
                                    <small class="text-muted">@lang('Rest Amount')</small>
                                    <span>
                                        @if(($escrow->payment_mode ?? 'system') === 'direct' && ($escrow->external_amount ?? 0) > 0)
                                            {{ showAmount(0) }}
                                        @else
                                            {{ showAmount($escrow->restAmount()) }}
                                        @endif
                                    </span>
                                </div>

                                @if ($escrow->status == Status::ESCROW_DISPUTED)
                                    <div class="list-group-item">
                                        <small class="text-muted">@lang('Disputed By')</small>
                                        <span>
                                            {{ $escrow->disputer->username }}
                                        </span>
                                    </div>

                                    <div class="list-group-item">
                                        <h6 class="m-0 text--danger">@lang('Dispute Reason')</h6>
                                        <p class="m-0">{{ __($escrow->dispute_note) }}</p>
                                    </div>
                                @endif
                            </div>

                        </div>

                        @if ($escrow->seller_id == auth()->user()->id && $escrow->status == Status::ESCROW_ACCEPTED && (($isDirectPayment ?? false) || $escrow->restAmount() <= 0))
                            <div class="card-footer bg-white border-top">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="las la-check-circle fs-4 me-3 text--success"></i>
                                        <div>
                                            @if(($isDirectPayment ?? false))
                                                <h6 class="mb-1 fw-bold">@lang('Direct Payout')</h6>
                                                <p class="mb-0 small">
                                                    @lang('The buyer will pay you outside the platform using the External Payment Link.')
                                                    @lang('Because this is a Direct-payment escrow, you will NOT receive the sale amount in your wallet.')
                                                </p>
                                            @else
                                                <h6 class="mb-1 fw-bold">@lang('Payment Received - Awaiting Release')</h6>
                                                <p class="mb-0 small">
                                                    @lang('The buyer has paid the full amount of') <strong>{{ showAmount($escrow->amount + $escrow->buyer_charge) }}</strong>.
                                                    @lang('The funds are held in escrow and will be released to you once the buyer confirms the transaction is complete.')
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($escrow->buyer_id == auth()->user()->id && $escrow->status == Status::ESCROW_ACCEPTED && (($isDirectPayment ?? false) || $escrow->restAmount() <= 0))
                            <div class="card-footer bg-white border-top">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="las la-exclamation-circle fs-4 me-3 text--warning"></i>
                                        <div>
                                            @if(($isDirectPayment ?? false))
                                                <h6 class="mb-1 fw-bold">@lang('Direct Payout')</h6>
                                                <p class="mb-0 small">
                                                    @lang('Pay the seller using the External Payment Link. After you have paid the seller and completed the transaction, you can mark this escrow as complete.')
                                                </p>
                                            @else
                                                <h6 class="mb-1 fw-bold">@lang('Payment Complete - Release Funds')</h6>
                                                <p class="mb-0 small">
                                                    @lang('You have paid the full amount of') <strong>{{ showAmount($escrow->amount + $escrow->buyer_charge) }}</strong>.
                                                    @lang('The funds are held in escrow. Please release the payment to the seller once the agreed arrangements are met and the transaction is complete.')
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($escrow->status == Status::ESCROW_ACCEPTED || $escrow->status == Status::ESCROW_NOT_ACCEPTED)
                            @php
                                $hasSellerAndBuyer = $escrow->seller_id && $escrow->buyer_id;
                            @endphp

                            <div class="card-footer d-flex flex-wrap justify-content-center gap-2 bg-white">
                                @if ($escrow->status == Status::ESCROW_NOT_ACCEPTED)
                                    <button class="btn btn--danger confirmationBtn" data-question="@lang('Are you sure to cancel this transaction?')"
                                        data-action="{{ route('user.escrow.cancel', $escrow->id) }}"><i
                                            class="la la-times"></i>@lang('Cancel')</button>

                                    @if ($escrow->creator_id != auth()->id() && $hasSellerAndBuyer)
                                        <button class="btn btn--success confirmationBtn" data-question="@lang('Are you sure to accept this transaction?')"
                                            data-action="{{ route('user.escrow.accept', $escrow->id) }}"><i
                                                class="la la-check"></i>@lang('Accept')</button>
                                    @endif
                                @else
                                    {{-- payment dispute button --}}
                                    @if ($hasSellerAndBuyer)
                                        <button class="btn btn--danger text-white user-action"> <i class="las la-exclamation-triangle"></i>
                                            @lang('Dispute Transaction')</button>
                                    @endif
                                    {{-- If all amount is paid and the escrow is accepted --}}
                                    @if ($escrow->buyer_id == auth()->user()->id && $hasSellerAndBuyer)
                                        @if(($isDirectPayment ?? false))
                                            <button class="btn btn--primary confirmationBtn"
                                                    data-question="@lang('Only proceed if you have paid the seller via the External Payment Link and the transaction is complete. Mark this escrow as complete?')"
                                                    data-action="{{ route('user.escrow.dispatch', $escrow->id) }}">
                                                <i class="la la-check-circle"></i> @lang('Mark Complete')
                                            </button>
                                        @elseif($escrow->restAmount() <= 0)
                                            <button class="btn btn--primary confirmationBtn" data-question="@lang('Are you sure to release payment to the seller?')"
                                                data-action="{{ route('user.escrow.dispatch', $escrow->id) }}"><i class="la la-money-check-alt"></i>
                                                @lang('Release Payment')</button>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-header bg--base d-flex flex-wrap align-items-center justify-content-between">
                            <h6 class="text-white">@lang('Conversations')</h6>
                            <button type="button" class="btn btn-sm btn--dark reloadButton"><i class="las la-redo-alt"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="messaging msg_history">
                                <div class="inbox_msg">
                                    <ul class="list msg-list d-flex flex-column">
                                        @if ($messages->count() > 0)
                                            @foreach ($messages as $message)
                                                @php
                                                    $classText = $message->sender_id == auth()->user()->id ? 'send' : 'receive';
                                                @endphp
                                                <div class="msg-list__item">
                                                    <div class="msg-{{ $classText }}">
                                                        @if ($escrow->status == Status::ESCROW_DISPUTED && $message->sender_id != auth()->id())
                                                            <p class="mb-0">
                                                                @if ($message->admin)
                                                                    <span class="fw-bold text--danger">
                                                                        @lang('SYSTEM')
                                                                    </span>
                                                                @else
                                                                    {{ @$message->sender->username }}
                                                                @endif
                                                            </p>
                                                        @endif
                                                        <div class="msg-{{ $classText }}__content">
                                                            <p class="msg-{{ $classText }}__text mb-0">
                                                                {{ __($message->message) }}
                                                            </p>
                                                        </div>
                                                        <ul
                                                            class="list msg-{{ $classText }}__history @if ($classText == 'send') justify-content-end @endif">
                                                            <div class="msg-receive__history-item">
                                                                {{ $message->created_at->format('h:i A') }}</div>
                                                            <div class="msg-receive__history-item">{{ $message->created_at->diffForHumans() }}</div>
                                                        </ul>
                                                    </div>
                                                    </li>
                                            @endforeach
                                        @else
                                            <div class="empty-message text-center">
                                                <div class="empty-message__icon">
                                                    <i class="la la-comment-slash"></i>
                                                </div>
                                                <div class="empty-message__heading">
                                                    @lang('No conversation yet')
                                                </div>

                                            </div>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($escrow->status != Status::ESCROW_CANCELLED && $escrow->status != Status::ESCROW_COMPLETED)
                        <div class="msg-option">
                            <form class="message-form">
                                <div class="msg-option__content rounded-pill">
                                    <div class="msg-option__group ">
                                        <input type="text" class="form-control msg-option__input" name="message" autocomplete="off"
                                            placeholder="@lang('Send Message')">
                                        <button type="submit" class="btn msg-option__button reloadButton rounded-pill fw-bold" style="background: #{{ gs('base_color', '4bea76') }} !important; color: #fff !important; border: none !important;">
                                            <i class="lab la-telegram-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade " id="actionModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Dispute Transaction')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.escrow.dispute', $escrow->id) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">@lang('Dispute Reason')</label>
                            <textarea class="form-control form--control-textarea" name="dispute_reason" rows="3" placeholder="@lang('Enter the reason')" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base h-45 w-100 fw-bold">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />

    {{-- Pay Full Amount Modal (not applicable to Direct payout escrows) --}}
    @if($escrow->status == Status::ESCROW_ACCEPTED && $escrow->buyer_id == auth()->user()->id && !($isDirectPayment ?? false))
        @php
            $hasFundedMilestones = $escrow->milestones->where('payment_status', \App\Constants\Status::MILESTONE_FUNDED)->count() > 0;
        @endphp
        @if(!$hasFundedMilestones)
        @php
            $totalAmount = $escrow->amount + $escrow->buyer_charge;
            $remainingAmount = $totalAmount - $escrow->paid_amount;
        @endphp
        @if($remainingAmount > 0)
        <div class="modal fade" id="payFullModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('Pay Full Amount') }}
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('user.escrow.pay.full', $escrow->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <p class="mb-2">
                                    <strong>
                                        {{ __('Total Amount') }}:
                                    </strong>
                                    {{ showAmount($totalAmount) }}
                                </p>
                                <p class="mb-2"><strong>@lang('Already Paid'):</strong> {{ showAmount($escrow->paid_amount) }}</p>
                                <p class="mb-0"><strong>@lang('Remaining'):</strong> <span class="text--base fw-bold">{{ showAmount($remainingAmount) }}</span></p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">@lang('Payment Method')</label>
                                <select name="pay_via" class="form-select form--select" required>
                                    <option value="1">@lang('Wallet') - {{ showAmount(auth()->user()->balance) }}</option>
                                    <option value="2">@lang('Pay via Gateway')</option>
                                </select>
                            </div>
                            <div class="alert alert-warning mt-3">
                                <small>
                                    <i class="las la-info-circle"></i> 
                                    @lang('By paying the full amount, you are committing to complete the entire transaction. You can release payment to the seller once the transaction is complete.')
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                            <button type="submit" class="btn btn--base fw-bold">@lang('Pay Now')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @endif
    @endif
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/chat.css') }}">
@endpush

@push('style')
    <style>
        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .msg-option__button {
            background: #{{ gs('base_color', '4bea76') }} !important;
            color: #fff !important;
            border: none !important;
            font-weight: 600 !important;
        }
        
        .msg-option__button:hover {
            background: #{{ gs('base_color', '4bea76') }} !important;
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
        }
        
        #actionModal button[type=submit].btn--base,
        #payFullModal button[type=submit].btn--base {
            background: #{{ gs('base_color', '4bea76') }} !important;
            color: #fff !important;
            font-weight: 600 !important;
            border: none !important;
        }
        
        #actionModal button[type=submit].btn--base:hover,
        #payFullModal button[type=submit].btn--base:hover {
            background: #{{ gs('base_color', '4bea76') }} !important;
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
        }
        
        .btn--base {
            background: #{{ gs('base_color', '4bea76') }} !important;
            color: #fff !important;
            font-weight: 600 !important;
        }
        
        .btn--base:hover {
            background: #{{ gs('base_color', '4bea76') }} !important;
            opacity: 0.9;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(".msg_history").animate({
                scrollTop: $('.msg_history').prop("scrollHeight")
            }, 1);

            var actionModal = $('#actionModal');

            $('.user-action').on('click', function() {
                actionModal.modal('show');
            });

            $('.message-form').on("submit", function(e) {
                e.preventDefault();
                $(this).find('button[type=submit]');
                let message = $(this).find('[name=message]').val();

                var url = '{{ route('user.escrow.message.reply') }}';
                var data = {
                    _token: "{{ csrf_token() }}",
                    conversation_id: "{{ $conversation->id }}",
                    message: $(this).find('[name=message]').val()
                }

                $.post(url, data, function(response) {
                    if (response['error']) {
                        $.each(response['error'], function(i, v) {
                            notify('error', v);
                        });
                        return true;
                    }

                    var html = `
                            <div class="msg-list__item">
                                <div class="msg-send">
                                    <div class="msg-send__content">
                                        <p class="msg-send__text mb-0">
                                            ${response['message']}
                                        </p>
                                    </div>
                                    <ul class="list msg-send__history  justify-content-end ">
                                        <div class="msg-receive__history-item">${response['created_time']}</div>
                                        <div class="msg-receive__history-item">${response['created_diff']}</div>
                                    </ul>
                                </div>
                            </li>
                    `;

                    $('.msg-list').append(html);
                    $(".msg_history").animate({
                        scrollTop: $('.msg_history').prop("scrollHeight")
                    }, 1);
                });
                $(this).find('[name=message]').val('')

            });

            $('.reloadButton').on("click", function() {
                var url = '{{ route('user.escrow.message.get') }}';
                var data = {
                    conversation_id: "{{ $conversation->id }}"
                }
                $.get(url, data, function(response) {

                    if (response['error']) {
                        $.each(response['error'], function(i, v) {
                            notify('error', v);
                        });
                        return true;
                    }

                    $('.msg-list').html(response);
                    $(".msg_history").animate({
                        scrollTop: $('.msg_history').prop("scrollHeight")
                    }, 1);
                });
            });

        })(jQuery);
    </script>
@endpush
