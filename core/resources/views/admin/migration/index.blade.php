@extends('admin.layouts.app')

@section('panel')
<div class="row migration-panel backoffice">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">@lang('Database Migrations')</h4>
            </div>
            <div class="card-body">
                @if(!$migrationsTableExists)
                    <div class="alert alert-warning">
                        <i class="las la-exclamation-triangle"></i>
                        <strong>@lang('Migrations table not found')</strong>
                        <p>@lang('The migrations table does not exist. Run the migration installer first.')</p>
                        <button class="btn btn-sm btn-primary" onclick="installMigrationsTable()">
                            <i class="las la-database"></i> @lang('Install Migrations Table')
                        </button>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">@lang('Pending')</h5>
                                <h2 class="mb-0">{{ count($pendingMigrations) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">@lang('Modified')</h5>
                                <h2 class="mb-0">{{ count($modifiedMigrations) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">@lang('Ran')</h5>
                                <h2 class="mb-0">{{ count($ranMigrations) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">@lang('Total')</h5>
                                <h2 class="mb-0">{{ count($migrationFiles) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <button class="btn btn-primary" onclick="runMigrations()" {{ count($pendingMigrations) == 0 && count($modifiedMigrations) == 0 ? 'disabled' : '' }}>
                            <i class="las la-play"></i> @lang('Run Pending Migrations')
                        </button>
                        <button class="btn btn-secondary" onclick="refreshStatus()">
                            <i class="las la-sync"></i> @lang('Refresh Status')
                        </button>
                        <a class="btn btn-outline-primary" href="{{ route('admin.migration.download.sql') }}">
                            <i class="las la-download"></i> @lang('Download SQL Backup')
                        </a>
                    </div>
                    <div>
                        <button class="btn btn-danger" onclick="rollbackMigrations()">
                            <i class="las la-undo"></i> @lang('Rollback Last Batch')
                        </button>
                    </div>
                </div>

                @if(count($pendingMigrations) > 0)
                    <div class="alert alert-info">
                        <h5><i class="las la-info-circle"></i> @lang('Pending Migrations')</h5>
                        <ul class="mb-0">
                            @foreach($pendingMigrations as $migration)
                                <li>{{ $migration['name'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(count($modifiedMigrations) > 0)
                    <div class="alert alert-warning">
                        <h5><i class="las la-exclamation-triangle"></i> @lang('Modified Migrations')</h5>
                        <p>@lang('These migrations have been modified since they were last run.')</p>
                        <ul class="mb-0">
                            @foreach($modifiedMigrations as $migration)
                                <li>
                                    <strong>{{ $migration['migration_name'] }}</strong>
                                    <button class="btn btn-sm btn-warning ms-2" onclick="runSpecificMigration('{{ $migration['migration_name'] }}')">
                                        <i class="las la-play"></i> @lang('Rerun')
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Migration Name')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Batch')</th>
                                <th>@lang('File Size')</th>
                                <th>@lang('Modified')</th>
                                <th>@lang('Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($migrationFiles as $file)
                                @php
                                    $migrationName = str_replace('.php', '', $file['name']);
                                    $isRan = isset($migrationStatus[$migrationName]);
                                    $isModified = false;
                                    foreach($modifiedMigrations as $mod) {
                                        if(isset($mod['migration_name']) && $mod['migration_name'] === $migrationName) {
                                            $isModified = true;
                                            break;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $file['name'] }}</td>
                                    <td>
                                        @if($isModified)
                                            <span class="badge bg-warning">@lang('Modified')</span>
                                        @elseif($isRan)
                                            <span class="badge bg-success">@lang('Ran')</span>
                                        @else
                                            <span class="badge bg-danger">@lang('Pending')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isRan)
                                            {{ $migrationStatus[$migrationName] }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                                    <td>{{ date('Y-m-d H:i:s', $file['modified']) }}</td>
                                    <td>
                                        @if(!$isRan)
                                            <button class="btn btn-sm btn-primary" onclick="runSpecificMigration('{{ $migrationName }}')">
                                                <i class="las la-play"></i> @lang('Run')
                                            </button>
                                        @elseif($isModified)
                                            <button class="btn btn-sm btn-warning" onclick="runSpecificMigration('{{ $migrationName }}')">
                                                <i class="las la-redo"></i> @lang('Rerun')
                                            </button>
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Confirm Action')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                    <label class="form-check-label" for="confirmCheck">
                        @lang('I understand and want to proceed')
                    </label>
                </div>
                @if(app()->environment('production'))
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="forceCheck">
                        <label class="form-check-label" for="forceCheck">
                            <strong>@lang('Force (Production Mode)')</strong>
                        </label>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">@lang('Confirm')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    window.EDEN_MIGRATION = {
        runUrl: '{{ route("admin.migration.run") }}',
        runSpecificUrlTemplate: '{{ route("admin.migration.run.specific", "REPLACE_MIGRATION") }}',
        rollbackUrl: '{{ route("admin.migration.rollback") }}',
        refreshUrl: '{{ route("admin.migration.refresh") }}',
        csrfToken: '{{ csrf_token() }}'
    };
</script>
<script src="{{ asset('js/migration.js') }}"></script>
@endpush

