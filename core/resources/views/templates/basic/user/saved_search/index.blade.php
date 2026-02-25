@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">@lang('Saved Searches')</h4>
            <a href="{{ route('marketplace.browse') }}" class="btn btn-sm btn-outline-primary">
                <i class="las la-search"></i> @lang('Browse Listings')
            </a>
        </div>

        @if($savedSearches->count() > 0)
            <div class="row g-4">
                @foreach($savedSearches as $search)
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $search->name }}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="las la-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('user.saved_search.apply', $search->id) }}">
                                            <i class="las la-search me-2"></i>@lang('Apply Search')
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('user.saved_search.update', $search->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="email_alerts" value="{{ $search->email_alerts ? 0 : 1 }}">
                                            <button type="submit" class="dropdown-item">
                                                <i class="las la-{{ $search->email_alerts ? 'bell-slash' : 'bell' }} me-2"></i>
                                                @lang($search->email_alerts ? 'Disable Alerts' : 'Enable Alerts')
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('user.saved_search.delete', $search->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('@lang('Are you sure you want to delete this saved search?')')">
                                                <i class="las la-trash me-2"></i>@lang('Delete')
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Search Filters Summary -->
                            <div class="row g-2 mb-3">
                                @if($search->filters['search'] ?? null)
                                    <div class="col-auto">
                                        <span class="badge bg-primary">@lang('Search'): {{ $search->filters['search'] }}</span>
                                    </div>
                                @endif
                                @if($search->filters['business_type'] ?? null)
                                    <div class="col-auto">
                                        <span class="badge bg-secondary">
                                            @lang('Type'): {{ ucfirst(str_replace('_', ' ', $search->filters['business_type'])) }}
                                        </span>
                                    </div>
                                @endif
                                @if($search->filters['sale_type'] ?? null)
                                    <div class="col-auto">
                                        <span class="badge bg-info">
                                            @lang('Sale'): {{ ucfirst(str_replace('_', ' ', $search->filters['sale_type'])) }}
                                        </span>
                                    </div>
                                @endif
                                @if($search->filters['min_price'] ?? null || $search->filters['max_price'] ?? null)
                                    <div class="col-auto">
                                        <span class="badge bg-success">
                                            @lang('Price'): {{ showAmount($search->filters['min_price'] ?? 0) }} -
                                            {{ showAmount($search->filters['max_price'] ?? '∞') }}
                                        </span>
                                    </div>
                                @endif
                                @if($search->filters['verified'] ?? null)
                                    <div class="col-auto">
                                        <span class="badge bg-warning">
                                            <i class="las la-check-circle"></i> @lang('Verified Only')
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Alert Settings -->
                            <div class="alert-settings mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted">@lang('Email Alerts')</small>
                                        <div class="form-check form-switch d-inline-block ms-2">
                                            <input class="form-check-input" type="checkbox"
                                                   {{ $search->email_alerts ? 'checked' : '' }} disabled>
                                        </div>
                                    </div>
                                    @if($search->email_alerts)
                                    <div class="text-end">
                                        <small class="text-muted d-block">@lang('Frequency')</small>
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst($search->alert_frequency ?? 'instant') }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('user.saved_search.apply', $search->id) }}"
                                   class="btn btn-primary btn-sm flex-fill">
                                    <i class="las la-play me-1"></i>@lang('Apply Search')
                                </a>
                                <a href="{{ route('marketplace.browse') }}?{{ http_build_query($search->filters) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="las la-external-link-alt me-1"></i>@lang('Open in Browser')
                                </a>
                            </div>
                        </div>

                        <div class="card-footer text-muted">
                            <small>
                                @lang('Created') {{ $search->created_at->format('M d, Y') }}
                                @if($search->last_alerted_at)
                                    • @lang('Last alerted') {{ $search->last_alerted_at->diffForHumans() }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $savedSearches->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="las la-search la-4x text-muted mb-3"></i>
                <h5 class="text-muted">@lang('No Saved Searches')</h5>
                <p class="text-muted mb-4">
                    @lang('You haven\'t saved any searches yet. Save searches to get notified when new listings match your criteria.')
                </p>
                <a href="{{ route('marketplace.browse') }}" class="btn btn-primary">
                    <i class="las la-search"></i> @lang('Start Browsing')
                </a>
            </div>
        @endif
@endsection

@push('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('user.home') }}">@lang('Dashboard')</a>
</li>
<li class="breadcrumb-item active" aria-current="page">@lang('Saved Searches')</li>
@endpush
