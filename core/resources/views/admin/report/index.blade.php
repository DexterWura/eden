@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.report.index') }}" method="GET" class="row g-3">
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">@lang('All Status')</option>
                            <option value="0" @selected(request('status') === '0')>@lang('Pending')</option>
                            <option value="1" @selected(request('status') === '1')>@lang('Reviewed')</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">@lang('All Types')</option>
                            <option value="listing" @selected(request('type') === 'listing')>@lang('Listing')</option>
                            <option value="user" @selected(request('type') === 'user')>@lang('User')</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn--primary w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Reporter')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Reported')</th>
                                <th>@lang('Reason')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.users.detail', $report->user_id) }}">{{ $report->user->username ?? 'N/A' }}</a>
                                    </td>
                                    <td>
                                        @if($report->reportable_type == \App\Models\Listing::class)
                                            <span class="badge badge--info">@lang('Listing')</span>
                                        @else
                                            <span class="badge badge--primary">@lang('User')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->reportable_type == \App\Models\Listing::class && $report->reportable)
                                            <a href="{{ route('admin.listing.details', $report->reportable_id) }}">{{ Str::limit($report->reportable->title ?? 'N/A', 30) }}</a>
                                        @elseif($report->reportable_type == \App\Models\User::class && $report->reportable)
                                            <a href="{{ route('admin.users.detail', $report->reportable_id) }}">{{ $report->reportable->username ?? 'N/A' }}</a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\Report::reasonOptions()[$report->reason] ?? $report->reason }}</td>
                                    <td>
                                        @if($report->status == 0)
                                            <span class="badge badge--warning">@lang('Pending')</span>
                                        @else
                                            <span class="badge badge--success">@lang('Reviewed')</span>
                                        @endif
                                    </td>
                                    <td>{{ showDateTime($report->created_at) }}</td>
                                    <td>
                                        <a href="{{ route('admin.report.show', $report->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-eye"></i> @lang('View')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">@lang('No reports found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reports->hasPages())
                <div class="card-footer">{{ $reports->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
