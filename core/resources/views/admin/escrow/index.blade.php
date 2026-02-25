@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12 ">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Listing | Transaction ID')</th>
                                    <th>@lang('Buyer')</th>
                                    <th>@lang('Seller')</th>
                                    <th>@lang('Amount | Charge')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Charge Payer')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse ($escrows as $escrow)
                                    <tr>
                                        <td>
                                            <div>
                                                @if($escrow->listing)
                                                    <a href="{{ route('admin.listing.details', $escrow->listing->id) }}" class="text-decoration-none">
                                                        <strong>{{ $escrow->listing->title }}</strong>
                                                    </a>
                                                    <br />
                                                    <small class="text-muted">{{ $escrow->listing->listing_number }}</small>
                                                @else
                                                    {{ __($escrow->title) }}
                                                @endif
                                                <br />
                                                <small class="text-muted">@lang('Transaction ID'): {{ $escrow->escrow_number }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($escrow->buyer)
                                                <span class="fw-bold d-block">{{ __($escrow->buyer->fullname) }}</span>

                                                <span class="small">
                                                    <a href="{{ route('admin.users.detail', $escrow->buyer->id) }}">
                                                        <span>@</span>{{ __($escrow->buyer->username) }}
                                                    </a>
                                                </span>
                                            @else
                                                {{ $escrow->invitation_mail }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($escrow->seller)
                                                <span class="fw-bold d-block">{{ __($escrow->seller->fullname) }}</span>
                                                <span class="small">
                                                    <a href="{{ route('admin.users.detail', $escrow->seller->id) }}">
                                                        <span>@</span>{{ __($escrow->seller->username) }}
                                                    </a>
                                                </span>
                                            @else
                                                {{ $escrow->invitation_mail }}
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                @php
                                                    $isDirectPayment = (($escrow->payment_mode ?? 'system') === 'direct') && (($escrow->external_amount ?? 0) > 0);
                                                @endphp

                                                @if($isDirectPayment)
                                                    <small class="text-muted d-block">@lang('Sale')</small>
                                                    <span class="fw-bold">{{ showAmount($escrow->external_amount) }}</span>
                                                    <br />
                                                    <small class="text-muted d-block">@lang('Buyer platform fee')</small>
                                                    <span>{{ showAmount(0) }}</span>
                                                    <div class="text-muted small mt-1">@lang('Direct payout is seller-fee-only')</div>
                                                @else
                                                    {{ showAmount($escrow->amount) }} <br /> {{ showAmount($escrow->charge) }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {{ $escrow->category ? $escrow->category->name : 'N/A' }}
                                        </td>
                                        <td>
                                            @if ($escrow->charge_payer == Status::CHARGE_PAYER_SELLER)
                                                <span class="badge badge--primary">@lang('Seller')</span>
                                            @elseif($escrow->charge_payer == Status::CHARGE_PAYER_BUYER)
                                                <span class="badge badge--dark">@lang('Buyer')</span>
                                            @else
                                                <span class="badge badge--success">@lang('50%-50%')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php echo $escrow->escrowStatus @endphp
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.escrow.details', $escrow->id) }}" class="btn btn-sm btn-outline--primary">
                                                <i class="las la-desktop"></i> @lang('Details')
                                            </a>
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

                @if ($escrows->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($escrows) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Title / Category name" />
@endpush
