@extends('admin.layouts.app')

@section('panel')
<div class="row mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">@lang('Total Referrals')</h6>
                <h4 class="mb-0">{{ number_format($totalReferrals) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">@lang('Total Affiliate Payout')</h6>
                <h4 class="mb-0">{{ showAmount($totalPayout) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">@lang('Top Referrers')</h5>
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Referred signups')</th>
                                <th>@lang('Total earned')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topReferrers as $referrer)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.detail', $referrer->id) }}">{{ $referrer->username }}</a>
                                    <br><span class="small text-muted">{{ $referrer->firstname }} {{ $referrer->lastname }}</span>
                                </td>
                                <td>{{ number_format($referrer->referred_users_count) }}</td>
                                <td>{{ showAmount($referrer->affiliate_earned ?? 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">@lang('No referrers yet')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
