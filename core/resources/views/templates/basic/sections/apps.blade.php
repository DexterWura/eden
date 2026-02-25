@php
    $appsContent = getContent('apps.content', true);
    $appsElement = getContent('apps.element', orderById: true);
@endphp

<div class="section--top">
    <div class="container">
        <div class="row g-4 justify-content-between flex-wrap-reverse">
            <div class="col-lg-6">
                <img src="{{ frontendImage('apps', @$appsContent->data_values->image, '645x720') }}" alt="@lang('image')" class="img-fluid">
            </div>
            <div class="col-lg-6 col-xxl-5">
                <h3 class="mt-lg-0 text-center text-lg-start">
                    {{ __(@$appsContent->data_values->heading) }}
                </h3>

                <p class="text-center text-lg-start">
                    {{ __(@$appsContent->data_values->subheading) }}
                </p>

                <p class="text-center text-lg-start">
                    {{ __(@$appsContent->data_values->details) }}
                </p>
                <ul class="list list--row justify-content-center justify-content-lg-start">
                    @foreach ($appsElement as $apps)
                        <li class="list__item">
                            <a href="{{ @$apps->data_values->link }}" class="t-link" target="_blank">
                                <img src="{{ frontendImage('apps', @$apps->data_values->image, '200x60') }}" alt="@lang('escrow')"
                                    class="img-fluid">
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
