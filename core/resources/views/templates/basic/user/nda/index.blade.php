@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">@lang('My NDA Documents')</h4>
                            <a href="{{ route('user.home') }}" class="btn btn-sm btn-outline-primary">
                                <i class="las la-arrow-left"></i> @lang('Back to Dashboard')
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($ndas->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>@lang('Listing')</th>
                                            <th>@lang('Business Type')</th>
                                            <th>@lang('Seller')</th>
                                            <th>@lang('Signed Date')</th>
                                            <th>@lang('Expires')</th>
                                            <th>@lang('Status')</th>
                                            <th>@lang('Actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ndas as $nda)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($nda->listing->primaryImage)
                                                        <img src="{{ getImage(getFilePath('listing') . '/' . $nda->listing->primaryImage->image) }}"
                                                             alt="{{ $nda->listing->title }}"
                                                             class="rounded me-2"
                                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="las la-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <a href="{{ route('marketplace.listing.show', $nda->listing->slug) }}"
                                                           class="text-decoration-none fw-bold">
                                                            {{ Str::limit($nda->listing->title, 30) }}
                                                        </a>
                                                        <br>
                                                        <small class="text-muted">{{ $nda->listing->listing_number }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $nda->listing->business_type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('marketplace.seller', $nda->listing->seller->username) }}"
                                                   class="text-decoration-none">
                                                    {{ $nda->listing->seller->username }}
                                                </a>
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
                                                <span class="badge bg-success">
                                                    <i class="las la-check-circle"></i> @lang('Signed')
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('marketplace.listing.show', $nda->listing->slug) }}"
                                                       class="btn btn-outline-primary btn-sm"
                                                       title="@lang('View Listing')">
                                                        <i class="las la-eye"></i>
                                                    </a>
                                                    <a href="{{ route('marketplace.nda.download', $nda->id) }}"
                                                       class="btn btn-outline-secondary btn-sm"
                                                       title="@lang('Download NDA Document')">
                                                        <i class="las la-file-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $ndas->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="las la-file-signature la-4x text-muted mb-3"></i>
                                <h5 class="text-muted">@lang('No NDA Documents Found')</h5>
                                <p class="text-muted mb-4">
                                    @lang('You haven\'t signed any Non-Disclosure Agreements yet.')
                                </p>
                                <a href="{{ route('marketplace.index') }}" class="btn btn-primary">
                                    <i class="las la-search"></i> @lang('Browse Listings')
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
        </div>
    </div>
@endsection

@push('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('user.home') }}">@lang('Dashboard')</a>
</li>
<li class="breadcrumb-item active" aria-current="page">@lang('My NDAs')</li>
@endpush
