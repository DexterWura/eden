@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>@lang('SL')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Context')</th>
                                    <th>@lang('Payer')</th>
                                    <th>@lang('Percent')</th>
                                    <th>@lang('Fixed')</th>
                                    <th>@lang('Cap')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fees as $fee)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ __($fee->name) }}</td>
                                        <td>
                                            @if($fee->context === 'escrow_service_fee')
                                                <span class="badge badge--primary">@lang('Escrow service fee')</span>
                                            @else
                                                <span class="badge badge--info">@lang('Direct payout listing fee')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge--dark">{{ ucfirst($fee->payer) }}</span>
                                        </td>
                                        <td>{{ showAmount($fee->percent) }}%</td>
                                        <td>{{ showAmount($fee->fixed) }}</td>
                                        <td>
                                            @if($fee->cap && $fee->cap > 0)
                                                {{ showAmount($fee->cap) }}
                                            @else
                                                <span class="text-muted">@lang('No cap')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($fee->is_active)
                                                <span class="badge badge--success">@lang('Enabled')</span>
                                            @else
                                                <span class="badge badge--warning">@lang('Disabled')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline--primary cuModalBtn"
                                                        data-resource="{{ $fee }}"
                                                        data-modal_title="@lang('Update Marketplace Fee')">
                                                    <i class="la la-pencil"></i>@lang('Edit')
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline--dark confirmationBtn"
                                                        data-question="@lang('Are you sure to change status?')"
                                                        data-action="{{ route('admin.marketplace.fee.status', $fee->id) }}">
                                                    <i class="las la-sync"></i>@lang('Toggle')
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline--danger confirmationBtn"
                                                        data-question="@lang('Are you sure to delete this fee?')"
                                                        data-action="{{ route('admin.marketplace.fee.remove', $fee->id) }}">
                                                    <i class="las la-trash"></i>@lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No fees found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Update Modal -->
    <div class="modal fade" id="cuModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.marketplace.fee.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Context')</label>
                            <select class="form-control" name="context" required>
                                <option value="escrow_service_fee">@lang('Escrow service fee')</option>
                                <option value="direct_payout_listing_fee">@lang('Direct payout listing fee')</option>
                            </select>
                            <small class="text-muted">@lang('Escrow service fees apply to System-payment escrows. Direct payout listing fees are charged to the seller on listing submission when payout is set to Direct.')</small>
                        </div>
                        <div class="form-group">
                            <label>@lang('Who pays?')</label>
                            <select class="form-control" name="payer" required>
                                <option value="buyer">@lang('Buyer')</option>
                                <option value="seller">@lang('Seller')</option>
                            </select>
                            <small class="text-muted">@lang('Direct payout listing fees must be paid by the seller.')</small>
                            <div class="text--warning small mt-1">
                                @lang('Important: Direct payout is seller-fee-only. The platform does not charge the buyer an escrow service fee in Direct-payment escrows.')
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Percent')</label>
                            <div class="input-group">
                                <input type="number" step="0.0001" class="form-control" name="percent" value="0" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Fixed')</label>
                            <div class="input-group">
                                <input type="number" step="any" class="form-control" name="fixed" value="0" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Cap') <code class="text--primary">(@lang('0 or empty = no cap'))</code></label>
                            <div class="input-group">
                                <input type="number" step="any" class="form-control" name="cap" value="">
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Sort Order')</label>
                            <input type="number" class="form-control" name="sort_order" value="0">
                        </div>
                        <div class="form-group">
                            <label class="fw-bold">@lang('Enabled')</label>
                            <select class="form-control" name="is_active">
                                <option value="1">@lang('Yes')</option>
                                <option value="0">@lang('No')</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--primary me-2 h-45 cuModalBtn" data-modal_title="@lang('Add Marketplace Fee')">
        <i class="las la-plus"></i>
        @lang('Add New')
    </button>
@endpush


