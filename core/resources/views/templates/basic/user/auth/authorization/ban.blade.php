@extends($activeTemplate . 'layouts.app')
@section('panel')
    @php
        $banContent = getContent('ban_page.content', true);
    @endphp
    <section class="maintenance-page d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-7 text-center">
                    <div class="ban-section">
                        <h4 class="text-center text-danger">
                            {{ __(@$banContent->data_values->heading) }}
                        </h4>

                        <img src="{{ frontendImage('ban_page', @$banContent->data_values->image) }}" alt="@lang('Ban Image')">
                        <div class="mt-3">
                            <p class="fw-bold mb-1">@lang('Reason'):</p>
                            <p>{{ $user->ban_reason }}</p>
                        </div>
                        <a href="{{ route('home') }}" class="btn btn--base h-45">
                            <i class="las la-undo"></i>
                            @lang('Browse ') {{  __(gs('site_name')) }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
