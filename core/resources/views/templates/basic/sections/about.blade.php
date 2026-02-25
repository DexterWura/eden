@php
    // About section (Transact Safely) commented out - not displaying on frontend
    return;
    
    $aboutContent = getContent('about.content', true);
    $aboutElements = getContent('about.element', orderById: true);
@endphp

{{-- About section (Transact Safely) commented out
<div class="section--top">
    <div class="container">
        <div class="row g-4 justify-content-lg-between justify-content-center">
            <div class="col-lg-5">
                <div class="section-heading">
                    <h3 class="section-title mt-lg-0 section-title">{{ __(@$aboutContent->data_values->heading) }}</h3>
                    <p>{{ __(@$aboutContent->data_values->subheading) }}</p>
                </div>

                <div class="row gy-4">
                    @foreach ($aboutElements as $about)
                        <div class="col-md-6">
                            <div class="d-flex">
                                <span class="flex-shrink-0 text--base d-inline-block lg-text me-3">
                                    @php echo @$about->data_values->icon; @endphp
                                </span>
                                <p class="mb-0">
                                    {{ __(@$about->data_values->details) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 col-sm-8 col-10">
                <img src="{{ frontendImage('about', @$aboutContent->data_values->image, '645x475') }}" alt="@lang('image')" class="img-fluid">
            </div>
        </div>
    </div>
</div>
--}}
