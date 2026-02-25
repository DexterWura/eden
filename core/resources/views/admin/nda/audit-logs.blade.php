@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@lang('NDA Audit Logs') - #{{ $nda->id }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.nda.show', $nda->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="las la-arrow-left"></i> @lang('Back to Details')
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>@lang('Listing'):</strong> {{ $nda->listing->title }} ({{ $nda->listing->listing_number }})<br>
                    <strong>@lang('Signer'):</strong> {{ $nda->user->username }}
                </div>

                <div class="table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Date & Time')</th>
                                <th>@lang('Action')</th>
                                <th>@lang('User')</th>
                                <th>@lang('IP Address')</th>
                                <th>@lang('Device Info')</th>
                                <th>@lang('Metadata')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>
                                    {{ $log->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->action === 'signed' ? 'success' : ($log->action === 'revoked' ? 'danger' : 'info') }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->user)
                                        <a href="{{ route('admin.users.detail', $log->user_id) }}">{{ $log->user->username }}</a>
                                    @else
                                        <span class="text-muted">@lang('System')</span>
                                    @endif
                                </td>
                                <td>{{ $log->ip_address ?: 'N/A' }}</td>
                                <td>
                                    @if($log->device_info)
                                        <small>
                                            @if(isset($log->device_info['device_type']))
                                                {{ ucfirst($log->device_info['device_type']) }}<br>
                                            @endif
                                            @if(isset($log->device_info['screen_resolution']))
                                                {{ $log->device_info['screen_resolution'] }}
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->metadata)
                                        <small>
                                            @foreach($log->metadata as $key => $value)
                                                <strong>{{ ucfirst($key) }}:</strong> {{ $value }}<br>
                                            @endforeach
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">@lang('No audit logs found')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($logs) }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
