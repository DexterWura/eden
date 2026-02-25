@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-6">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        @lang('Report') {{ $subjectType === 'listing' ? __('Listing') : __('User') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        @if($subjectType === 'listing')
                            @lang('You are reporting the listing'): <strong>{{ $subjectName }}</strong>
                        @else
                            @lang('You are reporting the user'): <strong>{{ $subjectName }}</strong>
                        @endif
                    </p>
                    <form action="{{ route('user.report.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="reportable_type" value="{{ $subjectType }}">
                        <input type="hidden" name="reportable_id" value="{{ $subject->id }}">
                        <div class="mb-3">
                            <label class="form-label">@lang('Reason') <span class="text-danger">*</span></label>
                            <select name="reason" class="form-select form--control" required>
                                <option value="">@lang('Select a reason')</option>
                                @foreach($reasonOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('reason') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">@lang('Additional details')</label>
                            <textarea name="details" class="form-control form--control" rows="3" maxlength="2000" placeholder="@lang('Optional - provide more context...')">{{ old('details') }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn--base">@lang('Submit Report')</button>
                            <a href="{{ $subjectType === 'listing' ? route('marketplace.listing.show', $subject->slug) : route('marketplace.seller', $subject->username) }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
