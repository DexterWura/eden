@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">@lang('Marketplace Fee Settings')</h6>
                    <small class="text-muted">@lang('These settings were moved from Marketplace Configuration.')</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.charge.marketplace.fees') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label class="form-label">@lang('Listing Fee (%)')</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" class="form-control" name="listing_fee_percentage"
                                           value="{{ $marketplaceSettings['listing_fee_percentage'] ?? 0 }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">@lang('Fee charged when a listing is sold.')</small>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label">@lang('Escrow Fee (%)')</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="50" class="form-control" name="escrow_fee_percentage"
                                           value="{{ $marketplaceSettings['escrow_fee_percentage'] ?? 5 }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">@lang('Escrow service fee percentage.')</small>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label">@lang('Featured Listing Fee (per day)')</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                    <input type="number" step="0.01" min="0" class="form-control" name="featured_listing_fee"
                                           value="{{ $marketplaceSettings['featured_listing_fee'] ?? 0 }}" required>
                                    <span class="input-group-text">/ @lang('day')</span>
                                </div>
                                <small class="text-muted">@lang('Charged per day when a user features a listing.')</small>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn--primary h-45 w-100">@lang('Update Marketplace Fees')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Marketplace Fees (named fees with payer control) --}}
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0">@lang('Marketplace Fees (Rules)')</h6>
                        <small class="text-muted">@lang('Create named fees, enable/disable them, and choose who pays.')</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline--primary h-45 marketplaceFeeModalBtn" data-modal_title="@lang('Add Marketplace Fee')">
                        <i class="las la-plus"></i> @lang('Add New')
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>@lang('SL')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Context')</th>
                                    <th>@lang('Who pays?')</th>
                                    <th>@lang('Percent')</th>
                                    <th>@lang('Fixed')</th>
                                    <th>@lang('Cap')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($marketplaceFees as $fee)
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
                                                        class="btn btn-sm btn-outline--primary marketplaceFeeModalBtn"
                                                        data-resource='@json($fee)'
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

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.charge.global') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>
                                    @lang('Charge Cap')
                                    <code class="text--primary">(@lang('Keep 0 for no charge cap'))</code>
                                </label>
                                <div class="input-group ">
                                    <input type="number" step="any" class="form-control" name="charge_cap" value="{{ getAmount(gs('charge_cap')) }}"
                                        required>
                                    <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label>
                                    @lang('Fixed Charge')
                                    <code class="text--primary">
                                        (@lang('If the amount doesn\'t match any range'))
                                    </code>
                                </label>
                                <div class="input-group ">
                                    <input type="number" step="any" class="form-control" name="fixed_charge"
                                        value="{{ getAmount(gs('fixed_charge')) }}" required>
                                    <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label>
                                    @lang('Percent Charge')
                                    <code class="text--primary">
                                        (@lang('If the amount doesn\'t match any range'))
                                    </code>
                                </label>
                                <div class="input-group ">
                                    <input type="number" step="any" class="form-control" name="percent_charge"
                                        value="{{ getAmount(gs('percent_charge')) }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn--primary h-45 w-100">@lang('Update')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>@lang('SL')</th>
                                    <th>@lang('Minimum')</th>
                                    <th>@lang('Maximum')</th>
                                    <th>@lang('Fixed Charge')</th>
                                    <th>@lang('Percent Charge')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($charges as $charge)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ showAmount($charge->minimum) }}

                                        </td>
                                        <td>
                                            {{ showAmount($charge->maximum) }}

                                        </td>
                                        <td>
                                            {{ showAmount($charge->fixed_charge) }}

                                        </td>
                                        <td>
                                            {{ showAmount($charge->percent_charge) }}%
                                        </td>
                                        <td>
                                            <div class="button--group">

                                                <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"
                                                    data-resource="{{ $charge }}" data-modal_title="@lang('Update Charge Range')" data-has_status="1">
                                                    <i class="la la-pencil"></i>@lang('Edit')
                                                </button>

                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn"
                                                    data-question="@lang('Are you sure to remove this charge range?')" data-action="{{ route('admin.charge.remove', $charge->id) }}">
                                                    <i class="las la-trash"></i>
                                                    @lang('Remove')
                                                </button>
                                            </div>
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
                <form action="{{ route('admin.charge.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Minimum Amount') </label>
                            <div class="input-group ">
                                <input type="number" step="any" class="form-control" name="minimum" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Maximum Amount') </label>
                            <div class="input-group ">
                                <input type="number" step="any" class="form-control" name="maximum" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>

                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Fixed Charge') </label>
                            <div class="input-group ">
                                <input type="number" step="any" class="form-control" name="fixed_charge" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Percent Charge') </label>
                            <div class="input-group ">
                                <input type="number" step="0.01" class="form-control" name="percent_charge" required>
                                <span class="input-group-text">%</span>
                            </div>
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

    <!-- Marketplace Fee Create/Update Modal (separate from #cuModal to avoid conflicts) -->
    <div class="modal fade" id="marketplaceFeeModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="marketplaceFeeModalTitle">@lang('Add Marketplace Fee')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form id="marketplaceFeeForm" action="{{ route('admin.marketplace.fee.store') }}" method="POST">
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
@endsection

@push('breadcrumb-plugins')
    <div class="d-inline">
        <div class="input-group ">
            <input type="text" name="search_table" class="form-control bg--white" placeholder="@lang('Search')...">
            <button class="btn btn--primary input-group-text"><i class="fa fa-search"></i></button>
        </div>
    </div>
    <!-- Modal Trigger Button -->
    <button type="button" class="btn btn-sm btn-outline--primary me-2 h-45 cuModalBtn" data-modal_title="@lang('Add Charge Range')">
        <i class="las la-plus"></i>
        @lang('Add New')
    </button>
@endpush

@push('script')
    <script>
        (function () {
            "use strict";

            function setValue(form, name, value) {
                var el = form.querySelector('[name="' + name + '"]');
                if (!el) return;
                el.value = value == null ? '' : value;
            }

            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.marketplaceFeeModalBtn');
                if (!btn) return;

                var modalEl = document.getElementById('marketplaceFeeModal');
                var form = document.getElementById('marketplaceFeeForm');
                var title = document.getElementById('marketplaceFeeModalTitle');
                if (!modalEl || !form || !title) return;

                title.textContent = btn.getAttribute('data-modal_title') || 'Marketplace Fee';
                form.setAttribute('action', '{{ route('admin.marketplace.fee.store') }}');

                // Reset defaults
                setValue(form, 'name', '');
                setValue(form, 'context', 'escrow_service_fee');
                setValue(form, 'payer', 'buyer');
                setValue(form, 'percent', 0);
                setValue(form, 'fixed', 0);
                setValue(form, 'cap', '');
                setValue(form, 'sort_order', 0);
                setValue(form, 'is_active', 1);

                var resource = btn.getAttribute('data-resource');
                if (resource) {
                    try {
                        var data = JSON.parse(resource);
                        if (data && data.id) {
                            form.setAttribute('action', '{{ url('admin/marketplace-fees/store') }}/' + data.id);
                        }
                        setValue(form, 'name', data.name);
                        setValue(form, 'context', data.context);
                        setValue(form, 'payer', data.payer);
                        setValue(form, 'percent', data.percent);
                        setValue(form, 'fixed', data.fixed);
                        setValue(form, 'cap', data.cap);
                        setValue(form, 'sort_order', data.sort_order);
                        setValue(form, 'is_active', data.is_active ? 1 : 0);
                    } catch (err) {
                        // ignore parse errors
                    }
                }

                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
        })();
    </script>
@endpush
