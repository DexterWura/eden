@php
    $subscribeContent = getContent('subscribe.content', true);
@endphp

<div class="section--sm subscribe bg--accent">
    <div class="container">
        <div class="row gy-4 align-items-center justify-content-center">
            <div class="col-lg-6">
                <div class="subscribe-content text-lg-start text-center">
                    <h4 class="subscribe-title text-white my-0">{{ __(@$subscribeContent->data_values->heading) }}</h4>
                </div>
            </div>
            <div class="col-lg-6 col-md-8">
                <form action="" method="POST" id="subscribeForm">
                    @csrf
                    <div class="input-group">
                        <input type="email" name="email" class="form-control form--control" placeholder="@lang('Your Email Address')...">
                        <button type="submit" class="btn--base input-group-text border-0 subscribe-btn">@lang('Subscribe')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('style')
    <style>
        .subscribe-btn {
            font-size: 1rem;
        }
    </style>
@endpush

@push('script')
    <script>
        "use strict";
        (function($) {
            $("#subscribeForm").on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData($(this)[0]);
                $.ajax({
                    url: `{{ route('subscribe') }}`,
                    method: "POST",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            notify('success', response.message)
                        } else {
                            notify('error', response.error || `@lang('Something went wrong')`)
                        }
                    },
                    error: function(e) {
                        notify(`@lang('Something went wrong')`)
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
