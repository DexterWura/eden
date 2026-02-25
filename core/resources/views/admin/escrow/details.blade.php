@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="text-end">
                        <a href="{{ route('admin.escrow.milestone', $escrow->id) }}" class="btn btn-sm btn-outline--primary">
                            @lang('See Milestones')
                            <i class="las la-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">{{ __($escrow->details) }} </h6>
                    @php
                        $isDirectPayment = (($escrow->payment_mode ?? 'system') === 'direct') && (($escrow->external_amount ?? 0) > 0);
                    @endphp
                    @if($isDirectPayment)
                        <div class="alert alert-warning">
                            <strong>@lang('Direct payout (seller-fee-only):')</strong>
                            @lang('The platform does not charge the buyer any additional fee in Direct payout. The sale amount is paid outside the platform using the external payment link. Any platform monetization for Direct payout is via the seller-side Direct payout listing fee (charged on listing submission).')
                        </div>
                    @endif
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Buyer')</span>
                            <span>{{ __(@$escrow->buyer->username ?? $escrow->invitation_mail) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Seller')</span>
                            <span>{{ __(@$escrow->seller->username ?? $escrow->invitation_mail) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Payment Mode')</span>
                            <span>
                                @if($isDirectPayment)
                                    <span class="badge badge--warning">@lang('Direct')</span>
                                @else
                                    <span class="badge badge--success">@lang('System')</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Sale Amount')</span>
                            <span>
                                {{ showAmount($isDirectPayment ? $escrow->external_amount : $escrow->amount) }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">
                                {{ $isDirectPayment ? __('Buyer platform fee') : __('Charge') }}
                            </span>
                            <span>
                                {{ showAmount($isDirectPayment ? 0 : $escrow->charge) }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Charge Payer')</span>
                            <span>
                                @if ($escrow->charge_payer == Status::CHARGE_PAYER_SELLER)
                                    <span class="badge badge--primary">@lang('Seller')</span>
                                @elseif($escrow->charge_payer == Status::CHARGE_PAYER_BUYER)
                                    <span class="badge badge--dark">@lang('Buyer')</span>
                                @else
                                    <span class="badge badge--success">@lang('50% - 50%')</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Status')</span>
                            <span>
                                @php echo $escrow->escrowStatus @endphp
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Milestone Created')</span>
                            <span>{{ showAmount($escrow->milestones->sum('amount')) }} </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Milestone Funded')</span>
                            <span>{{ showAmount($escrow->milestones->where('payment_status', Status::MILESTONE_FUNDED)->sum('amount')) }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Milestone Unfunded')</span>
                            <span>{{ showAmount($escrow->milestones->where('payment_status', Status::MILESTONE_UNFUNDED)->sum('amount')) }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold">@lang('Rest Amount')</span>
                            <span>{{ showAmount($restAmount) }} </span>
                        </li>

                        @if ($escrow->status == Status::ESCROW_DISPUTED)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">@lang('Disputed By')</span>
                                <span>{{ @$escrow->disputer->username }}</span>
                            </li>
                        @endif
                    </ul>
                    @if ($escrow->status == Status::ESCROW_DISPUTED)
                        <div class="mt-4">
                            <h5 class="text--danger">@lang('Dispute Reason')</h5>
                            <p>{{ __($escrow->dispute_note) }}</p>
                        </div>
                    @endif
                </div>

                @if ($escrow->status == Status::ESCROW_DISPUTED)
                    <div class="card-footer">
                        <button class="btn btn--primary btn-sm w-100 h-45" data-bs-toggle="modal"
                            data-bs-target="#actionModal">@lang('Take Action')</button>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="msg-container">
                <div class="card">
                    <div class="card-header">
                        <div class="text-end">
                            <button class="btn btn--primary  btn-sm reloadButton">
                                <i class="las la-redo-alt me-0"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 msg_history">
                        <div class="messaging p-3">
                            <div class="inbox_msg">
                                <ul class="msg-list d-flex flex-column">
                                    @if ($messages->count() > 0)
                                        @foreach ($messages as $message)
                                            @php
                                                $classText = $message->admin_id != 0 ? 'send' : 'receive';
                                            @endphp
                                            <li class="msg-list__item">
                                                <div class="msg-{{ $classText }}">
                                                    @if ($escrow->status == Status::ESCROW_DISPUTED && $message->admin_id == 0)
                                                        <p>{{ @$message->sender->username ?? $message->admin->username }}
                                                        </p>
                                                    @endif
                                                    <div class="msg-{{ $classText }}__content">
                                                        <p class="msg-{{ $classText }}__text mb-0">
                                                            {{ __($message->message) }}
                                                        </p>
                                                    </div>
                                                    <ul
                                                        class="msg-{{ $classText }}__history @if ($classText == 'send') justify-content-end @endif">
                                                        <li class="msg-receive__history-item">
                                                            {{ $message->created_at->format('h:i A') }}
                                                        </li>
                                                        <li class="msg-receive__history-item">
                                                            {{ $message->created_at->diffForHumans() }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        <div class="empty-message text-center ">
                                            <div class="empty-message__icon">
                                                <i class="fas fa-solid fa-comment-slash"></i>
                                            </div>
                                            <div class="empty-message__heading">
                                                @lang('No conversation started yet')
                                            </div>

                                        </div>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($escrow->status == Status::ESCROW_DISPUTED)
                    <div class="msg-option">
                        <form class="message-form">
                            <div class="msg-option__content rounded-pill">
                                <div class="msg-option__group ">
                                    <input type="text" class="form-control msg-option__input" name="message" autocomplete="off"
                                        placeholder="@lang('Send Message')">
                                    <button type="submit" class=" btn btn--primary  rounded-pill">
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

    <div id="actionModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Escrow Action')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.escrow.action') }}" method="POST">
                    @csrf
                    <input type="hidden" name="escrow_id" value="{{ $escrow->id }}">

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="fw-bold">@lang('Total Funded Amount')</label>
                            <div class="input-group">
                                <input type="text" class="form-control funded-amo"
                                    value="{{ getAmount($escrow->milestones->where('payment_status', 1)->sum('amount')) }}" readonly>

                                <span class="input-group-text">
                                    {{ __(gs('cur_text')) }}
                                </span>

                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">@lang('Amount Send to Buyer')</label>
                            <div class="input-group ">
                                <input type="number" step="any" name="buyer_amount" class="form-control range-calc" required>

                                <span class="input-group-text">
                                    {{ __(gs('cur_text')) }}
                                </span>

                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class=" fw-bold">@lang('Amount Send to Seller')</label>
                            <div class="input-group ">
                                <input type="number" step="any" name="seller_amount" class="form-control range-calc" required>

                                <span class="input-group-text">
                                    {{ __(gs('cur_text')) }}
                                </span>

                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class=" fw-bold">@lang('Charge')</label>
                            <div class="input-group ">
                                <input type="text" class="form-control charge" readonly>

                                <span class="input-group-text">
                                    {{ __(gs('cur_text')) }}
                                </span>

                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">@lang('Select Status')</label>
                            <select name="status" class="form-control form-select-sm" required>
                                <option value="1">@lang('Completed')</option>
                                <option value="9">@lang('Cancelled')</option>
                            </select>
                        </div>
                        <div class="form-gorup">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/chat.css') }}">
@endpush

@push('style')
    <style>
        .msg-option__input:focus {
            box-shadow: none;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict"
            $(".msg_history").animate({
                scrollTop: $('.msg_history').prop("scrollHeight")
            }, 1);


            $('.message-form').on("submit", function(e) {
                e.preventDefault();
                $(this).find('button[type=submit]').removeAttr('disabled');
                var url = "{{ route('admin.escrow.message.reply') }}";

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
                            <li class="msg-list__item">
                                <div class="msg-send">
                                    <div class="msg-send__content">
                                        <p class="msg-send__text mb-0">
                                            ${response['message']}
                                        </p>
                                    </div>
                                    <ul class="msg-send__history  justify-content-end ">
                                        <li class="msg-receive__history-item">${response['created_time']}</li>
                                        <li class="msg-receive__history-item">${response['created_diff']}</li>
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
                var url = '{{ route('admin.escrow.message.get') }}';
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

            $('.range-calc').on('input', function() {
                var buyerAmo = $('[name=buyer_amount]').val();
                if (!buyerAmo) {
                    buyerAmo = 0;
                }
                var sellerAmo = $('[name=seller_amount]').val();

                if (!sellerAmo) {
                    sellerAmo = 0;
                }
                chargeCalculator(buyerAmo, sellerAmo)
            });

            function chargeCalculator(buyerAmo, sellerAmo) {
                var fundedAmo = $('.funded-amo').val();
                var charge = fundedAmo - (parseFloat(buyerAmo) + parseFloat(sellerAmo));
                if (charge < 0) {
                    notify('error', 'You couldn\'t transact greater than funded amount');
                    return false;
                }
                $('.charge').val(charge);
            }

        })(jQuery);
    </script>
@endpush
