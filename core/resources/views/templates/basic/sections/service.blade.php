@php
    $serviceContent = getContent('service.content', true);
    $serviceElements = getContent('service.element', orderById: true);
@endphp

<section class="section bg--light">
    <div class="section__head">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <h3 class="section-title mt-0 text-center">
                        {{ __(@$serviceContent->data_values->heading) }}
                    </h3>
                    <p class="section-subtitle mb-0 text-center mx-auto">
                        {{ __(@$serviceContent->data_values->subheading) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row g-4 justify-content-center">
            @foreach ($serviceElements as $service)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="service-card text-center">
                        <div class="service-card__icon icon icon--circle icon--md">
                            @php echo @$service->data_values->icon @endphp
                        </div>
                        <div class="service-card__body">
                            <h5 class="service-card__title">
                                {{ __(@$service->data_values->title) }}
                            </h5>
                            <p class="mb-0">
                                {{ __(@$service->data_values->details) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
