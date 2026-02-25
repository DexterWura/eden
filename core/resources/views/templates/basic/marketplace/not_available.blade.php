@extends($activeTemplate . 'layouts.frontend')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="las la-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h1 class="h4 mb-3">@lang('Listing Not Available')</h1>
                        <p class="text-muted mb-4">{{ $message ?? __('This listing is no longer available. It may have been removed, deactivated, or sold.') }}</p>
                        <a href="{{ route('marketplace.index') }}" class="btn btn--base">
                            <i class="las la-store me-1"></i> @lang('Browse Listings')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
