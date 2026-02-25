@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Reviewer')</th>
                                <th>@lang('Reviewed User')</th>
                                <th>@lang('Listing')</th>
                                <th>@lang('Rating')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr>
                                    <td>
                                        @if($review->reviewer)
                                            <a href="{{ route('admin.users.detail', $review->reviewer->id) }}">
                                                {{ $review->reviewer->username }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->reviewedUser)
                                            <a href="{{ route('admin.users.detail', $review->reviewedUser->id) }}">
                                                {{ $review->reviewedUser->username }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($review->listing)
                                            <a href="{{ route('admin.listing.details', $review->listing->id) }}">
                                                {{ Str::limit($review->listing->title, 25) }}
                                            </a>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->overall_rating)
                                                    <i class="las la-star text-warning"></i>
                                                @else
                                                    <i class="las la-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-1">({{ $review->overall_rating }})</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($review->review_type == 'buyer_review')
                                            <span class="badge badge--info">@lang('Buyer Review')</span>
                                        @else
                                            <span class="badge badge--primary">@lang('Seller Review')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php echo $review->reviewStatus @endphp
                                    </td>
                                    <td>
                                        {{ showDateTime($review->created_at) }}
                                        <br>
                                        <small>{{ diffForHumans($review->created_at) }}</small>
                                    </td>
                                    <td>
                                        <div class="button-group">
                                            @if($review->status == \App\Constants\Status::REVIEW_PENDING)
                                                <button type="button" class="btn btn-sm btn-outline--success confirmationBtn"
                                                        data-action="{{ route('admin.review.approve', $review->id) }}"
                                                        data-question="@lang('Are you sure to approve this review?')">
                                                    <i class="la la-check"></i> @lang('Approve')
                                                </button>
                                            @endif
                                            @if($review->status != \App\Constants\Status::REVIEW_HIDDEN)
                                                <button type="button" class="btn btn-sm btn-outline--warning confirmationBtn"
                                                        data-action="{{ route('admin.review.hide', $review->id) }}"
                                                        data-question="@lang('Are you sure to hide this review?')">
                                                    <i class="la la-eye-slash"></i> @lang('Hide')
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn"
                                                    data-action="{{ route('admin.review.delete', $review->id) }}"
                                                    data-question="@lang('Are you sure to delete this review?')">
                                                <i class="la la-trash"></i> @lang('Delete')
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __($emptyMessage ?? 'No data found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($reviews->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($reviews) }}
                </div>
            @endif
        </div>
    </div>
</div>
<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
<x-search-form placeholder="Search by Username, Review" />
@endpush

