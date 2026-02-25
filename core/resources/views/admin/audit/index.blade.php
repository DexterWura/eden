@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="show-filter mb-3 text-end">
                <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
            </div>
            <div class="card responsive-filter-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.audit.index') }}">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="flex-grow-1">
                                <label>@lang('Admin')</label>
                                <select name="admin_id" class="form-control select2" data-minimum-results-for-search="-1">
                                    <option value="">@lang('All')</option>
                                    @foreach($admins as $a)
                                        <option value="{{ $a->id }}" @selected(request()->admin_id == $a->id)>{{ $a->name }} ({{ $a->username }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('Action')</label>
                                <input type="text" name="action" class="form-control" value="{{ request()->action }}" placeholder="@lang('e.g. staff.created')">
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('Date')</label>
                                <input name="date" type="text" class="datepicker-here form-control bg--white pe-2 date-range" placeholder="@lang('Start Date - End Date')" autocomplete="off" value="{{ request()->date }}">
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <button class="btn btn--primary w-100 h-45"><i class="fas fa-filter"></i> @lang('Filter')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Time')</th>
                                    <th>@lang('Admin')</th>
                                    <th>@lang('Action')</th>
                                    <th>@lang('Description')</th>
                                    <th>@lang('IP')</th>
                                    <th>@lang('Details')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>
                                        {{ showDateTime($log->created_at) }}<br>
                                        <span class="small">{{ diffForHumans($log->created_at) }}</span>
                                    </td>
                                    <td>
                                        @if($log->admin)
                                            <span class="fw-bold">{{ $log->admin->name }}</span><br>
                                            <span class="small">@{{ $log->admin->username }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $log->action }}</code></td>
                                    <td><span class="small">{{ \Illuminate\Support\Str::limit($log->description, 60) }}</span></td>
                                    <td><span class="small">{{ $log->ip_address ?? '—' }}</span></td>
                                    <td>
                                        @if($log->old_values || $log->new_values)
                                            <button type="button" class="btn btn-sm btn-outline--info" data-bs-toggle="collapse" data-bs-target="#log-{{ $log->id }}">
                                                @lang('View')
                                            </button>
                                            <div class="collapse mt-2" id="log-{{ $log->id }}">
                                                @if($log->old_values)
                                                    <div class="small"><strong>@lang('Old'):</strong> <pre class="d-inline-block mb-0 small">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></div>
                                                @endif
                                                @if($log->new_values)
                                                    <div class="small"><strong>@lang('New'):</strong> <pre class="d-inline-block mb-0 small">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></div>
                                                @endif
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="6">{{ __($emptyMessage ?? 'No audit log entries found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($logs) }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
