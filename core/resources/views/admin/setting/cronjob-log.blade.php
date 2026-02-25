@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="las la-file-alt me-2"></i>{{ $pageTitle }}
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.setting.cronjob.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="las la-arrow-left"></i> @lang('Back')
                    </a>
                    @if($logExists && $logSize > 0)
                    <form action="{{ route('admin.setting.cronjob.clear.logs') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="job" value="{{ $job['name'] }}">
                        <button type="submit" class="btn btn-sm btn--danger" onclick="return confirm('@lang('Are you sure you want to clear the logs?')')">
                            <i class="las la-trash"></i> @lang('Clear Logs')
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Job Info -->
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>@lang('Task'):</strong> {{ $job['name'] }}<br>
                            <strong>@lang('Description'):</strong> {{ $job['description'] }}<br>
                            <strong>@lang('Schedule'):</strong> {{ $job['schedule'] }}<br>
                            <strong>@lang('Command'):</strong> <code>{{ $job['command'] }}</code>
                        </div>
                        <div class="col-md-6">
                            @if($logExists)
                            <strong>@lang('Log File Size'):</strong> {{ number_format($logSize / 1024, 2) }} KB<br>
                            @if($logLastModified)
                            <strong>@lang('Last Modified'):</strong> {{ showDateTime($logLastModified, 'd M Y, h:i A') }}<br>
                            <strong>@lang('Modified'):</strong> {{ diffForHumans($logLastModified) }}
                            @endif
                            @else
                            <span class="text-muted">@lang('Log file does not exist yet')</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Logs -->
                @if($logExists && $logSize > 0)
                    <div class="log-container" style="max-height: 600px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.6;">
                        @if(count($recentLogs) > 0)
                            @foreach($recentLogs as $log)
                                @php
                                    $logLower = strtolower($log);
                                    $isError = strpos($logLower, 'error') !== false || 
                                              strpos($logLower, 'failed') !== false || 
                                              strpos($logLower, 'exception') !== false ||
                                              strpos($logLower, 'fatal') !== false;
                                    $isSuccess = strpos($logLower, 'success') !== false || 
                                                strpos($logLower, 'completed') !== false ||
                                                strpos($logLower, 'processed') !== false;
                                    $logClass = $isError ? 'text-danger' : ($isSuccess ? 'text-success' : '');
                                @endphp
                                <div class="log-line {{ $logClass }}" style="margin-bottom: 2px; word-wrap: break-word;">
                                    {{ $log }}
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted">@lang('No log entries found')</div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="las la-file-alt display-4 text-muted mb-3"></i>
                        <p class="text-muted">@lang('No log file found. Logs will appear here after the task runs.')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .log-container {
        font-size: 12px;
        line-height: 1.6;
    }
    .log-line {
        margin-bottom: 2px;
        word-wrap: break-word;
    }
    .log-line:last-child {
        margin-bottom: 0;
    }
    .log-line.text-danger {
        background: rgba(220, 53, 69, 0.1);
        padding: 2px 4px;
        border-left: 3px solid #dc3545;
    }
    .log-line.text-success {
        background: rgba(25, 135, 84, 0.1);
        padding: 2px 4px;
        border-left: 3px solid #198754;
    }
</style>
@endpush
