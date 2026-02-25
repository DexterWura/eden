@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <form method="post" action="{{ route('admin.setting.affiliate') }}">
                @csrf
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                            <div>
                                <p class="fw-bold mb-0">@lang('Enable Affiliate Links')</p>
                                <p class="mb-0">
                                    <small>@lang('When off, users will not see their referral link in the dashboard. When on, users can share their link and earn when someone signs up.')</small>
                                </p>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="affiliate_enable" value="0">
                                <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="@lang('Enable')" data-off="@lang('Disable')" name="affiliate_enable" value="1" @if(gs('affiliate_enable')) checked @endif>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="fw-bold">@lang('Amount per signup')</label>
                                    <p class="text-muted small mb-2">@lang('How much to credit the referrer when a user they referred creates an account.')</p>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="affiliate_signup_amount" value="{{ getAmount(gs('affiliate_signup_amount') ?? 0, 2) }}" step="0.01" min="0" required>
                                        <span class="input-group-text">{{ gs('cur_sym') ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <button type="submit" class="btn btn--primary">@lang('Save')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
