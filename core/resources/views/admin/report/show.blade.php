@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-8">
        <div class="card b-radius--10">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>@lang('Reporter')</strong>
                        <p class="mb-0">
                            <a href="{{ route('admin.users.detail', $report->user_id) }}">{{ $report->user->username ?? 'N/A' }}</a>
                            ({{ $report->user->email ?? '' }})
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>@lang('Date')</strong>
                        <p class="mb-0">{{ showDateTime($report->created_at) }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>@lang('Type')</strong>
                        <p class="mb-0">
                            @if($report->reportable_type == \App\Models\Listing::class)
                                @lang('Listing')
                                @if($report->reportable)
                                    — <a href="{{ route('admin.listing.details', $report->reportable_id) }}">{{ $report->reportable->title }}</a>
                                @endif
                            @else
                                @lang('User')
                                @if($report->reportable)
                                    — <a href="{{ route('admin.users.detail', $report->reportable_id) }}">{{ $report->reportable->username ?? $report->reportable->email }}</a>
                                @endif
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>@lang('Reason')</strong>
                        <p class="mb-0">{{ \App\Models\Report::reasonOptions()[$report->reason] ?? $report->reason }}</p>
                    </div>
                </div>
                @if($report->details)
                    <div class="mb-3">
                        <strong>@lang('Details')</strong>
                        <p class="mb-0">{{ $report->details }}</p>
                    </div>
                @endif
                <form action="{{ route('admin.report.update', $report->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">@lang('Status')</label>
                        <select name="status" class="form-select">
                            <option value="0" @selected($report->status == 0)>@lang('Pending')</option>
                            <option value="1" @selected($report->status == 1)>@lang('Reviewed')</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('Admin notes')</label>
                        <textarea name="admin_notes" class="form-control" rows="3">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn--primary">@lang('Update Report')</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
