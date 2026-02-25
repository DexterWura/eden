@php
    $workContent = getContent('how_works.content', true);
    $workElements = getContent('how_works.element', orderById: true);
@endphp

<section class="section">
    <div class="section__head">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <h3 class="section-title mt-0 text-center">
                        {{ __(@$workContent->data_values->heading) }}
                    </h3>
                    <p class="section-subtitle mb-0 text-center mx-auto">
                        {{ __(@$workContent->data_values->subheading) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4 justify-content-center">
            @foreach ($workElements as $work)
                <div class="col-md-6 col-lg-3">
                    <div class="process text-center">
                        <div class="icon icon--circle icon--xxl process__icon @if (!$loop->last) process__icon-shape @endif">
                            <div class="process-count">
                                <span>{{ $loop->index + 1 }}</span>
                            </div>
                            @php echo @$work->data_values->icon; @endphp
                        </div>
                        <h5 class="mb-0">
                            {{ __(@$work->data_values->details) }}
                        </h5>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
