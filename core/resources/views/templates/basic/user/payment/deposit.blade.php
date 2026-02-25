@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card b-radius--10">
                <div class="card-body">
                    <form action="{{ route('user.deposit.insert') }}" method="post" class="deposit-form">
                        @csrf
                        <input type="hidden" name="currency">
                        <input type="hidden" name="type" value="@if ($amount) checkout @else deposit @endif">
                        <div class="gateway-card">
                            <div class="row justify-content-center gy-sm-4 gy-3">
                                <div class="col-12">
                                    <h5 class="payment-card-title">
                                        @lang('Deposit')
                                        <span id="selectedGatewayName" class="text--base">
                                            @if($gatewayCurrency->first())
                                                @lang('with') {{ __($gatewayCurrency->first()->name) }}
                                            @endif
                                        </span>
                                    </h5>
                                </div>
                                <div class="col-lg-6">
                                    <div class="payment-system-list is-scrollable gateway-option-list">
                                        @foreach ($gatewayCurrency as $data)
                                            <label for="{{ titleToKey($data->name) }}"
                                                class="payment-item-card @if ($loop->index > 4) d-none @endif gateway-option">
                                                <div class="payment-item-card__content">
                                                    <div class="payment-item-card__radio">
                                                        <span class="payment-item-card__check"></span>
                                                    </div>
                                                    <div class="payment-item-card__logo">
                                                        <img class="payment-item-card__logo-img"
                                                            src="{{ getImage(getFilePath('gateway') . '/' . $data->method->image) }}" alt="{{ __($data->name) }}">
                                                    </div>
                                                    <div class="payment-item-card__name">
                                                        <span>{{ __($data->name) }}</span>
                                                    </div>
                                                </div>
                                                <input class="payment-item-card__radio-input gateway-input" id="{{ titleToKey($data->name) }}" hidden
                                                    data-gateway='@json($data)' type="radio" name="gateway"
                                                    value="{{ $data->method_code }}"
                                                    @if (old('gateway')) @checked(old('gateway') == $data->method_code) @else @checked($loop->first) @endif
                                                    data-min-amount="{{ showAmount($data->min_amount) }}"
                                                    data-max-amount="{{ showAmount($data->max_amount) }}">
                                            </label>
                                        @endforeach
                                        @if ($gatewayCurrency->count() > 4)
                                            <button type="button" class="payment-item__btn more-gateway-option">
                                                <p class="payment-item__btn-text">@lang('Show All Payment Options')</p>
                                                <span class="payment-item__btn__icon"><i class="fas fa-chevron-down"></i></i></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="payment-system-list p-3">
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text mb-0">@lang('Amount')</p>
                                            </div>

                                            <div class="deposit-info__input">
                                                <div class="deposit-info__input-group input-group">
                                                    <span class="deposit-info__input-group-text">{{ gs('cur_sym') }}</span>
                                                    <input type="text" class="form-control form--control amount" name="amount"
                                                        placeholder="@lang('00.00')" value="{{ old('amount', @$amount ? getAmount($amount) : 0) }}"
                                                        autocomplete="off" @readonly(@$amount)>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text has-icon"> @lang('Limit')
                                                    <span></span>
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span class="gateway-limit">@lang('0.00')</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text has-icon">@lang('Processing Charge')
                                                    <span data-bs-toggle="tooltip" title="@lang('Processing charge for payment gateways')" class="proccessing-fee-info"><i
                                                            class="las la-info-circle"></i> </span>
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span class="processing-fee">@lang('0.00')</span>
                                                    {{ __(gs('cur_text')) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="deposit-info total-amount pt-3">
                                            <div class="deposit-info__title">
                                                <p class="text">@lang('Total')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span class="final-amount">@lang('0.00')</span>
                                                    {{ __(gs('cur_text')) }}</p>
                                            </div>
                                        </div>

                                        <div class="deposit-info gateway-conversion d-none total-amount pt-2">
                                            <div class="deposit-info__title">
                                                <p class="text">@lang('Conversion')
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"></p>
                                            </div>
                                        </div>
                                        <div class="deposit-info conversion-currency d-none total-amount pt-2">
                                            <div class="deposit-info__title">
                                                <p class="text">
                                                    @lang('In') <span class="gateway-currency"></span>
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text">
                                                    <span class="in-currency"></span>
                                                </p>

                                            </div>
                                        </div>
                                        <div class="d-none crypto-message mb-3">
                                            @lang('Conversion with') <span class="gateway-currency"></span> @lang('and final value will Show on next step')
                                        </div>
                                        <button type="submit" class="btn btn--base w-100 mt-3 fw-bold" disabled>
                                            <i class="las la-check-circle"></i> @lang('Confirm Deposit')
                                        </button>
                                        <div class="info-text pt-3">
                                            <p class="text">@lang('Add funds to your account securely to make purchases, place bids, and submit offers on listings.')</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('style')
<style>
    /* Gateway Card Container */
    .payment-item-card {
        display: block;
        cursor: pointer;
        margin-bottom: 12px;
        padding: 16px 20px;
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .payment-item-card:hover {
        border-color: rgb(var(--base));
        background-color: rgba(var(--base-rgb, 70, 52, 255), 0.02);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .payment-item-card.active {
        border-color: rgb(var(--base)) !important;
        background-color: rgba(var(--base-rgb, 70, 52, 255), 0.05) !important;
        box-shadow: 0 4px 16px rgba(var(--base-rgb, 70, 52, 255), 0.2);
    }
    
    .payment-item-card.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: rgb(var(--base));
        border-radius: 8px 0 0 8px;
    }
    
    /* Card Content Layout */
    .payment-item-card__content {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    /* Radio Button */
    .payment-item-card__radio {
        flex-shrink: 0;
    }
    
    .payment-item-card__check {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        background: #fff;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .payment-item-card.active .payment-item-card__check {
        border-color: rgb(var(--base));
        background-color: rgb(var(--base));
    }
    
    .payment-item-card.active .payment-item-card__check::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background-color: #fff;
        border-radius: 50%;
    }
    
    /* Logo Container */
    .payment-item-card__logo {
        flex-shrink: 0;
        width: 120px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        border-radius: 6px;
        padding: 8px 12px;
    }
    
    .payment-item-card__logo-img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
    }
    
    /* Gateway Name */
    .payment-item-card__name {
        flex: 1;
        min-width: 0;
    }
    
    .payment-item-card__name span {
        font-size: 15px;
        font-weight: 500;
        color: #374151;
        transition: all 0.3s ease;
    }
    
    .payment-item-card.active .payment-item-card__name span {
        font-weight: 600;
        color: rgb(var(--base));
    }
    
    /* Hide default radio input */
    .payment-item-card__radio-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    
    /* Button Styles */
    .deposit-form button[type=submit].btn--base {
        background: #{{ gs('base_color', '4bea76') }} !important;
        color: #fff !important;
        font-weight: 600 !important;
        border: none !important;
    }
    
    .deposit-form button[type=submit].btn--base:hover:not(:disabled) {
        background: #{{ gs('base_color', '4bea76') }} !important;
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payment-item-card__logo {
            width: 100px;
            height: 45px;
        }
        
        .payment-item-card__name span {
            font-size: 14px;
        }
    }
</style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {
            var amount = parseFloat($('.amount').val() || 0);
            var gateway, minAmount, maxAmount;

            $('.amount').on('input', function(e) {
                amount = parseFloat($(this).val());
                if (!amount) {
                    amount = 0;
                }
                calculation();
            });

                   // Function to update active state based on checked radio
            function updateActiveGateway() {
                // Remove active class from all payment items
                $('.payment-item-card').removeClass('active');
                
                // Find the checked radio button and add active class to its parent
                const checkedInput = $('.gateway-input:checked');
                if (checkedInput.length) {
                    checkedInput.closest('.payment-item-card').addClass('active');
                    
                    // Update title with selected gateway name
                    const gatewayName = checkedInput.closest('.payment-item-card').find('.payment-item-card__name span').text().trim();
                    $('#selectedGatewayName').text('@lang("with") ' + gatewayName);
                }
            }

            $('.gateway-input').on('change', function(e) {
                updateActiveGateway();
                gatewayChange();
            });
            
            // Initialize active state on page load - ensure it runs after DOM is ready
            $(document).ready(function() {
                updateActiveGateway();
                gatewayChange();
            });

            function gatewayChange() {
                let gatewayElement = $('.gateway-input:checked');
                let methodCode = gatewayElement.val();

                if (!gatewayElement.length) {
                    return;
                }

                gateway = gatewayElement.data('gateway');
                minAmount = gatewayElement.data('min-amount');
                maxAmount = gatewayElement.data('max-amount');

                // Update title with selected gateway name
                const gatewayName = gatewayElement.closest('.payment-item-card').find('.payment-item-card__name span').text().trim();
                $('#selectedGatewayName').text('@lang("with") ' + gatewayName);

                let processingFeeInfo =
                    `${parseFloat(gateway.percent_charge).toFixed(2)}% with ${parseFloat(gateway.fixed_charge).toFixed(2)} {{ __(gs('cur_text')) }} charge for payment gateway processing fees`
                $(".proccessing-fee-info").attr("data-bs-original-title", processingFeeInfo);
                calculation();
            }

            $(".more-gateway-option").on("click", function(e) {
                let paymentList = $(".gateway-option-list");
                paymentList.find(".gateway-option").removeClass("d-none");
                $(this).addClass('d-none');
                paymentList.animate({
                    scrollTop: (paymentList.height() - 60)
                }, 'slow');
            });

            function calculation() {
                if (!gateway) return;
                $(".gateway-limit").text(minAmount + " - " + maxAmount);

                let percentCharge = 0;
                let fixedCharge = 0;
                let totalPercentCharge = 0;

                if (amount) {
                    percentCharge = parseFloat(gateway.percent_charge);
                    fixedCharge = parseFloat(gateway.fixed_charge);
                    totalPercentCharge = parseFloat(amount / 100 * percentCharge);
                }

                let totalCharge = parseFloat(totalPercentCharge + fixedCharge);
                let totalAmount = parseFloat((amount || 0) + totalPercentCharge + fixedCharge);

                $(".final-amount").text(totalAmount.toFixed(2));
                $(".processing-fee").text(totalCharge.toFixed(2));
                $("input[name=currency]").val(gateway.currency);
                $(".gateway-currency").text(gateway.currency);

                if (amount < Number(gateway.min_amount) || amount > Number(gateway.max_amount)) {
                    $(".deposit-form button[type=submit]").prop('disabled', true).addClass('disabled');
                } else {
                    $(".deposit-form button[type=submit]").prop('disabled', false).removeClass('disabled');
                }

                if (gateway.currency != "{{ gs('cur_text') }}" && gateway.method.crypto != 1) {
                    $('.deposit-form').addClass('adjust-height')

                    $(".gateway-conversion, .conversion-currency").removeClass('d-none');
                    $(".gateway-conversion").find('.deposit-info__input .text').html(
                        `1 {{ __(gs('cur_text')) }} = <span class="rate">${parseFloat(gateway.rate).toFixed(2)}</span>  <span class="method_currency">${gateway.currency}</span>`
                    );
                    $('.in-currency').text(parseFloat(totalAmount * gateway.rate).toFixed(gateway.method.crypto == 1 ? 8 : 2))
                } else {
                    $(".gateway-conversion, .conversion-currency").addClass('d-none');
                    $('.deposit-form').removeClass('adjust-height')
                }

                if (gateway.method.crypto == 1) {
                    $('.crypto-message').removeClass('d-none');
                } else {
                    $('.crypto-message').addClass('d-none');
                }
            }

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        })(jQuery);
    </script>
@endpush
