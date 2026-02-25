@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end mb-3">
                <x-search-form btn="btn--base" />
            </div>

            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Gateway | Transaction')</th>
                                <th class="text-center">@lang('Initiated')</th>
                                <th class="text-center">@lang('Amount')</th>
                                <th class="text-center">@lang('Conversion')</th>
                                <th class="text-center">@lang('Status')</th>
                                <th>@lang('Details')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits as $deposit)
                                <tr>
                                    <td>
                                        <span class="fw-bold"> <span class="text-primary">{{ __($deposit->gateway?->name) }}</span>
                                        </span>
                                        <br>
                                        <small> {{ $deposit->trx }} </small>
                                    </td>

                                    <td class="text-center">
                                        {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                    </td>
                                    <td class="text-center">
                                   {{ showAmount($deposit->amount) }} + <span class="text-danger" title="@lang('charge')">{{ showAmount($deposit->charge) }}
                                        </span>
                                        <br>
                                        <strong title="@lang('Amount with charge')">
                                            {{ showAmount($deposit->amount + $deposit->charge) }}
                                          
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        1 {{ __(gs('cur_text')) }} = {{ showAmount($deposit->rate, currencyFormat: false) }}
                                        {{ __($deposit->method_currency) }}
                                        <br>
                                        <strong>{{ showAmount($deposit->final_amount) }}
                                            {{ __($deposit->method_currency) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @php echo $deposit->statusBadge @endphp
                                    </td>
                                    @php
                                    $details = [];
                                    if($deposit->method_code >= 1000 && $deposit->method_code <= 5000){
                                        foreach (@$deposit->detail ?? [] as $key => $info) {
                                            $details[] = $info;
                                            if ($info->type == 'file') {
                                                $details[$key]->value = route('user.download.attachment',encrypt(getFilePath('verify').'/'.$info->value));
                                            }
                                        }
                                    }
                                @endphp


                                    <td>
                                        @if($deposit->method_code >= 1000 && $deposit->method_code <= 5000)
                                        <a href="javascript:void(0)" class="btn btn-sm btn-outline--primary detailBtn" data-info="{{ json_encode($details) }}"
                                            @if ($deposit->status == Status::PAYMENT_REJECT)
                                            data-admin_feedback="{{ $deposit->admin_feedback }}"
                                            @endif
                                            >
                                            <i class="las la-desktop"></i>
                                        </a>
                                        @else
                                    
                                        <button type="button"  class="btn btn-sm btn-outline--success" data-bs-toggle="tooltip" title="@lang('Automatically processed')">
                                             <i class="las la-check-circle"></i>
                                        </button>
                                        @endif
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

            @if ($deposits->hasPages())
                <div class="mt-3">
                    {{ $deposits->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- APPROVE MODAL --}}
    <div id="detailModal" class="modal fade " tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData mb-2 list-group-flush">
                    </ul>
                    <div class="feedback"></div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');

                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span">${element.value}</span>
                            </li>`;
                        }
                    });
                }

                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);
                modal.modal('show');
            });
            $('input[name="search"]').addClass("form--control");

        })(jQuery);
    </script>
@endpush
