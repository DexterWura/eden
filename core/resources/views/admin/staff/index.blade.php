@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Username')</th>
                                    <th>@lang('Email')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Modules')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admins as $admin)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $admin->name }}</span>
                                    </td>
                                    <td>
                                        <span>@</span>{{ $admin->username }}
                                        @if($admin->is_super_admin)
                                            <span class="badge bg--primary ms-1">@lang('Super Admin')</span>
                                        @endif
                                    </td>
                                    <td>{{ maskForDemo($admin->email) }}</td>
                                    <td>
                                        @if($admin->status == \App\Models\Admin::STATUS_ENABLED)
                                            <span class="badge badge--success">@lang('Enabled')</span>
                                        @else
                                            <span class="badge badge--danger">@lang('Disabled')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($admin->is_super_admin)
                                            <span class="text-muted">@lang('Full access')</span>
                                        @else
                                            @php $mods = is_array($admin->allowed_modules) ? $admin->allowed_modules : []; @endphp
                                            @if(count($mods) > 0)
                                                <span class="small">{{ implode(', ', array_slice($mods, 0, 3)) }}{{ count($mods) > 3 ? '...' : '' }}</span>
                                            @else
                                                <span class="text-muted">@lang('None')</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            <a href="{{ route('admin.staff.edit', $admin->id) }}" class="btn btn-sm btn-outline--primary">
                                                <i class="las la-pen"></i> @lang('Edit')
                                            </a>
                                            @if($admin->id !== auth('admin')->id())
                                                <button type="button" class="btn btn-sm btn-outline--{{ $admin->status == \App\Models\Admin::STATUS_ENABLED ? 'danger' : 'success' }} confirmationBtn"
                                                    data-question="@lang($admin->status == \App\Models\Admin::STATUS_ENABLED ? 'Are you sure to disable this staff?' : 'Are you sure to enable this staff?')"
                                                    data-action="{{ route('admin.staff.status', $admin->id) }}">
                                                    <i class="las la-{{ $admin->status == \App\Models\Admin::STATUS_ENABLED ? 'ban' : 'check' }}"></i>
                                                    @lang($admin->status == \App\Models\Admin::STATUS_ENABLED ? 'Disable' : 'Enable')
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn"
                                                    data-question="@lang('Are you sure to delete this staff?')"
                                                    data-action="{{ route('admin.staff.destroy', $admin->id) }}">
                                                    <i class="las la-trash"></i> @lang('Delete')
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No staff found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($admins->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($admins) }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus"></i> @lang('Add Staff')
    </a>
@endpush
