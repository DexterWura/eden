@php
    $coverageContent = getContent('coverage.content', true);
@endphp

<section class="section">
    <div class="section__head">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <h3 class="section-title mt-0 text-center">
                        {{ __(@$coverageContent->data_values->heading) }}
                    </h3>
                    <p class="section-subtitle mb-0 text-center mx-auto">
                        {{ __(@$coverageContent->data_values->subheading) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center">
                    <img src="{{ frontendImage('coverage', @$coverageContent->data_values->image, '1035x505') }}" alt="@lang('image')"
                        class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>
