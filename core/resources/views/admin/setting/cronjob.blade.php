@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <!-- Cronjob Setup Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-cog me-2"></i>@lang('Cronjob Setup')
                </h5>
            </div>
            <div class="card-body">
                @if(!$cronJobActive)
                <div class="alert alert-danger mb-4">
                    <div class="d-flex align-items-center">
                        <i class="las la-exclamation-triangle fs-3 me-3"></i>
                        <div>
                            <strong>@lang('Cron Job Not Detected!')</strong><br>
                            @lang('Please set up the cron job in your cPanel. Schedule:') <code>* * * * *</code> @lang('(every minute)')
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center">
                        <i class="las la-check-circle fs-3 me-3"></i>
                        <div>
                            <strong>@lang('Cron Job Active')</strong>
                            @if($cronJobLastRun)
                                <br>@lang('Last run'): {{ showDateTime($cronJobLastRun, 'd M Y, h:i A') }} ({{ diffForHumans($cronJobLastRun) }})
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">@lang('cPanel Cron Command')</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cronCommand" value="{{ $cronCommand }}" readonly>
                            <button class="btn btn--primary" type="button" id="copyCronCommand" onclick="copyCronCommand()">
                                <i class="las la-copy"></i> @lang('Copy')
                            </button>
                        </div>
                        <small class="form-text text-muted mt-1">
                            @lang('Set schedule to') <code>* * * * *</code> @lang('in cPanel')
                        </small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">@lang('Full Cron Command')</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cronCommandFull" value="{{ $cronCommandFull }}" readonly>
                            <button class="btn btn--primary" type="button" id="copyCronCommandFull" onclick="copyCronCommandFull()">
                                <i class="las la-copy"></i> @lang('Copy')
                            </button>
                        </div>
                        <small class="form-text text-muted mt-1">
                            @lang('For crontab (includes schedule)')
                        </small>
                    </div>
                    
                    @if(isset($phpCommand))
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">@lang('Alternative PHP Command')</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="phpCommand" value="{{ $phpCommandFull }}" readonly>
                            <button class="btn btn--primary" type="button" id="copyPhpCommand" onclick="copyPhpCommand()">
                                <i class="las la-copy"></i> @lang('Copy')
                            </button>
                        </div>
                        <small class="form-text text-muted mt-1">
                            @lang('Use if curl is not available')
                        </small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cron Jobs Status -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-tasks me-2"></i>@lang('Scheduled Tasks Status')
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Task Name')</th>
                                <th>@lang('Schedule')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Last Run')</th>
                                <th>@lang('Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cronJobs as $job)
                            @php
                                $status = $job['status'];
                                $isActive = $status['active'];
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($job['type'] === 'critical')
                                            <i class="las la-exclamation-circle text-danger me-2"></i>
                                        @elseif($job['type'] === 'important')
                                            <i class="las la-info-circle text-warning me-2"></i>
                                        @else
                                            <i class="las la-check-circle text-info me-2"></i>
                                        @endif
                                        <div>
                                            <strong>{{ $job['name'] }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $job['schedule'] }}</span>
                                </td>
                                <td>
                                    @if($isActive)
                                        <span class="badge bg-success">
                                            <i class="las la-check-circle"></i> @lang('Active')
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="las la-times-circle"></i> @lang('Inactive')
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($status['last_run'])
                                        <small>{{ $status['last_run']->format('M d, Y H:i') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $status['last_run']->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">@lang('Never')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($job['log_file'])
                                    <a href="{{ route('admin.setting.cronjob.log', urlencode($job['name'])) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="@lang('View Logs')">
                                        <i class="las la-file-alt"></i>
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cron Job Setup Modal -->
@if(!$cronJobActive)
<div class="modal fade show" id="cronSetupModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" style="display: block;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="las la-exclamation-triangle me-2"></i>@lang('Cron Job Not Configured')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <strong>@lang('Important:')</strong> @lang('Your scheduled tasks require a cron job to run automatically. Without it, critical functions like auction processing will not work.')
                </div>
                
                <div class="alert alert-info mb-3">
                    <strong>@lang('Note:')</strong> @lang('You only need to set up ONE cron job in cPanel. This single command will run all your scheduled tasks automatically.')
                </div>
                
                <h6 class="mb-3">@lang('Quick Setup Instructions:')</h6>
                <ol class="mb-4">
                    <li>@lang('Go to your cPanel')</li>
                    <li>@lang('Navigate to "Cron Jobs"')</li>
                    <li>@lang('Set schedule to:') <code>* * * * *</code> @lang('(every minute)')</li>
                    <li>@lang('Copy and paste the command below')</li>
                    <li>@lang('Click "Add New Cron Job"')</li>
                    <li>@lang('That\'s it! One command runs all scheduled tasks.')</li>
                </ol>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">@lang('Cron Command (Copy this):')</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="modalCronCommand" value="{{ $cronCommand }}" readonly>
                        <button class="btn btn--primary" type="button" onclick="copyModalCommand()">
                            <i class="las la-copy"></i> @lang('Copy')
                        </button>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="button" class="btn btn--success me-2" onclick="location.reload()">
                        <i class="las la-check-circle"></i> @lang('I\'ve Set It Up')
                    </button>
                    <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">
                        @lang('Remind Me Later')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
@endif

@endsection

@push('script')
<script>
    (function($) {
        "use strict";
        
        function copyCronCommand() {
            const commandInput = document.getElementById('cronCommand');
            commandInput.select();
            commandInput.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                const btn = document.getElementById('copyCronCommand');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="las la-check"></i> @lang("Copied!")';
                btn.classList.add('btn--success');
                btn.classList.remove('btn--primary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn--success');
                    btn.classList.add('btn--primary');
                }, 2000);
            } catch (err) {
                alert('@lang("Failed to copy command. Please copy manually.")');
            }
        }
        
        function copyCronCommandFull() {
            const commandInput = document.getElementById('cronCommandFull');
            commandInput.select();
            commandInput.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                const btn = document.getElementById('copyCronCommandFull');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="las la-check"></i> @lang("Copied!")';
                btn.classList.add('btn--success');
                btn.classList.remove('btn--primary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn--success');
                    btn.classList.add('btn--primary');
                }, 2000);
            } catch (err) {
                alert('@lang("Failed to copy command. Please copy manually.")');
            }
        }
        
        function copyPhpCommand() {
            const commandInput = document.getElementById('phpCommand');
            if (!commandInput) return;
            
            commandInput.select();
            commandInput.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                const btn = document.getElementById('copyPhpCommand');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="las la-check"></i> @lang("Copied!")';
                btn.classList.add('btn--success');
                btn.classList.remove('btn--primary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn--success');
                    btn.classList.add('btn--primary');
                }, 2000);
            } catch (err) {
                alert('@lang("Failed to copy command. Please copy manually.")');
            }
        }
        
        function copyModalCommand() {
            const commandInput = document.getElementById('modalCronCommand');
            commandInput.select();
            commandInput.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                const btn = event.target.closest('button');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="las la-check"></i> @lang("Copied!")';
                btn.classList.add('btn--success');
                btn.classList.remove('btn--primary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn--success');
                    btn.classList.add('btn--primary');
                }, 2000);
            } catch (err) {
                alert('@lang("Failed to copy command. Please copy manually.")');
            }
        }
        
        window.copyCronCommand = copyCronCommand;
        window.copyCronCommandFull = copyCronCommandFull;
        window.copyPhpCommand = copyPhpCommand;
        window.copyModalCommand = copyModalCommand;
        
        // Auto-hide modal after 30 seconds if user hasn't interacted
        @if(!$cronJobActive)
        setTimeout(function() {
            const modal = document.getElementById('cronSetupModal');
            if (modal) {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        }, 30000);
        @endif
    })(jQuery);
</script>
@endpush

@push('style')
<style>
    #cronCommand, #cronCommandFull, #phpCommand {
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }
</style>
@endpush
