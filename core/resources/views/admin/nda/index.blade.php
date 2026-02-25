@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@lang('NDA Management')</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.nda.export', request()->all()) }}" class="btn btn-sm btn-outline-primary">
                            <i class="las la-download"></i> @lang('Export CSV')
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>@lang('Total NDAs')</h6>
                                <h3>{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>@lang('Active')</h6>
                                <h3>{{ $stats['active'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6>@lang('Expired')</h6>
                                <h3>{{ $stats['expired'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6>@lang('Revoked')</h6>
                                <h3>{{ $stats['revoked'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6>@lang('Signed Today')</h6>
                                <h4>{{ $stats['signed_today'] }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6>@lang('Signed This Week')</h6>
                                <h4>{{ $stats['signed_this_week'] }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6>@lang('Signed This Month')</h6>
                                <h4>{{ $stats['signed_this_month'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.nda.index') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label>@lang('Search')</label>
                                    <input type="text" name="search" class="form-control" value="{{ request()->search }}" placeholder="@lang('Listing, User...')">
                                </div>
                                <div class="col-md-2">
                                    <label>@lang('Status')</label>
                                    <select name="status" class="form-control">
                                        <option value="">@lang('All')</option>
                                        <option value="signed" @selected(request()->status == 'signed')>@lang('Signed')</option>
                                        <option value="active" @selected(request()->status == 'active')>@lang('Active')</option>
                                        <option value="expired" @selected(request()->status == 'expired')>@lang('Expired')</option>
                                        <option value="revoked" @selected(request()->status == 'revoked')>@lang('Revoked')</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>@lang('Date From')</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request()->date_from }}">
                                </div>
                                <div class="col-md-2">
                                    <label>@lang('Date To')</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request()->date_to }}">
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary w-100">@lang('Filter')</button>
                                        <a href="{{ route('admin.nda.index') }}" class="btn btn-outline-secondary">@lang('Clear')</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- NDA List -->
                <div class="table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('ID')</th>
                                <th>@lang('Listing')</th>
                                <th>@lang('Signer')</th>
                                <th>@lang('Signed At')</th>
                                <th>@lang('Expires')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ndas as $nda)
                            <tr>
                                <td>#{{ $nda->id }}</td>
                                <td>
                                    <a href="{{ route('admin.listing.detail', $nda->listing_id) }}" class="text-decoration-none">
                                        {{ Str::limit($nda->listing->title, 30) }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $nda->listing->listing_number }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.detail', $nda->user_id) }}" class="text-decoration-none">
                                        {{ $nda->user->username }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $nda->user->email }}</small>
                                </td>
                                <td>
                                    {{ $nda->signed_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $nda->signed_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($nda->expires_at)
                                        {{ $nda->expires_at->format('M d, Y') }}
                                        @if($nda->expires_at->isPast())
                                            <br><span class="badge bg-danger">@lang('Expired')</span>
                                        @else
                                            <br><small class="text-muted">{{ $nda->expires_at->diffForHumans() }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">@lang('Never')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($nda->status === 'signed' && $nda->isActive())
                                        <span class="badge bg-success">@lang('Active')</span>
                                    @elseif($nda->status === 'expired' || $nda->isExpired())
                                        <span class="badge bg-warning">@lang('Expired')</span>
                                    @elseif($nda->status === 'revoked')
                                        <span class="badge bg-danger">@lang('Revoked')</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($nda->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.nda.show', $nda->id) }}" class="btn btn-outline-primary" title="@lang('View Details')">
                                            <i class="las la-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.nda.audit-logs', $nda->id) }}" class="btn btn-outline-info" title="@lang('Audit Logs')">
                                            <i class="las la-history"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">@lang('No NDAs found')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($ndas->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($ndas) }}
                </div>
                @endif
            </div>
        </div>

        <!-- Most Protected Listings -->
        @if($mostProtected->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">@lang('Most Protected Listings')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>@lang('Listing')</th>
                                <th>@lang('NDAs Signed')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mostProtected as $listing)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.listing.detail', $listing->id) }}" class="text-decoration-none">
                                        {{ $listing->title }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $listing->nda_documents_count }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
