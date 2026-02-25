@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-8">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Leave a Review')</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        @lang('You purchased') <strong>{{ $escrow->listing->title }}</strong> @lang('from') {{ $escrow->seller->username ?? 'Seller' }}.
                        @lang('Your feedback helps other buyers and builds trust on the marketplace.')
                    </p>
                    <form action="{{ route('user.review.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="escrow_id" value="{{ $escrow->id }}">
                        <div class="mb-4">
                            <label class="form-label">@lang('Overall rating') <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="mb-0">
                                        <input type="radio" name="overall_rating" value="{{ $i }}" class="form-check-input" {{ old('overall_rating') == $i ? 'checked' : '' }} required>
                                        <span class="ms-1">{{ $i }}</span>
                                    </label>
                                @endfor
                                <span class="text-muted small">(1 = @lang('Poor'), 5 = @lang('Excellent'))</span>
                            </div>
                            @error('overall_rating')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">@lang('Communication')</label>
                                <select name="communication_rating" class="form-select form--control">
                                    <option value="">@lang('Optional')</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('communication_rating') == $i ? 'selected' : '' }}>{{ $i }} @lang('stars')</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">@lang('As described')</label>
                                <select name="accuracy_rating" class="form-select form--control">
                                    <option value="">@lang('Optional')</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('accuracy_rating') == $i ? 'selected' : '' }}>{{ $i }} @lang('stars')</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">@lang('Timeliness')</label>
                                <select name="timeliness_rating" class="form-select form--control">
                                    <option value="">@lang('Optional')</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('timeliness_rating') == $i ? 'selected' : '' }}>{{ $i }} @lang('stars')</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">@lang('Your review') <span class="text-danger">*</span></label>
                            <textarea name="review" class="form-control form--control" rows="4" maxlength="2000" placeholder="@lang('Describe your experience with this seller...')" required>{{ old('review') }}</textarea>
                            <small class="text-muted">@lang('Max 2000 characters')</small>
                            @error('review')<span class="text-danger small d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn--base">
                                <i class="las la-paper-plane"></i> @lang('Submit Review')
                            </button>
                            <a href="{{ route('user.escrow.details', $escrow->id) }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
